<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle;

use Shopware\Core\Framework\Api\Util\AccessKeyHelper;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Event\AppDeletedEvent;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Event\AppInstalledEvent;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Event\AppUpdatedEvent;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Persister\ActionButtonPersister;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Persister\CustomFieldPersister;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Persister\PermissionPersister;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Persister\TemplatePersister;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Persister\WebhookPersister;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Registration\AppRegistrationService;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\Module;
use Swag\SaasConnect\Storefront\Theme\Lifecycle\ThemeLifecycleHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AppLifecycle implements AppLifecycleInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    /**
     * @var ActionButtonPersister
     */
    private $actionButtonPersister;

    /**
     * @var PermissionPersister
     */
    private $permissionPersister;

    /**
     * @var CustomFieldPersister
     */
    private $customFieldPersister;

    /**
     * @var WebhookPersister
     */
    private $webhookPersister;

    /**
     * @var AppLoaderInterface
     */
    private $appLoader;

    /**
     * @var TemplatePersister
     */
    private $templatePersister;

    /**
     * @var ThemeLifecycleHandler
     */
    private $themeLifecycleHandler;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var AppRegistrationService
     */
    private $registrationService;

    public function __construct(
        EntityRepositoryInterface $appRepository,
        ActionButtonPersister $actionButtonPersister,
        PermissionPersister $permissionPersister,
        CustomFieldPersister $customFieldPersister,
        WebhookPersister $webhookPersister,
        AppLoaderInterface $appLoader,
        TemplatePersister $templatePersister,
        ThemeLifecycleHandler $themeLifecycleHandler,
        EventDispatcherInterface $eventDispatcher,
        AppRegistrationService $registrationService
    ) {
        $this->appRepository = $appRepository;
        $this->actionButtonPersister = $actionButtonPersister;
        $this->permissionPersister = $permissionPersister;
        $this->customFieldPersister = $customFieldPersister;
        $this->webhookPersister = $webhookPersister;
        $this->appLoader = $appLoader;
        $this->templatePersister = $templatePersister;
        $this->themeLifecycleHandler = $themeLifecycleHandler;
        $this->eventDispatcher = $eventDispatcher;
        $this->registrationService = $registrationService;
    }

    public function install(Manifest $manifest, Context $context): void
    {
        $metadata = $manifest->getMetadata()->toArray();
        $appId = Uuid::randomHex();
        $roleId = Uuid::randomHex();
        $metadata = $this->enrichInstallMetadata($manifest, $metadata, $roleId);

        $this->updateApp($manifest, $metadata, $appId, $roleId, $context);

        $this->registrationService->registerApp($manifest, $appId, $context);

        $this->eventDispatcher->dispatch(
            new AppInstalledEvent($appId, $manifest, $context)
        );
    }

    /**
     * @param array<string, string> $app
     */
    public function update(Manifest $manifest, array $app, Context $context): void
    {
        $metadata = $manifest->getMetadata()->toArray();
        $this->updateApp($manifest, $metadata, $app['id'], $app['roleId'], $context);

        $this->eventDispatcher->dispatch(
            new AppUpdatedEvent($app['id'], $manifest, $context)
        );
    }

    /**
     * @param array<string, string> $app
     */
    public function delete(string $appName, array $app, Context $context): void
    {
        $this->themeLifecycleHandler->handleUninstall($appName, $context);
        $this->appRepository->delete([['id' => $app['id']]], $context);

        $this->eventDispatcher->dispatch(
            new AppDeletedEvent($app['id'], $context)
        );
    }

    /**
     * @param array<string, string|array<string, string|bool>> $metadata
     */
    private function updateApp(Manifest $manifest, array $metadata, string $id, string $roleId, Context $context): void
    {
        unset($metadata['icon']);
        $metadata['path'] = $manifest->getPath();
        $metadata['id'] = $id;
        $metadata['modules'] = array_reduce(
            $manifest->getAdmin() ? $manifest->getAdmin()->getModules() : [],
            static function (array $modules, Module $module) {
                $modules[] = $module->toArray();

                return $modules;
            },
            []
        );
        $metadata['iconRaw'] = $this->appLoader->getIcon($manifest);

        $this->updateMetadata($metadata, $context);
        $this->permissionPersister->updatePrivileges($manifest, $roleId);

        $this->actionButtonPersister->updateActions($manifest, $id, $context);
        $this->customFieldPersister->updateCustomFields($manifest, $id, $context);
        $this->webhookPersister->updateWebhooks($manifest, $id, $context);
        $this->templatePersister->updateTemplates($manifest, $id, $context);

        $this->themeLifecycleHandler->handleAppUpdate($manifest, $context);
    }

    /**
     * @param array<string, string|array<string, string|bool>|null> $metadata
     */
    private function updateMetadata(array $metadata, Context $context): void
    {
        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($metadata): void {
            $this->appRepository->upsert([$metadata], $context);
        });
    }

    /**
     * @param  array<string, string|array<string, string>> $metadata
     * @return array<string, string|array<string, string|bool>>
     */
    private function enrichInstallMetadata(Manifest $manifest, array $metadata, string $roleId): array
    {
        $secret = AccessKeyHelper::generateSecretAccessKey();

        $metadata['integration'] = [
            'label' => $manifest->getMetadata()->getName(),
            'writeAccess' => true,
            'accessKey' => AccessKeyHelper::generateAccessKey('integration'),
            'secretAccessKey' => $secret,
        ];
        $metadata['aclRole'] = [
            'id' => $roleId,
            'name' => $manifest->getMetadata()->getName(),
        ];
        $metadata['accessToken'] = $secret;

        return $metadata;
    }
}

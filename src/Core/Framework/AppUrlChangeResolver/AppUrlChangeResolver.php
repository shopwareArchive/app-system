<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\AppUrlChangeResolver;

use Shopware\Core\Framework\Api\Util\AccessKeyHelper;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Swag\SaasConnect\Core\Content\App\AppCollection;
use Swag\SaasConnect\Core\Content\App\AppEntity;
use Swag\SaasConnect\Core\Content\App\Lifecycle\AppLoaderInterface;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Registration\AppRegistrationService;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;

abstract class AppUrlChangeResolver implements AppUrlChangeResolverInterface
{
    /**
     * @var AppLoaderInterface
     */
    private $appLoader;

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    /**
     * @var AppRegistrationService
     */
    private $registrationService;

    public function __construct(
        AppLoaderInterface $appLoader,
        EntityRepositoryInterface $appRepository,
        AppRegistrationService $registrationService
    ) {
        $this->appLoader = $appLoader;
        $this->appRepository = $appRepository;
        $this->registrationService = $registrationService;
    }

    abstract public function getName(): string;

    abstract public function getDescription(): string;

    abstract public function resolve(Context $context): void;

    protected function forEachInstalledApp(Context $context, callable $callback): void
    {
        $manifests = $this->appLoader->load();
        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $context)->getEntities();

        /** @var Manifest $manifest */
        foreach ($manifests as $manifest) {
            $app = $this->getAppForManifest($manifest, $apps);

            if (!$app || !$manifest->getSetup()) {
                continue;
            }

            $callback($manifest, $app, $context);
        }
    }

    protected function reRegisterApp(Manifest $manifest, AppEntity $app, Context $context): void
    {
        $secret = AccessKeyHelper::generateSecretAccessKey();

        $this->appRepository->update([
            [
                'id' => $app->getId(),
                'integration' => [
                    'id' => $app->getIntegrationId(),
                    'accessKey' => AccessKeyHelper::generateAccessKey('integration'),
                    'secretAccessKey' => $secret,
                ],
            ],
        ], $context);

        $this->registrationService->registerApp($manifest, $app->getId(), $secret, $context);
    }

    private function getAppForManifest(Manifest $manifest, AppCollection $installedApps): ?AppEntity
    {
        $matchedApps = $installedApps->filter(static function (AppEntity $installedApp) use ($manifest): bool {
            return $installedApp->getName() === $manifest->getMetadata()->getName();
        });

        return $matchedApps->first();
    }
}

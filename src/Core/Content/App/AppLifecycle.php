<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Api\Util\AccessKeyHelper;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\ActionButton;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\Permissions;

class AppLifecycle implements AppLifecycleInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $actionButtonRepository;

    /**
     * @var array<string, array<string>>
     */
    private $privilegeDependence;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param array<string, array<string>> $privilegeDependence
     */
    public function __construct(
        EntityRepositoryInterface $appRepository,
        EntityRepositoryInterface $actionButtonRepository,
        Connection $connection,
        array $privilegeDependence
    ) {
        $this->appRepository = $appRepository;
        $this->actionButtonRepository = $actionButtonRepository;
        $this->privilegeDependence = $privilegeDependence;
        $this->connection = $connection;
    }

    public function install(Manifest $manifest, Context $context): void
    {
        $metadata = $manifest->getMetadata()->toArray();
        $appId = Uuid::randomHex();
        $roleId = Uuid::randomHex();
        $metadata = $this->enrichInstallMetadata($manifest, $metadata, $roleId);

        $this->updateApp($manifest, $metadata, $appId, $roleId, $context);
    }

    /**
     * @param array<string, string> $app
     */
    public function update(Manifest $manifest, array $app, Context $context): void
    {
        $metadata = $manifest->getMetadata()->toArray();
        $this->updateApp($manifest, $metadata, $app['id'], $app['roleId'], $context);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     *
     * @param array<string, string> $app
     */
    public function delete(string $appName, array $app, Context $context): void
    {
        $this->appRepository->delete([['id' => $app['id']]], $context);
    }

    /**
     * @param array<string, string|array<string, string|bool>> $metadata
     */
    private function updateApp(Manifest $manifest, array $metadata, string $id, string $roleId, Context $context): void
    {
        $metadata['path'] = $manifest->getPath();
        $metadata['id'] = $id;

        $this->updateMetadata($metadata, $context);
        $this->updateActions($manifest->getAdmin() ? $manifest->getAdmin()->getActionButtons() : [], $id, $context);
        $this->updatePrivileges($manifest->getPermissions(), $roleId);
    }

    /**
     * @param array<string, string|array<string, string|bool>> $metadata
     */
    private function updateMetadata(array $metadata, Context $context): void
    {
        // ToDo handle import and saving of icons
        unset($metadata['icon']);

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($metadata): void {
            $this->appRepository->upsert([$metadata], $context);
        });
    }

    /**
     * @param array<ActionButton> $actionButtons
     */
    private function updateActions(array $actionButtons, string $appId, Context $context): void
    {
        $this->deleteExistingActions($appId, $context);
        $this->addActionButtons($actionButtons, $appId, $context);
    }

    private function updatePrivileges(?Permissions $permissions, string $roleId): void
    {
        $this->deleteExistingPrivileges($roleId);
        $this->addPrivileges($permissions, $roleId);
    }

    private function deleteExistingActions(string $appId, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('appId', $appId));

        /** @var array<string> $ids */
        $ids = $this->actionButtonRepository->searchIds($criteria, $context)->getIds();

        if (!empty($ids)) {
            $ids = array_map(static function (string $id): array {
                return ['id' => $id];
            }, $ids);

            $this->actionButtonRepository->delete($ids, $context);
        }
    }

    /**
     * @param array<ActionButton> $actionButtons
     */
    private function addActionButtons(array $actionButtons, string $appId, Context $context): void
    {
        if (empty($actionButtons)) {
            return;
        }

        $actionButtons = array_map(static function (ActionButton $actionButton) use ($appId): array {
            $actionButton = $actionButton->toArray();
            $actionButton['appId'] = $appId;

            return $actionButton;
        }, $actionButtons);

        $this->actionButtonRepository->create($actionButtons, $context);
    }

    /**
     * @param  array<string, string|array<string, string>> $metadata
     * @return array<string,                               string|array<string, string|bool>>
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

    private function deleteExistingPrivileges(string $roleId): void
    {
        $this->connection->executeQuery(
            'DELETE FROM `acl_resource` WHERE `acl_role_id` = :roleId',
            ['roleId' => Uuid::fromHexToBytes($roleId)]
        );
    }

    private function addPrivileges(?Permissions $permissions, string $roleId): void
    {
        if (!$permissions || empty($permissions->getPermissions())) {
            return;
        }

        $payload = $this->generatePrivileges($permissions->getPermissions(), $roleId);

        $this->connection->executeQuery(
            sprintf(
                'INSERT INTO `acl_resource` (`resource`, `privilege`, `acl_role_id`, `created_at`) VALUES %s;',
                $payload
            )
        );
    }

    /**
     * @param array<string, array<string>> $permissions
     */
    private function generatePrivileges(array $permissions, string $roleId): string
    {
        $privilegeValues = [];

        foreach ($permissions as $resource => $privileges) {
            $grantedPrivileges = $privileges;

            foreach ($privileges as $privilege) {
                $grantedPrivileges = array_merge($grantedPrivileges, $this->privilegeDependence[$privilege]);
            }

            foreach (array_unique($grantedPrivileges) as $privilege) {
                $privilegeValues[] = sprintf('("%s", "%s", UNHEX("%s"), NOW())', $resource, $privilege, $roleId);
            }
        }

        return implode(', ', $privilegeValues);
    }
}

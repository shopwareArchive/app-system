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

class AppLifecycle
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
     * @var array
     */
    private $privilegeDependence;

    /**
     * @var Connection
     */
    private $connection;

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
        $metadata = $manifest->getMetadata();
        $metadata['path'] = $manifest->getPath();
        $metadata['id'] = $appId = Uuid::randomHex();
        $roleId = Uuid::randomHex();

        $metadata = $this->enrichInstallMetadata($manifest, $metadata, $roleId);

        $appId = $this->updateMetadata($metadata, $appId, $roleId, $context);
        $this->addActionButtons($manifest->getAdmin()['actionButtons'] ?? [], $appId, $context);
        $this->addPrivileges($manifest->getPermissions(), $roleId);
    }

    public function update(Manifest $manifest, string $id, string $roleId, Context $context): void
    {
        $metadata = $manifest->getMetadata();
        $metadata['path'] = $manifest->getPath();
        $metadata['id'] = $id;

        $this->updateMetadata($metadata, $id, $roleId, $context);
        $this->updateActions($manifest->getAdmin()['actionButtons'] ?? [], $id, $context);
        $this->updatePrivileges($manifest->getPermissions(), $roleId);
    }

    public function delete(string $appId, Context $context): void
    {
        $this->appRepository->delete([['id' => $appId]], $context);
    }

    private function updateMetadata(array $metadata, string $id, string $roleId, Context $context): string
    {
        // ToDo handle import and saving of icons
        unset($metadata['icon']);

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($metadata): void {
            $this->appRepository->upsert([$metadata], $context);
        });

        return $id;
    }

    private function updateActions(array $actionButtons, string $appId, Context $context): void
    {
        $this->deleteExistingActions($appId, $context);
        $this->addActionButtons($actionButtons, $appId, $context);
    }

    private function updatePrivileges(array $privileges, string $roleId): void
    {
        $this->deleteExistingPrivileges($roleId);
        $this->addPrivileges($privileges, $roleId);
    }

    private function deleteExistingActions(string $appId, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('appId', $appId));

        /** @var string[] $ids */
        $ids = $this->actionButtonRepository->searchIds($criteria, $context)->getIds();

        if (!empty($ids)) {
            $ids = array_map(function (string $id): array {
                return ['id' => $id];
            }, $ids);

            $this->actionButtonRepository->delete($ids, $context);
        }
    }

    private function addActionButtons(array $actionButtons, string $appId, Context $context): void
    {
        if (empty($actionButtons)) {
            return;
        }

        $actionButtons = array_map(function ($actionButton) use ($appId): array {
            $actionButton['appId'] = $appId;

            return $actionButton;
        }, $actionButtons);

        $this->actionButtonRepository->create($actionButtons, $context);
    }

    private function enrichInstallMetadata(Manifest $manifest, array $metadata, string $roleId): array
    {
        $secret = AccessKeyHelper::generateSecretAccessKey();

        $metadata['integration'] = [
            'label' => $manifest->getMetadata()['name'],
            'writeAccess' => true,
            'accessKey' => AccessKeyHelper::generateAccessKey('integration'),
            'secretAccessKey' => $secret
        ];
        $metadata['aclRole'] = [
            'id' => $roleId,
            'name' => $manifest->getMetadata()['name'],
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

    private function addPrivileges(array $privileges, string $roleId): void
    {
        $payload = $this->generatePrivileges($privileges, $roleId);

        if (empty($payload)) {
            return;
        }

        $this->connection->executeQuery(
            sprintf(
                'INSERT INTO `acl_resource` (`resource`, `privilege`, `acl_role_id`, `created_at`) VALUES %s;',
                $payload
            )
        );
    }

    private function generatePrivileges(array $permissions, string $roleId): string
    {
        $privileges = [];

        foreach ($permissions as $resource => $privilege) {
            $privileges[] = sprintf('("%s", "%s", UNHEX("%s"), NOW())', $resource, $privilege, $roleId);

            foreach ($this->privilegeDependence[$privilege] as $dependedPrivilege) {
                $privileges[] = sprintf('("%s", "%s", UNHEX("%s"), NOW())', $resource, $dependedPrivilege, $roleId);
            }
        }

        return implode(', ', $privileges);
    }
}

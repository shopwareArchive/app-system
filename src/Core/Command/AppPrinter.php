<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Command;

use Shopware\Core\Framework\Api\Acl\Resource\AclResourceDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Swag\SaasConnect\Core\Content\App\AppCollection;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;

class AppPrinter
{
    private const PRIVILEGE_TO_HUMAN_READABLE = [
        AclResourceDefinition::PRIVILEGE_LIST => 'read',
        AclResourceDefinition::PRIVILEGE_DETAIL => 'read',
        AclResourceDefinition::PRIVILEGE_CREATE => 'write',
        AclResourceDefinition::PRIVILEGE_UPDATE => 'write',
        AclResourceDefinition::PRIVILEGE_DELETE => 'delete',
    ];

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    public function __construct(EntityRepositoryInterface $appRepository)
    {
        $this->appRepository = $appRepository;
    }

    public function printInstalledApps(ShopwareSaasStyle $io, Context $context): void
    {
        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $context)->getEntities();

        $appTable = [];

        foreach ($apps as $app) {
            $appTable[] = [
                $app->getName(),
                $app->getLabel(),
                $app->getVersion(),
                $app->getAuthor(),
            ];
        }

        $io->table(
            ['Plugin', 'Label', 'Version', 'Author'],
            $appTable
        );
    }

    public function printPermissions(Manifest $app, ShopwareSaasStyle $io, bool $install): void
    {
        $io->caution(sprintf(
                'App "%s" should be %s but requires following permissions:',
                $app->getMetadata()->getName(),
                $install ? 'installed' : 'updated')
        );

        $this->printPermissionTable($io, $this->reducePermissions($app));
    }

    /**
     * @return array<string, array<string>>
     */
    private function reducePermissions(Manifest $app): array
    {
        $permissions = [];
        foreach ($app->getPermissions()->getPermissions() as $resource => $privileges) {
            foreach ($privileges as $privilege) {
                $permissions[$resource][] = self::PRIVILEGE_TO_HUMAN_READABLE[$privilege];
            }
        }

        return $permissions;
    }

    /**
     * @param array<string, array<string>> $permissions
     */
    private function printPermissionTable(ShopwareSaasStyle $io, array $permissions): void
    {
        $permissionTable = [];
        foreach ($permissions as $resource => $privileges) {
            $permissionTable[] = [
                $resource,
                implode(', ', array_unique($privileges)),
            ];
        }

        $io->table(
            ['Resource', 'Privileges'],
            $permissionTable
        );
    }
}

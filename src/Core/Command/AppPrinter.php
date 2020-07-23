<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Swag\SaasConnect\Core\Content\App\AppCollection;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Framework\Api\Acl\AclPrivilegeCollection;

class AppPrinter
{
    private const PRIVILEGE_TO_HUMAN_READABLE = [
        AclPrivilegeCollection::PRIVILEGE_READ => 'read',
        AclPrivilegeCollection::PRIVILEGE_CREATE => 'write',
        AclPrivilegeCollection::PRIVILEGE_UPDATE => 'write',
        AclPrivilegeCollection::PRIVILEGE_DELETE => 'delete',
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

    /**
     * @param array<Manifest> $fails
     */
    public function printIncompleteInstallations(ShopwareSaasStyle $io, array $fails): void
    {
        if (empty($fails)) {
            return;
        }

        $appTable = [];

        /** @var Manifest $fail */
        foreach ($fails as $fail) {
            $appTable[] = [
                $fail->getMetadata()->getName(),
            ];
        }

        $io->table(
            ['Failed'],
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

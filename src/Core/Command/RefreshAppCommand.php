<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Command;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Api\Acl\Resource\AclResourceDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Swag\SaasConnect\Core\Content\App\AppService;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshAppCommand extends Command
{
    /**
     * @var AppService
     */
    private $appService;

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    public function __construct(AppService $appService, EntityRepositoryInterface $appRepository)
    {
        parent::__construct();

        $this->appService = $appService;
        $this->appRepository = $appRepository;
    }

    protected function configure(): void
    {
        $this->setName('app:refresh')
            ->setDescription('Refreshes the installed Apps')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the refreshing of apps, apps will automatically be granted all requested permissions.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareStyle($input, $output);

        $context = Context::createDefaultContext();

        if (!$input->getOption('force')) {
            if (!$this->grantPermissions($io, $context)) {
                return 1;
            }
        }

        $this->appService->refreshApps($context);

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

        return 0;
    }

    private function grantPermissions(ShopwareStyle $io, Context $context): bool
    {
        $refreshableApps = $this->appService->getRefreshableApps($context);

        if (count($refreshableApps['install']) === 0 &&
            count($refreshableApps['update']) === 0 &&
            count($refreshableApps['delete']) === 0) {
            $io->note('Nothing to install, update or delete.');

            return true;
        }

        $confirm = $io->confirm(
            sprintf(
                '%d apps will be installed, %d apps will be updated and %d apps will be deleted. Do you want to continue?',
                count($refreshableApps['install']),
                count($refreshableApps['update']),
                count($refreshableApps['delete'])
            )
        );

        if (!$confirm) {
            return $this->abort($io);
        }

        /** @var Manifest $app */
        foreach ($refreshableApps['install'] as $app) {
            if (!$this->grantPermissionsForApp($app, $io)) {
                return false;
            }
        }

        /** @var Manifest $app */
        foreach ($refreshableApps['update'] as $app) {
            if (!$this->grantPermissionsForApp($app, $io, false)) {
                return false;
            }
        }

        return true;
    }

    private function grantPermissionsForApp(Manifest $app, ShopwareStyle $io, bool $install = true): bool
    {
        if (!empty($app->getPermissions())) {
            $io->caution(sprintf('App "%s" should be %s but requires following permissions:', $app->getMetadata()['name'], $install ? 'installed' : 'updated'));
            $this->printPermissions($app, $io);

            if (!$io->confirm(
                sprintf('Do you want to grant these permissions for app "%s"?', $app->getMetadata()['name']),
                false
            )
            ) {
                return $this->abort($io);
            }
        }

        return true;
    }

    private function abort(ShopwareStyle $io): bool
    {
        $io->error('Aborting due to user input.');

        return false;
    }

    private function printPermissions(Manifest $app, ShopwareStyle $io): void
    {
        $permissions = [];
        foreach ($app->getPermissions() as $resource => $privileges) {
            foreach ($privileges as $privilege) {
                switch ($privilege) {
                    case AclResourceDefinition::PRIVILEGE_LIST:
                    case AclResourceDefinition::PRIVILEGE_DETAIL:
                        $permissions[$resource][] = 'read';
                        break;
                    case AclResourceDefinition::PRIVILEGE_CREATE:
                    case AclResourceDefinition::PRIVILEGE_UPDATE:
                        $permissions[$resource][] = 'write';
                        break;
                    case AclResourceDefinition::PRIVILEGE_DELETE:
                        $permissions[$resource][] = 'delete';
                        break;
                }
            }
        }

        $permissionTable = [];
        foreach ($permissions as $resource => $privileges) {
            $permissionTable[] = [
                $resource,
                implode(', ', array_unique($privileges))
            ];
        }

        $io->table(
            ['Resource', 'Privileges'],
            $permissionTable
        );
    }
}

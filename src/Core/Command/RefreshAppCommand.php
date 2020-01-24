<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Command;

use Shopware\Core\Framework\Context;
use Swag\SaasConnect\Core\Command\Exception\UserAbortedCommandException;
use Swag\SaasConnect\Core\Content\App\AppService;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Content\App\RefreshableAppDryRun;
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
     * @var AppPrinter
     */
    private $appPrinter;

    public function __construct(AppService $appService, AppPrinter $appPrinter)
    {
        parent::__construct();

        $this->appService = $appService;
        $this->appPrinter = $appPrinter;
    }

    protected function configure(): void
    {
        $this->setName('app:refresh')
            ->setDescription('Refreshes the installed Apps')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force the refreshing of apps, apps will automatically be granted all requested permissions.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareSaasStyle($input, $output);

        $context = Context::createDefaultContext();

        $refreshableApps = $this->appService->getRefreshableAppInfo($context);
        if ($refreshableApps->isEmpty()) {
            $io->note('Nothing to install, update or delete.');

            return 0;
        }

        if (!$input->getOption('force')) {
            try {
                $this->grantPermissions($refreshableApps, $io);
            } catch (UserAbortedCommandException $e) {
                $io->error('Aborting due to user input.');

                return 1;
            }
        }

        $this->appService->refreshApps($context);

        $this->appPrinter->printInstalledApps($io, $context);

        return 0;
    }

    private function grantPermissions(RefreshableAppDryRun $refreshableApps, ShopwareSaasStyle $io): void
    {
        $io->confirmOrThrow(
            sprintf(
                '%d apps will be installed, %d apps will be updated and
                %d apps will be deleted. Do you want to continue?',
                count($refreshableApps->getToBeInstalled()),
                count($refreshableApps->getToBeUpdated()),
                count($refreshableApps->getToBeDeleted())
            ),
            new UserAbortedCommandException()
        );

        /** @var Manifest $app */
        foreach ($refreshableApps->getToBeInstalled() as $app) {
            $this->grantPermissionsForApp($app, $io);
        }

        /** @var Manifest $app */
        foreach ($refreshableApps->getToBeUpdated() as $app) {
            $this->grantPermissionsForApp($app, $io, false);
        }
    }

    private function grantPermissionsForApp(Manifest $app, ShopwareSaasStyle $io, bool $install = true): void
    {
        if ($app->getPermissions()) {
            $this->appPrinter->printPermissions($app, $io, $install);

            $io->confirmOrThrow(
                sprintf('Do you want to grant these permissions for app "%s"?', $app->getMetadata()->getName()),
                new UserAbortedCommandException(),
                false
            );
        }
    }
}

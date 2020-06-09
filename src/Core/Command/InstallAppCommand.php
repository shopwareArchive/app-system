<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Command;

use Shopware\Core\Framework\Context;
use Swag\SaasConnect\Core\Command\Exception\UserAbortedCommandException;
use Swag\SaasConnect\Core\Content\App\Lifecycle\AppLifecycle;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallAppCommand extends Command
{
    /**
     * @var string
     */
    private $appDir;

    /**
     * @var AppLifecycle
     */
    private $appLifecycle;

    /**
     * @var AppPrinter
     */
    private $appPrinter;

    public function __construct(string $appDir, AppLifecycle $appLifecycle, AppPrinter $appPrinter)
    {
        parent::__construct();
        $this->appDir = $appDir;
        $this->appLifecycle = $appLifecycle;
        $this->appPrinter = $appPrinter;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareSaasStyle($input, $output);

        $manifest = $this->getManifest($input, $io);

        if (!$manifest) {
            return 1;
        }

        if (!$input->getOption('force')) {
            try {
                $this->checkPermissions($manifest, $io);
            } catch (UserAbortedCommandException $e) {
                $io->error('Aborting due to user input.');

                return 1;
            }
        }

        $this->appLifecycle->install($manifest, Context::createDefaultContext());

        $io->success('App installed successfully.');

        return 0;
    }

    protected function configure(): void
    {
        $this->setName('app:install')
            ->setDescription('Installs the app in the folder with the given name')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the app, has also to be the name of the folder under
                which the app can be found under custom/apps'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force the install of the app, it will automatically grant all requested permissions.'
            );
    }

    private function getManifest(InputInterface $input, ShopwareSaasStyle $io): ?Manifest
    {
        /** @var string $name */
        $name = $input->getArgument('name');
        $manifestPath = sprintf('%s/%s/manifest.xml', $this->appDir, $name);
        if (!is_file($manifestPath)) {
            $io->error(
                sprintf(
                    'No app with name "%s" found.
                    Please make sure that a folder with that name exist in the custom/apps folder
                    and that it contains a manifest.xml file.',
                    $name
                )
            );

            return null;
        }

        return Manifest::createFromXmlFile($manifestPath);
    }

    private function checkPermissions(Manifest $manifest, ShopwareSaasStyle $io): void
    {
        if ($manifest->getPermissions()) {
            $this->appPrinter->printPermissions($manifest, $io, true);

            $io->confirmOrThrow(
                sprintf('Do you want to grant these permissions for app "%s"?', $manifest->getMetadata()->getName()),
                new UserAbortedCommandException(),
                false
            );
        }
    }
}

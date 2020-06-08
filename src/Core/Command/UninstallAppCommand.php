<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Swag\SaasConnect\Core\Content\App\AppEntity;
use Swag\SaasConnect\Core\Content\App\Lifecycle\AppLifecycle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UninstallAppCommand extends Command
{
    /**
     * @var AppLifecycle
     */
    private $appLifecycle;

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    public function __construct(AppLifecycle $appLifecycle, EntityRepositoryInterface $appRepository)
    {
        parent::__construct();
        $this->appLifecycle = $appLifecycle;
        $this->appRepository = $appRepository;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareSaasStyle($input, $output);

        /** @var string $name */
        $name = $input->getArgument('name');

        $context = Context::createDefaultContext();
        $app = $this->getAppByName($name, $context);

        if (!$app) {
            $io->error(sprintf('No app with name "%s" installed.', $name));

            return 1;
        }

        $this->appLifecycle->delete($app->getName(), ['id' => $app->getId()], $context);

        $io->success('App uninstalled successfully.');

        return 0;
    }

    protected function configure(): void
    {
        $this->setName('app:uninstall')
            ->setDescription('Uninstalls the app')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the app');
    }

    private function getAppByName(string $name, Context $context): ?AppEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));

        return $this->appRepository->search($criteria, $context)->first();
    }
}

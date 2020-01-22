<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Command;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Swag\SaasConnect\Core\Content\App\AppService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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
            ->setDescription('Refreshes the installed Apps');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareStyle($input, $output);

        $context = Context::createDefaultContext();
        $this->appService->refreshApps($context);

        $apps = $this->appRepository->search(new Criteria(), $context)->getEntities();

        $pluginTable = [];

        foreach ($apps as $app) {
            $pluginTable[] = [
                $app->getName(),
                $app->getLabel(),
                $app->getVersion(),
                $app->getAuthor(),
            ];
        }

        $io->table(
            ['Plugin', 'Label', 'Version', 'Author'],
            $pluginTable
        );

        return 0;
    }
}

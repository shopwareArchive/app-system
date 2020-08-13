<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Command;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
use Swag\SaasConnect\Core\Framework\AppUrlChangeResolver\AppUrlChangeResolverStrategy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResolveAppUrlChangeCommand extends Command
{
    /**
     * @var AppUrlChangeResolverStrategy
     */
    private $appUrlChangeResolverStrategy;

    public function __construct(AppUrlChangeResolverStrategy $appUrlChangeResolverStrategy)
    {
        parent::__construct();

        $this->appUrlChangeResolverStrategy = $appUrlChangeResolverStrategy;
    }

    protected function configure(): void
    {
        $this->setName('app:url-change:resolve')
            ->setDescription('Resolve changes in the app url and how the app system should handle it.')
            ->addArgument('strategy', InputArgument::OPTIONAL, 'The strategy that should be applied');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareStyle($input, $output);

        $availableStrategies = $this->appUrlChangeResolverStrategy->getAvailableStrategies();
        /** @var string|null $strategy */
        $strategy = $input->getArgument('strategy');

        if ($strategy === null || !array_key_exists($strategy, $availableStrategies)) {
            if ($strategy !== null) {
                $io->note('Strategy with name: "' . $strategy . '" not found.');
            }

            $strategy = $io->choice(
                'Choose what strategy should be applied, to resolve the app url change?',
                $availableStrategies
            );
        }

        $this->appUrlChangeResolverStrategy->resolve($strategy, Context::createDefaultContext());

        $io->success('Strategy "' . $strategy . '" was applied successfully');

        return 0;
    }
}

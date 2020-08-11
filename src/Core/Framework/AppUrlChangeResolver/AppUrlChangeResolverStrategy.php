<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\AppUrlChangeResolver;

use Shopware\Core\Framework\Context;

class AppUrlChangeResolverStrategy
{
    /**
     * @var iterable|array<AppUrlChangeResolverInterface>
     */
    private $strategies;

    /**
     * @param iterable|array<AppUrlChangeResolverInterface> $strategies
     */
    public function __construct(iterable $strategies)
    {
        $this->strategies = $strategies;
    }

    public function resolve(string $strategyName, Context $context): void
    {
        /** @var AppUrlChangeResolverInterface $strategy */
        foreach ($this->strategies as $strategy) {
            if ($strategy->getName() === $strategyName) {
                $strategy->resolve($context);

                return;
            }
        }

        throw new AppUrlChangeResolverNotFoundException($strategyName);
    }
}

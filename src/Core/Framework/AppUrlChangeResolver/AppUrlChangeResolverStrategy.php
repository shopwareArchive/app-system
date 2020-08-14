<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\AppUrlChangeResolver;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swag\SaasConnect\Core\Framework\ShopId\ShopIdProvider;

class AppUrlChangeResolverStrategy
{
    /**
     * @var iterable|array<AppUrlChangeResolverInterface>
     */
    private $strategies;

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @param iterable|array<AppUrlChangeResolverInterface> $strategies
     */
    public function __construct(iterable $strategies, SystemConfigService $systemConfigService)
    {
        $this->strategies = $strategies;
        $this->systemConfigService = $systemConfigService;
    }

    public function resolve(string $strategyName, Context $context): void
    {
        if (!$this->systemConfigService->get(ShopIdProvider::SHOP_DOMAIN_CHANGE_CONFIG_KEY)) {
            throw new NoAppUrlChangeDetectedException();
        }

        /** @var AppUrlChangeResolverInterface $strategy */
        foreach ($this->strategies as $strategy) {
            if ($strategy->getName() === $strategyName) {
                $strategy->resolve($context);

                return;
            }
        }

        throw new AppUrlChangeResolverNotFoundException($strategyName);
    }

    /**
     * @return array<string, string>
     */
    public function getAvailableStrategies(): array
    {
        $strategies = [];

        /** @var AppUrlChangeResolverInterface $strategy */
        foreach ($this->strategies as $strategy) {
            $strategies[$strategy->getName()] = $strategy->getDescription();
        }

        return $strategies;
    }
}

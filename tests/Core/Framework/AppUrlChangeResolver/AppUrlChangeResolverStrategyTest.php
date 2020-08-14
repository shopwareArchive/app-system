<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Framework\AppUrlChangeResolver;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Swag\SaasConnect\Core\Framework\AppUrlChangeResolver\AppUrlChangeResolverInterface;
use Swag\SaasConnect\Core\Framework\AppUrlChangeResolver\AppUrlChangeResolverNotFoundException;
use Swag\SaasConnect\Core\Framework\AppUrlChangeResolver\AppUrlChangeResolverStrategy;
use Swag\SaasConnect\Core\Framework\AppUrlChangeResolver\NoAppUrlChangeDetectedException;
use Swag\SaasConnect\Core\Framework\ShopId\ShopIdProvider;
use Swag\SaasConnect\Test\SystemConfigTestBehaviour;

class AppUrlChangeResolverStrategyTest extends TestCase
{
    use IntegrationTestBehaviour;
    use SystemConfigTestBehaviour;

    /**
     * @var MockObject|AppUrlChangeResolverInterface
     */
    private $firstStrategy;

    /**
     * @var MockObject|AppUrlChangeResolverInterface
     */
    private $secondStrategy;

    /**
     * @var AppUrlChangeResolverStrategy
     */
    private $appUrlChangedResolverStrategy;

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function setUp(): void
    {
        $this->firstStrategy = $this->createMock(AppUrlChangeResolverInterface::class);
        $this->firstStrategy->method('getName')
            ->willReturn('FirstStrategy');

        $this->secondStrategy = $this->createMock(AppUrlChangeResolverInterface::class);
        $this->secondStrategy->method('getName')
            ->willReturn('SecondStrategy');

        $this->systemConfigService = $this->getContainer()->get(SystemConfigService::class);

        $this->appUrlChangedResolverStrategy = new AppUrlChangeResolverStrategy([
            $this->firstStrategy,
            $this->secondStrategy,
        ], $this->systemConfigService);
    }

    public function testItThrowsWhenAppUrlChangeIsNotDetected(): void
    {
        $this->firstStrategy->expects(static::never())
            ->method('resolve');

        $this->secondStrategy->expects(static::never())
            ->method('resolve');

        static::expectException(NoAppUrlChangeDetectedException::class);
        $this->appUrlChangedResolverStrategy->resolve('FirstStrategy', Context::createDefaultContext());
    }

    public function testItCallsRightStrategy(): void
    {
        $this->systemConfigService->set(ShopIdProvider::SHOP_DOMAIN_CHANGE_CONFIG_KEY, true);

        $this->firstStrategy->expects(static::once())
            ->method('resolve');

        $this->secondStrategy->expects(static::never())
            ->method('resolve');

        $this->appUrlChangedResolverStrategy->resolve('FirstStrategy', Context::createDefaultContext());
    }

    public function testItThrowsOnUnknownStrategy(): void
    {
        $this->systemConfigService->set(ShopIdProvider::SHOP_DOMAIN_CHANGE_CONFIG_KEY, true);

        $this->firstStrategy->expects(static::never())
            ->method('resolve');

        $this->secondStrategy->expects(static::never())
            ->method('resolve');

        static::expectException(AppUrlChangeResolverNotFoundException::class);
        $this->appUrlChangedResolverStrategy->resolve('ThirdStrategy', Context::createDefaultContext());
    }

    public function testGetAvailableStrategies(): void
    {
        $this->firstStrategy->expects(static::once())
            ->method('getDescription')
            ->willReturn('first description');

        $this->secondStrategy->expects(static::once())
            ->method('getDescription')
            ->willReturn('second description');

        static::assertEquals([
            'FirstStrategy' => 'first description',
            'SecondStrategy' => 'second description',
        ], $this->appUrlChangedResolverStrategy->getAvailableStrategies());
    }
}

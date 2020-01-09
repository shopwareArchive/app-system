<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SaasConnect\Core\Content\App\AppCollection;
use Swag\SaasConnect\Core\Content\App\AppLoader;
use Swag\SaasConnect\Core\Content\App\AppService;

class AppServiceTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var AppService
     */
    private $appService;

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    /**
     * @var Context
     */
    private $context;

    public function setUp(): void
    {
        $this->appRepository = $this->getContainer()->get('app.repository');
        $this->appService = new AppService(
            $this->appRepository,
            new AppLoader(__DIR__ . '/Manifest/_fixtures/test')
        );
        $this->context = Context::createDefaultContext();
    }

    public function testRefreshInstallsNewApp(): void
    {
        $this->appService->refreshApps($this->context);

        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $this->context)->getEntities();

        static::assertCount(1, $apps);
        static::assertEquals('SwagApp', $apps->first()->getName());
    }

    public function testRefreshUpdatesApp(): void
    {
        $this->appRepository->create([[
            'name' => 'SwagApp',
            'path' => __DIR__ . '/Manifest/_fixtures/test',
            'version' => '0.0.1',
            'label' => 'test'
        ]], $this->context);

        $this->appService->refreshApps($this->context);

        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $this->context)->getEntities();

        static::assertCount(1, $apps);
        static::assertEquals('SwagApp', $apps->first()->getName());
        static::assertEquals('1.0.0', $apps->first()->getVersion());
        static::assertNotEquals('test', $apps->first()->getTranslation('label'));
    }

    public function testRefreshAppIsUntouched(): void
    {
        $this->appRepository->create([[
            'name' => 'SwagApp',
            'path' => __DIR__ . '/Manifest/_fixtures/test',
            'version' => '1.0.0',
            'label' => 'test'
        ]], $this->context);

        $this->appService->refreshApps($this->context);

        /** @var AppCollection $apps */
        $apps = $this->appRepository->search(new Criteria(), $this->context)->getEntities();

        static::assertCount(1, $apps);
        static::assertEquals('SwagApp', $apps->first()->getName());
        static::assertEquals('1.0.0', $apps->first()->getVersion());
        static::assertEquals('test', $apps->first()->getTranslation('label'));
    }

    public function testRefreshDeletesApp(): void
    {
        $appId = Uuid::randomHex();
        $this->appRepository->create([[
            'id' => $appId,
            'name' => 'Test',
            'path' => __DIR__ . '/Manifest/_fixtures/test',
            'version' => '0.0.1',
            'label' => 'test'
        ]], $this->context);

        $this->appService->refreshApps($this->context);

        /** @var AppCollection $apps */
        $apps = $this->appRepository->searchIds(new Criteria([$appId]), $this->context)->getIds();

        static::assertCount(0, $apps);
    }
}

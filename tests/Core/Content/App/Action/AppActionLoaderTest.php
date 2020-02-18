<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Action;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SaasConnect\Core\Content\App\Action\AppActionLoader;
use Swag\SaasConnect\Core\Content\App\Aggregate\ActionButton\ActionButtonEntity;
use Swag\SaasConnect\Test\AppSystemTestBehaviour;

class AppActionLoaderTest extends TestCase
{
    use IntegrationTestBehaviour;
    use AppSystemTestBehaviour;

    public function testCreateAppActionReturnCorrectData(): void
    {
        $actionLoader = $this->getContainer()->get(AppActionLoader::class);

        /** @var EntityRepository $actionRepo */
        $actionRepo = $this->getContainer()->get('swag_app_action_button.repository');
        $this->loadAppsFromDir(__DIR__ . '/../Manifest/_fixtures/test');

        $criteria = (new Criteria())
            ->setLimit(1)
            ->addAssociation('app')
            ->addAssociation('app.integration');

        $actionCollection = $actionRepo->search($criteria, Context::createDefaultContext());
        /** @var ActionButtonEntity $action */
        $action = $actionCollection->first();

        $ids = [Uuid::randomHex()];
        $result = $actionLoader->loadAppAction($action->getId(), $ids, Context::createDefaultContext());

        $expected = [
            'source' => [
                'url' => getenv('APP_URL'),
                'appVersion' => $action->getApp()->getVersion(),
                'apiKey' => $action->getApp()->getIntegration()->getAccessKey(),
                'secretKey' => $action->getApp()->getAccessToken(),
            ],
            'data' => [
                'ids' => $ids,
                'entity' => $action->getEntity(),
                'action' => $action->getAction(),
            ],
        ];

        static::assertEquals($expected, $result->asPayload());
        static::assertEquals($action->getUrl(), $result->getTargetUrl());
    }
}

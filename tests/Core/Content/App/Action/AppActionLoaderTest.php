<?php


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
        $actionRepo = $this->getContainer()->get('app_action_button.repository');
        $this->loadAppsFromDir(__DIR__ . '/../Manifest/_fixtures/test');

        $criteria = (new Criteria())
            ->setLimit(1)
            ->addAssociation('app');

        $actionCollection = $actionRepo->search($criteria, Context::createDefaultContext());
        /** @var ActionButtonEntity $action */
        $action = $actionCollection->first();

        $ids = [Uuid::randomHex()];
        $result = $actionLoader->loadAppAction($action->getId(), $ids, Context::createDefaultContext());

        $expected = [
            'source' => [
                'url' => getenv('APP_URL'),
                'appVersion' => $action->getApp()->getVersion(),
                'apiKey' => '',
            ],
            'data' => [
                'ids' => $ids,
                'entity' => $action->getEntity(),
                'action' => $action->getAction(),
            ]
        ];

        static::assertEquals($expected, $result->asPayload());
        static::assertEquals($action->getUrl(), $result->getTargetUrl());
    }
}

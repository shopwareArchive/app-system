<?php

namespace Swag\SaasConnect\Test;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Class CiTest
 * @todo Remove once real tests exist
 */
class CiTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testStuff()
    {
        /** @var EntityRepository $repo */
        $repo = $this->getContainer()->get('media.repository');
        $id = Uuid::randomHex();
        $repo->create([
            [
                'id' => $id,
                'fileName' => 'test123'
            ]
        ], Context::createDefaultContext());

        $criteria = new Criteria([$id]);
        $result = $repo->search($criteria, Context::createDefaultContext());
        static::assertEquals(1, $result->count());
    }
}

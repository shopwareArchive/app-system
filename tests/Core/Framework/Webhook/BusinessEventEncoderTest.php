<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Framework\Webhook;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Event\BusinessEventInterface;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\System\Tax\TaxCollection;
use Shopware\Core\System\Tax\TaxEntity;
use Swag\SaasConnect\Core\Framework\Webhook\BusinessEventEncoder;
use Swag\SaasConnect\Test\Core\Framework\Webhook\_fixtures\BusinessEvents\ArrayBusinessEvent;
use Swag\SaasConnect\Test\Core\Framework\Webhook\_fixtures\BusinessEvents\CollectionBusinessEvent;
use Swag\SaasConnect\Test\Core\Framework\Webhook\_fixtures\BusinessEvents\EntityBusinessEvent;
use Swag\SaasConnect\Test\Core\Framework\Webhook\_fixtures\BusinessEvents\InvalidAvailableDataBusinessEvent;
use Swag\SaasConnect\Test\Core\Framework\Webhook\_fixtures\BusinessEvents\InvalidTypeBusinessEvent;
use Swag\SaasConnect\Test\Core\Framework\Webhook\_fixtures\BusinessEvents\ScalarBusinessEvent;
use Swag\SaasConnect\Test\Core\Framework\Webhook\_fixtures\BusinessEvents\StructuredArrayObjectBusinessEvent;
use Swag\SaasConnect\Test\Core\Framework\Webhook\_fixtures\BusinessEvents\StructuredObjectBusinessEvent;
use Swag\SaasConnect\Test\Core\Framework\Webhook\_fixtures\BusinessEvents\UnstructuredObjectBusinessEvent;

class BusinessEventEncoderTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var BusinessEventEncoder
     */
    private $businessEventEncoder;

    public function setUp(): void
    {
        $this->businessEventEncoder = $this->getContainer()->get(BusinessEventEncoder::class);
    }

    /**
     * @dataProvider getEvents
     */
    public function testScalarEvents(BusinessEventInterface $event): void
    {
        static::assertEquals($event->getEncodeValues(), $this->businessEventEncoder->encode($event));
    }

    public function getEvents(): array
    {
        return [
            [new ScalarBusinessEvent()],
            [new StructuredObjectBusinessEvent()],
            [new StructuredArrayObjectBusinessEvent()],
            [new UnstructuredObjectBusinessEvent()],
            [new EntityBusinessEvent($this->getTaxEntity())],
            [new CollectionBusinessEvent($this->getTaxCollection())],
            [new ArrayBusinessEvent($this->getTaxCollection())],
        ];
    }

    public function testInvalidType(): void
    {
        static::expectException(\RuntimeException::class);
        $this->businessEventEncoder->encode(new InvalidTypeBusinessEvent());
    }

    public function testInvalidAvailableData(): void
    {
        static::expectException(\RuntimeException::class);
        $this->businessEventEncoder->encode(new InvalidAvailableDataBusinessEvent());
    }

    private function getTaxEntity(): TaxEntity
    {
        /** @var EntityRepositoryInterface $taxRepo */
        $taxRepo = $this->getContainer()->get('tax.repository');

        return $taxRepo->search(new Criteria(), Context::createDefaultContext())->first();
    }

    private function getTaxCollection(): TaxCollection
    {
        /** @var EntityRepositoryInterface $taxRepo */
        $taxRepo = $this->getContainer()->get('tax.repository');

        return $taxRepo->search(new Criteria(), Context::createDefaultContext())->getEntities();
    }
}

<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Framework\Webhook\_fixtures\BusinessEvents;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\BusinessEventInterface;
use Shopware\Core\Framework\Event\EventData\EntityType;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\Tax\TaxDefinition;
use Shopware\Core\System\Tax\TaxEntity;

class EntityBusinessEvent implements BusinessEventInterface, BusinessEventEncoderTestInterface
{
    /**
     * @var TaxEntity
     */
    private $tax;

    public function __construct(TaxEntity $tax)
    {
        $this->tax = $tax;
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('tax', new EntityType(TaxDefinition::class));
    }

    public function getEncodeValues(): array
    {
        return [
            'tax' => [
                'id' => $this->tax->getId(),
                '_uniqueIdentifier' => $this->tax->getId(),
                'versionId' => null,
                'name' => $this->tax->getName(),
                'taxRate' => (int) $this->tax->getTaxRate(),
                'products' => null,
                'customFields' => null,
                'rules' => null,
                'translated' => [],
                'createdAt' => $this->tax->getCreatedAt()->format(DATE_ATOM),
                'updatedAt' => null,
                'extensions' => [
                    'foreignKeys' => (new ArrayEntity())->jsonSerialize(),
                ],
            ],
        ];
    }

    public function getName(): string
    {
        return 'test';
    }

    public function getContext(): Context
    {
        return Context::createDefaultContext();
    }

    public function getTax(): TaxEntity
    {
        return $this->tax;
    }
}

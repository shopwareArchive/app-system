<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Framework\Webhook\_fixtures\BusinessEvents;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\BusinessEventInterface;
use Shopware\Core\Framework\Event\EventData\EntityCollectionType;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\Tax\TaxCollection;
use Shopware\Core\System\Tax\TaxDefinition;

class CollectionBusinessEvent implements BusinessEventInterface, BusinessEventEncoderTestInterface
{
    /**
     * @var TaxCollection
     */
    private $taxes;

    public function __construct(TaxCollection $taxes)
    {
        $this->taxes = $taxes;
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('taxes', new EntityCollectionType(TaxDefinition::class));
    }

    public function getEncodeValues(): array
    {
        $taxes = [];

        foreach ($this->taxes->getElements() as $tax) {
            $taxes[] = [
                'id' => $tax->getId(),
                '_uniqueIdentifier' => $tax->getId(),
                'versionId' => null,
                'name' => $tax->getName(),
                'taxRate' => (int) $tax->getTaxRate(),
                'products' => null,
                'customFields' => null,
                'translated' => [],
                'createdAt' => $tax->getCreatedAt()->format(DATE_ATOM),
                'updatedAt' => null,
                'extensions' => [
                    'foreignKeys' => (new ArrayEntity())->jsonSerialize(),
                ],
            ];
        }

        return [
            'taxes' => $taxes,
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

    public function getTaxes(): TaxCollection
    {
        return $this->taxes;
    }
}

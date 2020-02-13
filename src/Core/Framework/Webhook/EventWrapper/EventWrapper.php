<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\Webhook\EventWrapper;

use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductPrice\ProductPriceDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventWrapper implements EventSubscriberInterface
{
    private const HOOKABLE_ENTITIES = [
        ProductDefinition::ENTITY_NAME,
        ProductPriceDefinition::ENTITY_NAME,
        CategoryDefinition::ENTITY_NAME,
        SalesChannelDefinition::ENTITY_NAME,
    ];

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EntityWrittenContainerEvent::class => 'wrapEntityWrittenEvent',
        ];
    }

    public function wrapEntityWrittenEvent(EntityWrittenContainerEvent $event): void
    {
        foreach (self::HOOKABLE_ENTITIES as $entity) {
            $writtenEvent = $event->getEventByEntityName($entity);

            if (!$writtenEvent) {
                continue;
            }

            $this->eventDispatcher->dispatch(new HookableEntityWrittenEvent($writtenEvent));
        }
    }
}

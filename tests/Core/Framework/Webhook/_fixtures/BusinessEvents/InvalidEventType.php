<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Framework\Webhook\_fixtures\BusinessEvents;

use Shopware\Core\Framework\Event\EventData\EventDataType;

class InvalidEventType implements EventDataType
{
    public function toArray(): array
    {
        return [
            'type' => 'invalid',
        ];
    }
}

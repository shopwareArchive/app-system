<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Event;

class AppDeactivatedEvent extends AppChangedEvent
{
    public const NAME = 'app.deactivated';

    public function getName(): string
    {
        return self::NAME;
    }
}

<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Event;

class AppActivatedEvent extends AppChangedEvent
{
    public const NAME = 'app.activated';

    public function getName(): string
    {
        return self::NAME;
    }
}

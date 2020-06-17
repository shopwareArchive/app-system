<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Event;

class AppUpdatedEvent extends AppInstalledEvent
{
    public const NAME = 'app_updated';

    public function getName(): string
    {
        return self::NAME;
    }
}

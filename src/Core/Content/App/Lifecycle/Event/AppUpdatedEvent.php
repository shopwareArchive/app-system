<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Event;

class AppUpdatedEvent extends ManifestChangedEvent
{
    public const NAME = 'app.updated';

    public function getName(): string
    {
        return self::NAME;
    }
}

<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Event;

class AppInstalledEvent extends ManifestChangedEvent
{
    public const NAME = 'app.installed';

    public function getName(): string
    {
        return self::NAME;
    }
}

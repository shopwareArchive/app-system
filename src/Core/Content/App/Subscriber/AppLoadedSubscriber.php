<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Swag\SaasConnect\Core\Content\App\AppEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AppLoadedSubscriber implements EventSubscriberInterface
{
    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'saas_app.loaded' => 'unserialize',
        ];
    }

    public function unserialize(EntityLoadedEvent $event): void
    {
        /** @var AppEntity $app */
        foreach ($event->getEntities() as $app) {
            $iconRaw = $app->getIconRaw();

            if ($iconRaw !== null) {
                $app->setIcon(base64_encode($iconRaw));
            }
        }
    }
}

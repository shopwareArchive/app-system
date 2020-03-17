<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\Webhook;

use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use Shopware\Core\Framework\Event\BusinessEvent;
use Shopware\Core\Framework\Event\BusinessEventInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WebhookDispatcher implements EventDispatcherInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array|null
     */
    private $webhooks;

    /**
     * @var Client
     */
    private $guzzle;

    /**
     * @var BusinessEventEncoder
     */
    private $eventEncoder;

    /**
     * @var string
     */
    private $shopUrl;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        Connection $connection,
        Client $guzzle,
        BusinessEventEncoder $eventEncoder,
        string $shopUrl
    ) {
        $this->dispatcher = $dispatcher;
        $this->connection = $connection;
        $this->guzzle = $guzzle;
        $this->eventEncoder = $eventEncoder;
        $this->shopUrl = $shopUrl;
    }

    /**
     * @param object $event
     */
    public function dispatch($event, ?string $eventName = null): object
    {
        $event = $this->dispatcher->dispatch($event, $eventName);

        if (!$event instanceof BusinessEventInterface && !$event instanceof Hookable) {
            return $event;
        }

        // BusinessEvent are the generic Events that get wrapped around the specific events
        // we don't want to dispatch those to the webhooks
        if ($event instanceof BusinessEvent) {
            return $event;
        }

        $this->callWebhooks($event->getName(), $event);

        return $event;
    }

    /**
     * @param string $eventName
     * @param callable $listener
     * @param int $priority
     */
    public function addListener($eventName, $listener, $priority = 0): void
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->dispatcher->addSubscriber($subscriber);
    }

    /**
     * @param string $eventName
     * @param callable $listener
     */
    public function removeListener($eventName, $listener): void
    {
        $this->dispatcher->removeListener($eventName, $listener);
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->dispatcher->removeSubscriber($subscriber);
    }

    /**
     * @param string|null $eventName
     */
    public function getListeners($eventName = null): array
    {
        return $this->dispatcher->getListeners($eventName);
    }

    /**
     * @param string $eventName
     * @param callable $listener
     */
    public function getListenerPriority($eventName, $listener): ?int
    {
        return $this->dispatcher->getListenerPriority($eventName, $listener);
    }

    /**
     * @param string|null $eventName
     */
    public function hasListeners($eventName = null): bool
    {
        return $this->dispatcher->hasListeners($eventName);
    }

    public function clearInternalCache(): void
    {
        $this->webhooks = null;
    }

    /**
     * @param BusinessEventInterface|Hookable $event
     */
    private function callWebhooks(string $eventName, $event): void
    {
        if (!array_key_exists($eventName, $this->getWebhooks())) {
            return;
        }

        $payload = $this->getPayloadFor($event);
        $requests = [];
        foreach ($this->getWebhooks()[$eventName] as $webhookConfig) {
            $payload = ['payload' => $payload];
            $payload['sourceUrl'] = $this->shopUrl;

            if ($webhookConfig['version']) {
                $payload['appVersion'] = $webhookConfig['version'];
            }

            if ($webhookConfig['access_token'] && $webhookConfig['access_key']) {
                $payload['apiKey'] = $webhookConfig['access_key'];
                $payload['secretKey'] = $webhookConfig['access_token'];
            }

            /** @var string $jsonPayload */
            $jsonPayload = \json_encode($payload);

            $requests[] = new Request('POST', $webhookConfig['url'], [], $jsonPayload);
        }

        $pool = new Pool($this->guzzle, $requests);
        $pool->promise()->wait();
    }

    private function getWebhooks(): array
    {
        if ($this->webhooks) {
            return $this->webhooks;
        }

        $result = $this->connection->fetchAll('
            SELECT `webhook`.`event_name`, `webhook`.`url`, `app`.`access_token`, `app`.`version`, `integration`.`access_key`
            FROM `swag_webhook` AS `webhook`
            LEFT JOIN `swag_app` AS `app` ON `webhook`.`app_id` = `app`.`id`
            LEFT JOIN `integration` ON `app`.`integration_id` = `integration`.`id`
        ');

        return $this->webhooks = FetchModeHelper::group($result);
    }

    /**
     * @param BusinessEventInterface|Hookable $event
     */
    private function getPayloadFor($event): array
    {
        if ($event instanceof BusinessEventInterface) {
            return $this->eventEncoder->encode($event);
        }

        return $event->getWebhookPayload();
    }
}

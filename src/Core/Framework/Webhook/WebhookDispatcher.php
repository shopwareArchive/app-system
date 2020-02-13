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

    public function __construct(
        EventDispatcherInterface $dispatcher,
        Connection $connection,
        Client $guzzle,
        BusinessEventEncoder $eventEncoder
    ) {
        $this->dispatcher = $dispatcher;
        $this->connection = $connection;
        $this->guzzle = $guzzle;
        $this->eventEncoder = $eventEncoder;
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

        $payload = $this->getJsonPayloadFor($event);
        $requests = [];
        foreach ($this->getWebhooks()[$eventName] as $url) {
            $requests[] = new Request('POST', $url['url'], [], $payload);
        }

        $pool = new Pool($this->guzzle, $requests);
        $pool->promise()->wait();
    }

    private function getWebhooks(): array
    {
        if ($this->webhooks) {
            return $this->webhooks;
        }

        $result = $this->connection->fetchAll('SELECT `event_name`, `url` FROM `webhook`');

        return $this->webhooks = FetchModeHelper::group($result);
    }

    /**
     * @param BusinessEventInterface|Hookable $event
     */
    private function getJsonPayloadFor($event): string
    {
        $payload = '';

        if ($event instanceof BusinessEventInterface) {
            /** @var string $payload */
            $payload = json_encode($this->eventEncoder->encode($event));
        }
        if ($event instanceof Hookable) {
            /** @var string $payload */
            $payload = json_encode($event->getWebhookPayload());
        }

        return $payload;
    }
}

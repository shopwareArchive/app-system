<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Framework\Webhook;

use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Customer\Event\CustomerBeforeLoginEvent;
use Shopware\Core\Content\MailTemplate\Subscriber\MailSendSubscriber;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Event\BusinessEvent;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Swag\SaasConnect\Core\Framework\Webhook\BusinessEventEncoder;
use Swag\SaasConnect\Core\Framework\Webhook\WebhookDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WebhookDispatcherTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var MockHandler
     */
    private $appServerMock;

    /**
     * @var EntityRepositoryInterface
     */
    private $webhookRepository;

    public function setUp(): void
    {
        $this->appServerMock = $this->getContainer()->get(MockHandler::class);
        $this->webhookRepository = $this->getContainer()->get('webhook.repository');
    }

    public function testDispatchesBusinessEventToWebhook(): void
    {
        $this->webhookRepository->upsert([
            [
                'eventName' => CustomerBeforeLoginEvent::EVENT_NAME,
                'url' => 'https://test.com',
            ],
        ], Context::createDefaultContext());

        $this->appServerMock->append(new Response(200));

        $event = new CustomerBeforeLoginEvent(
            $this->getContainer()->get(SalesChannelContextFactory::class)->create(Uuid::randomHex(), Defaults::SALES_CHANNEL),
            'test@example.com'
        );

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $eventDispatcher->dispatch($event);

        /** @var Request $request */
        $request = $this->appServerMock->getLastRequest();

        static::assertEquals('POST', $request->getMethod());
        $body = $request->getBody()->getContents();
        static::assertJson($body);
        static::assertEquals([
            'email' => 'test@example.com',
        ], json_decode($body, true));
    }

    public function testDispatchesWrappedEntityWrittenEventToWebhook(): void
    {
        $this->webhookRepository->upsert([
            [
                'eventName' => ProductEvents::PRODUCT_WRITTEN_EVENT,
                'url' => 'https://test.com',
            ],
        ], Context::createDefaultContext());

        $this->appServerMock->append(new Response(200));

        $id = Uuid::randomHex();

        /** @var EntityRepositoryInterface $productRepository */
        $productRepository = $this->getContainer()->get('product.repository');
        $productRepository->upsert([
            [
                'id' => $id,
                'name' => 'testProduct',
                'productNumber' => 'SWC-1000',
                'stock' => 100,
                'manufacturer' => [
                    'name' => 'app creator',
                ],
                'price' => [
                    [
                        'gross' => 100,
                        'net' => 200,
                        'linked' => false,
                        'currencyId' => Defaults::CURRENCY,
                    ],
                ],
                'tax' => [
                    'name' => 'luxury',
                    'taxRate' => '25',
                ],
            ],
        ], Context::createDefaultContext());

        /** @var Request $request */
        $request = $this->appServerMock->getLastRequest();

        static::assertEquals('POST', $request->getMethod());
        $body = $request->getBody()->getContents();
        static::assertJson($body);
        static::assertEquals([[
            'entity' => 'product',
            'operation' => 'insert',
            'primaryKey' => $id,
            'updatedFields' => [
                'versionId',
                'id',
                'parentVersionId',
                'manufacturerId',
                'productManufacturerVersionId',
                'taxId',
                'stock',
                'price',
                'productNumber',
                'isCloseout',
                'purchaseSteps',
                'minPurchase',
                'shippingFree',
                'restockTime',
                'createdAt',
            ],
        ]], json_decode($body, true));
    }

    public function testNoRegisteredWebhook(): void
    {
        $event = new CustomerBeforeLoginEvent(
            $this->getContainer()->get(SalesChannelContextFactory::class)->create(Uuid::randomHex(), Defaults::SALES_CHANNEL),
            'test@example.com'
        );

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects(static::never())
            ->method('sendAsync');

        $webhookDispatcher = new WebhookDispatcher(
            $this->getContainer()->get('event_dispatcher'),
            $this->getContainer()->get(Connection::class),
            $clientMock,
            $this->getContainer()->get(BusinessEventEncoder::class)
        );

        $webhookDispatcher->dispatch($event);
    }

    public function testDoesntDispatchesWrappedBusinessEventToWebhook(): void
    {
        $this->webhookRepository->upsert([
            [
                'eventName' => CustomerBeforeLoginEvent::EVENT_NAME,
                'url' => 'https://test.com',
            ],
        ], Context::createDefaultContext());

        $event = new BusinessEvent(
            MailSendSubscriber::ACTION_NAME,
            new CustomerBeforeLoginEvent(
                $this->getContainer()->get(SalesChannelContextFactory::class)->create(Uuid::randomHex(), Defaults::SALES_CHANNEL),
                'test@example.com'
            )
        );

        $clientMock = $this->createMock(Client::class);
        $clientMock->expects(static::never())
            ->method('sendAsync');

        $webhookDispatcher = new WebhookDispatcher(
            $this->getContainer()->get('event_dispatcher'),
            $this->getContainer()->get(Connection::class),
            $clientMock,
            $this->getContainer()->get(BusinessEventEncoder::class)
        );

        $webhookDispatcher->dispatch($event);
    }

    public function testAddSubscriber(): void
    {
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $eventDispatcherMock->expects(static::once())
            ->method('addSubscriber');

        $webhookDispatcher = new WebhookDispatcher(
            $eventDispatcherMock,
            $this->getContainer()->get(Connection::class),
            $this->getContainer()->get(Client::class),
            $this->getContainer()->get(BusinessEventEncoder::class)
        );

        $webhookDispatcher->addSubscriber(new MockSubscriber());
    }

    public function testRemoveSubscriber(): void
    {
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $eventDispatcherMock->expects(static::once())
            ->method('removeSubscriber');

        $webhookDispatcher = new WebhookDispatcher(
            $eventDispatcherMock,
            $this->getContainer()->get(Connection::class),
            $this->getContainer()->get(Client::class),
            $this->getContainer()->get(BusinessEventEncoder::class)
        );

        $webhookDispatcher->removeSubscriber(new MockSubscriber());
    }
}

class MockSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [];
    }
}

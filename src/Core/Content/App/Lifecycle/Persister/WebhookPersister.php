<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Persister;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\Webhook;

class WebhookPersister
{
    /**
     * @var EntityRepositoryInterface
     */
    private $webhookRepository;

    public function __construct(EntityRepositoryInterface $webhookRepository)
    {
        $this->webhookRepository = $webhookRepository;
    }

    public function updateWebhooks(Manifest $manifest, string $appId, Context $context): void
    {
        $this->deleteExistingWebhooks($appId, $context);

        $webhooks = $manifest->getWebhooks() ? $manifest->getWebhooks()->getWebhooks() : [];
        $this->addWebhooks($webhooks, $appId, $context);
    }

    private function deleteExistingWebhooks(string $appId, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('appId', $appId));

        /** @var array<string> $ids */
        $ids = $this->webhookRepository->searchIds($criteria, $context)->getIds();

        if (!empty($ids)) {
            $ids = array_map(static function (string $id): array {
                return ['id' => $id];
            }, $ids);

            $this->webhookRepository->delete($ids, $context);
        }
    }

    /**
     * @param array<Webhook> $webhooks
     */
    private function addWebhooks(array $webhooks, string $appId, Context $context): void
    {
        if (empty($webhooks)) {
            return;
        }

        $webhooks = array_map(static function (Webhook $webhook) use ($appId): array {
            $webhook = $webhook->toArray();
            $webhook['appId'] = $appId;
            $webhook['eventName'] = $webhook['event'];

            return $webhook;
        }, $webhooks);

        $this->webhookRepository->create($webhooks, $context);
    }
}

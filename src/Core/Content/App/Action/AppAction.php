<?php


namespace Swag\SaasConnect\Core\Content\App\Action;

use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SaasConnect\Core\Content\App\Exception\InvalidArgumentException;

class AppAction
{
    const VERSION_VALIDATE_REGEX = '/^[0-9]+\.[0-9]+\.[0-9]+$/';
    /**
     * @var array
     */
    private $ids;

    /**
     * @var string
     */
    private $targetUrl;

    /**
     * @var string
     */
    private $appVersion;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var string
     */
    private $action;

    /**
     * @var string
     */
    private $shopUrl;

    /**
     * @param array<string> $ids
     */
    public function __construct(string $targetUrl, string $shopUrl, string $appVersion, string $entity, string $action, array $ids)
    {
        $this->setAction($action);
        $this->setAppVersion($appVersion);
        $this->setEntity($entity);
        $this->setIds($ids);
        $this->setShopUrl($shopUrl);
        $this->setTargetUrl($targetUrl);
    }

    /**
     * @param array<string> $ids
     */
    private function setIds(array $ids): void
    {
        foreach ($ids as $id) {
            if (!Uuid::isValid($id)) {
                throw new InvalidArgumentException(sprintf('%s is not a valid uuid', $id));
            }
        }
        $this->ids = $ids;
    }

    private function setTargetUrl(string $targetUrl): void
    {
        if (!filter_var($targetUrl, FILTER_VALIDATE_URL, [FILTER_FLAG_SCHEME_REQUIRED, FILTER_FLAG_HOST_REQUIRED])) {
            throw new InvalidArgumentException(sprintf('%s is not a valid url', $targetUrl));
        }
        $this->targetUrl = $targetUrl;
    }

    private function setAppVersion(string $appVersion): void
    {
        if (!preg_match(self::VERSION_VALIDATE_REGEX, $appVersion)) {
            throw new InvalidArgumentException(sprintf('%s is not a valid version', $appVersion));
        }
        $this->appVersion = $appVersion;
    }

    private function setEntity(string $entity): void
    {
        if ($entity === '') {
            throw new InvalidArgumentException('entity name cannot be empty');
        }
        $this->entity = $entity;
    }

    private function setAction(string $action): void
    {
        if ($action === '') {
            throw new InvalidArgumentException('action name cannot be empty');
        }
        $this->action = $action;
    }

    private function setShopUrl(string $shopUrl): void
    {
        if (!filter_var($shopUrl, FILTER_VALIDATE_URL, [FILTER_FLAG_SCHEME_REQUIRED, FILTER_FLAG_HOST_REQUIRED])) {
            throw new InvalidArgumentException(sprintf('%s is not a valid url', $shopUrl));
        }
        $this->shopUrl = $shopUrl;
    }

    public function getTargetUrl(): string
    {
        return $this->targetUrl;
    }

    public function asPayload(): array
    {
        return [
            'source' => [
                'url' => $this->shopUrl,
                'appVersion' => $this->appVersion,
                /** @Todo add integration key: SAAS-221 */
                'apiKey' => '',
            ],
            'data' => [
                'ids' => $this->ids,
                'entity' => $this->entity,
                'action' => $this->action,
            ]
        ];
    }
}

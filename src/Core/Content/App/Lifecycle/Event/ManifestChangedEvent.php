<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Event;

use Shopware\Core\Framework\Context;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Core\Framework\Api\Acl\AclPrivilegeCollection;

abstract class ManifestChangedEvent extends AppChangedEvent
{
    /**
     * @var Manifest
     */
    private $app;

    public function __construct(string $appId, Manifest $app, Context $context)
    {
        $this->app = $app;
        parent::__construct($appId, $context);
    }

    abstract public function getName(): string;

    public function getApp(): Manifest
    {
        return $this->app;
    }

    /**
     * @return array<string, string>
     */
    public function getWebhookPayload(): array
    {
        return [
            'appVersion' => $this->getApp()->getMetadata()->getVersion(),
        ];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function isAllowed(string $appId, AclPrivilegeCollection $permissions): bool
    {
        return $appId === $this->getAppId();
    }
}

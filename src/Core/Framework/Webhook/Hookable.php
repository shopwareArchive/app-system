<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\Webhook;

use Shopware\Core\Framework\Api\Acl\Permission\AclPermissionCollection;

interface Hookable
{
    public function getName(): string;

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint
     */
    public function getWebhookPayload(): array;

    /**
     * returns if it is allowed to dispatch the event to given app with given permissions
     */
    public function isAllowed(string $appId, AclPermissionCollection $permissions): bool;
}

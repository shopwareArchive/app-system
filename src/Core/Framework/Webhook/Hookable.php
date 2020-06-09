<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\Webhook;

interface Hookable
{
    public function getName(): string;

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint
     */
    public function getWebhookPayload(): array;
}

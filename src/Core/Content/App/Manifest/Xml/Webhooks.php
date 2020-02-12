<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest\Xml;

class Webhooks extends XmlElement
{
    /**
     * @var array<Webhook>
     */
    protected $webhooks = [];

    /**
     * @param array<Webhook> $webhooks
     */
    private function __construct(array $webhooks)
    {
        $this->webhooks = $webhooks;
    }

    public static function fromXml(\DOMElement $element): self
    {
        return new self(self::parseWebhooks($element));
    }

    /**
     * @return array<Webhook>
     */
    public function getWebhooks(): array
    {
        return $this->webhooks;
    }

    /**
     * @return array<Webhook>
     */
    private static function parseWebhooks(\DOMElement $element): array
    {
        $webhooks = [];
        /** @var \DOMElement $webhook */
        foreach ($element->getElementsByTagName('webhook') as $webhook) {
            $webhooks[] = Webhook::fromXml($webhook);
        }

        return $webhooks;
    }
}

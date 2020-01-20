<?php

namespace Swag\SaasConnect\Core\Content\App\Action;

use GuzzleHttp\Client;
use Shopware\Core\Framework\Uuid\Uuid;

class Executor
{
    /**
     * @var Client
     */
    private $guzzleClient;

    public function __construct(Client $guzzle)
    {
        $this->guzzleClient = $guzzle;
    }

    public function execute(AppAction $action): void
    {
        $payload = $action->asPayload();
        $payload['meta'] = [
            'timestamp' => (new \DateTime())->getTimestamp(),
            'reference' => Uuid::randomHex()
        ];

        $this->guzzleClient->postAsync($action->getTargetUrl(), ['json' => $payload]);
    }
}

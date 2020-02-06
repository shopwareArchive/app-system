<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Action;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
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
            'reference' => Uuid::randomHex(),
        ];

        try {
            $this->guzzleClient->post($action->getTargetUrl(), ['json' => $payload]);
        } catch (ServerException $e) {
            // ignore failing requests
        }
    }
}

<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Registration;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

class PrivateHandshake implements AppHandshakeInterface
{
    /**
     * @var string
     */
    private $shopUrl;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var string
     */
    private $appEndpoint;

    /**
     * @var string
     */
    private $appName;

    /**
     * @var string
     */
    private $shopId;

    public function __construct(string $shopUrl, string $secret, string $appEndpoint, string $appName, string $shopId)
    {
        $this->shopUrl = $shopUrl;
        $this->secret = $secret;
        $this->appEndpoint = $appEndpoint;
        $this->appName = $appName;
        $this->shopId = $shopId;
    }

    public function assembleRequest(): RequestInterface
    {
        $date = new \DateTime();
        $queryString = sprintf(
            'shop-id=%s&shop-url=%s&timestamp=%s',
            $this->shopId,
            urlencode($this->shopUrl),
            $date->getTimestamp()
        );
        $signature = hash_hmac('sha256', $queryString, $this->secret);

        return new Request(
            'GET',
            $this->appEndpoint . '?' . $queryString,
            [
                'shopware-app-signature' => $signature,
            ]
        );
    }

    public function fetchAppProof(): string
    {
        return hash_hmac('sha256', $this->shopId . $this->shopUrl . $this->appName, $this->secret);
    }
}

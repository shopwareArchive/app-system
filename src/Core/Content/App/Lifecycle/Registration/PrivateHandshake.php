<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Registration;

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

    public function __construct(string $shopUrl, string $secret, string $appEndpoint, string $appName)
    {
        $this->shopUrl = $shopUrl;
        $this->secret = $secret;
        $this->appEndpoint = $appEndpoint;
        $this->appName = $appName;
    }

    public function fetchUrl(): string
    {
        $date = new \DateTime();
        $queryString = 'shop=' . urlencode($this->shopUrl) . '&timestamp=' . $date->getTimestamp();
        $signature = hash_hmac('sha256', $queryString, $this->secret);

        return $this->appEndpoint . '?' . $queryString . '&hmac=' . $signature;
    }

    public function fetchAppProof(): string
    {
        return hash_hmac('sha256', $this->shopUrl . $this->appName, $this->secret);
    }
}

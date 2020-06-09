<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle\Registration;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Swag\SaasConnect\Core\Content\App\AppEntity;
use Swag\SaasConnect\Core\Content\App\Exception\AppRegistrationException;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;

class AppRegistrationService
{
    /**
     * @var HandshakeFactory
     */
    private $handshakeFactory;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    /**
     * @var string
     */
    private $shopUrl;

    public function __construct(
        HandshakeFactory $handshakeFactory,
        Client $httpClient,
        EntityRepositoryInterface $appRepository,
        string $shopUrl
    ) {
        $this->handshakeFactory = $handshakeFactory;
        $this->httpClient = $httpClient;
        $this->appRepository = $appRepository;
        $this->shopUrl = $shopUrl;
    }

    public function registerApp(Manifest $manifest, string $id, Context $context): void
    {
        if (!$manifest->getSetup()) {
            return;
        }

        $appResponse = $this->registerWithApp($manifest);

        $secret = $appResponse['secret'];
        $confirmationUrl = $appResponse['confirmation_url'];

        $this->saveAppSecret($id, $context, $secret);

        $this->confirmRegistration($id, $context, $secret, $confirmationUrl);
    }

    /**
     * @return array<string,string>
     */
    private function registerWithApp(Manifest $manifest): array
    {
        $handshake = $this->handshakeFactory->create($manifest);

        $request = $handshake->assembleRequest();
        $response = $this->httpClient->send($request);

        return $this->parseResponse($handshake, $response);
    }

    private function saveAppSecret(string $id, Context $context, string $secret): void
    {
        $update = ['id' => $id, 'appSecret' => $secret];

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($update): void {
            $this->appRepository->update([$update], $context);
        });
    }

    private function confirmRegistration(string $id, Context $context, string $secret, string $confirmationUrl): void
    {
        $payload = $this->getConfirmationPayload($id, $context);

        $signature = $this->signPayload($payload, $secret);

        $this->httpClient->post($confirmationUrl, [
            'headers' => [
                'shopware-shop-signature' => $signature,
            ],
            'json' => $payload,
        ]);
    }

    /**
     * @return array<string,string>
     */
    private function parseResponse(AppHandshakeInterface $handshake, ResponseInterface $response): array
    {
        $data = \json_decode($response->getBody()->getContents(), true);

        $proof = $data['proof'] ?? '';
        if (!hash_equals($handshake->fetchAppProof(), trim($proof))) {
            throw new AppRegistrationException('The app provided a invalid response');
        }

        return $data;
    }

    /**
     * @return array<string,string>
     */
    private function getConfirmationPayload(string $id, Context $context): array
    {
        $app = $this->getApp($id, $context);

        return [
            'apiKey' => $app->getIntegration()->getAccessKey(),
            'secretKey' => $app->getAccessToken(),
            'timestamp' => (string) (new \DateTime())->getTimestamp(),
            'shopUrl' => $this->shopUrl,
        ];
    }

    /**
     * @param array<string,string> $body
     */
    private function signPayload(array $body, string $secret): string
    {
        return hash_hmac('sha256', (string) \json_encode($body), $secret);
    }

    private function getApp(string $id, Context $context): AppEntity
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('integration');

        /** @var AppEntity $app */
        $app = $this->appRepository->search($criteria, $context)->first();

        return $app;
    }
}

<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Lifecycle\Registration;

use Doctrine\DBAL\Connection;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\SaasConnect\Core\Content\App\AppEntity;
use Swag\SaasConnect\Core\Content\App\Exception\AppRegistrationException;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Registration\AppRegistrationService;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Test\GuzzleTestClientBehaviour;
use Swag\SaasConnect\Test\TestAppServer;

class AppRegistrationServiceTest extends TestCase
{
    use GuzzleTestClientBehaviour;

    /**
     * @var AppRegistrationService
     */
    private $registrator;

    /**
     * @var string
     */
    private $shopUrl;

    /**
     * @var EntityRepositoryInterface|null
     */
    private $appRepository;

    public function setup(): void
    {
        $this->appRepository = $this->getContainer()->get('saas_app.repository');
        $this->registrator = $this->getContainer()->get(AppRegistrationService::class);
        $this->shopUrl = (string) getenv('APP_URL');
    }

    public function testRegisterPrivateApp(): void
    {
        $id = Uuid::randomHex();
        $this->createApp($id);

        $manifest = Manifest::createFromXmlFile(__DIR__ . '/_fixtures/minimal/manifest.xml');

        $appSecret = 'dont_tell';
        $appResponseBody = $this->buildAppResponse($manifest, $appSecret);

        $this->appendNewResponse(new Response(200, [], $appResponseBody));
        $this->appendNewResponse(new Response(200, []));

        $this->registrator->registerApp($manifest, $id, Context::createDefaultContext());

        $registrationRequest = $this->getPastRequest(0);

        $uriWithoutQuery = $registrationRequest->getUri()->withQuery('');
        static::assertEquals($manifest->getSetup()->getRegistrationUrl(), (string) $uriWithoutQuery);

        $this->assertRequestIsSigned($registrationRequest, $manifest->getSetup()->getSecret());

        $app = $this->fetchApp($id);

        static::assertEquals(TestAppServer::APP_SECRET, $app->getAppSecret());

        static::assertEquals(2, $this->getRequestCount());

        $confirmationReq = $this->getPastRequest(1);
        static::assertEquals('POST', $confirmationReq->getMethod());

        $postBody = \json_decode($confirmationReq->getBody()->getContents(), true);
        static::assertEquals($app->getAccessToken(), $postBody['secretKey']);
        static::assertEquals($app->getIntegration()->getAccessKey(), $postBody['apiKey']);
        static::assertEquals(getenv('APP_URL'), $postBody['shopUrl']);

        static::assertEquals(
            hash_hmac('sha256', json_encode($postBody), $appSecret),
            $confirmationReq->getHeaderLine('shopware-shop-signature')
        );
    }

    public function testRegistrationFailsWithWrongProof(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/_fixtures/minimal/manifest.xml');

        $this->appendNewResponse(new Response(200, [], '{"proof": "wrong proof"}'));

        static::expectException(AppRegistrationException::class);
        $this->registrator->registerApp($manifest, '', Context::createDefaultContext());
    }

    public function testRegistrationFailsWithoutProof(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/_fixtures/minimal/manifest.xml');

        $this->appendNewResponse(new Response(200, [], '{}'));

        static::expectException(AppRegistrationException::class);
        $this->registrator->registerApp($manifest, '', Context::createDefaultContext());
    }

    // currently not implemented
    public function testRegisterStoreApp(): void
    {
        $manifest = Manifest::createFromXmlFile(__DIR__ . '/_fixtures/minimal/manifest.xml');

        static::expectException(\RuntimeException::class);
        $this->registrator->registerApp($manifest, '', Context::createDefaultContext());
    }

    private function createApp(string $id): void
    {
        $roleId = Uuid::randomHex();

        $this->appRepository->create([[
            'id' => $id,
            'name' => 'SwagApp',
            'path' => __DIR__ . '/../Manifest/_fixtures/test',
            'version' => '0.0.1',
            'label' => 'test',
            'accessToken' => 'testtoken',
            'integration' => [
                'label' => 'test',
                'writeAccess' => false,
                'accessKey' => 'testkey',
                'secretAccessKey' => 'test',
            ],
            'customFieldSets' => [
                [
                    'name' => 'test',
                ],
            ],
            'aclRole' => [
                'id' => $roleId,
                'name' => 'SwagApp',
            ],
        ]], Context::createDefaultContext());

        /** @var Connection $connection */
        $connection = $this->getContainer()->get(Connection::class);
        $connection->executeUpdate('
            INSERT INTO `acl_resource` (`resource`, `privilege`, `acl_role_id`, `created_at`)
            VALUES ("test", "list", UNHEX(:roleId), NOW()), ("product", "detail", UNHEX(:roleId), NOW())
        ', ['roleId' => $roleId]);
    }

    private function buildAppResponse(Manifest $manifest, string $appSecret)
    {
        $proof = \hash_hmac('sha256', $this->shopUrl . $manifest->getMetadata()->getName(), $manifest->getSetup()->getSecret());

        $confirmationUrl = 'https://my-app.com/confirm';
        $appResponseBody = \json_encode(['proof' => $proof, 'secret' => $appSecret, 'confirmation_url' => $confirmationUrl]);

        return $appResponseBody;
    }

    private function assertRequestIsSigned(RequestInterface $registrationRequest, string $secret): void
    {
        static::assertEquals(
            hash_hmac('sha256', $registrationRequest->getUri()->getQuery(), $secret),
            $registrationRequest->getHeaderLine('shopware-app-signature')
        );
    }

    private function fetchApp(string $id): AppEntity
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('integration');
        /** @var AppEntity $app */
        $app = $this->appRepository->search($criteria, Context::createDefaultContext())->first();

        return $app;
    }
}

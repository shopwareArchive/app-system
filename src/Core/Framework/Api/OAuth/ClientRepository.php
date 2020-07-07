<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\Api\OAuth;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Shopware\Core\Framework\Api\Util\AccessKeyHelper;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Swag\SaasConnect\Core\Content\App\AppEntity;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * @var ClientRepositoryInterface
     */
    private $inner;

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepo;

    public function __construct(ClientRepositoryInterface $inner, EntityRepositoryInterface $integrationRepo)
    {
        $this->inner = $inner;
        $this->appRepo = $integrationRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientEntity(
        $clientIdentifier,
        $grantType = null,
        $clientSecret = null,
        $mustValidateSecret = true
    ) {
        $client = $this->inner->getClientEntity($clientIdentifier, $grantType, $clientSecret, $mustValidateSecret);
        if (!$this->isIntegration($client)) {
            return $client;
        }

        $app = $this->fetchApp($client);

        if (!$app) {
            // integration without an app
            return $client;
        }

        if (!$app->isActive()) {
            throw OAuthServerException::accessDenied();
        }

        return $client;
    }

    private function isIntegration(ClientEntityInterface $client): bool
    {
        try {
            $origin = AccessKeyHelper::getOrigin($client->getIdentifier());
        } catch (\RuntimeException $e) {
            $origin = null;
        }

        return $origin === 'integration';
    }

    private function fetchApp(ClientEntityInterface $client): ?AppEntity
    {
        $id = $client->getIdentifier();

        $criteria = new Criteria();
        $criteria->addAssociation('integration');
        $criteria->addFilter(new EqualsFilter('integration.accessKey', $id));

        /** @var AppEntity | null $app */
        $app = $this->appRepo->search($criteria, Context::createDefaultContext())->first();

        return $app;
    }
}

<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\Api;

use Shopware\Core\Framework\ShopwareHttpException;
use Swag\SaasConnect\Core\Framework\AppUrlChangeResolver\AppUrlChangeResolverNotFoundException;
use Symfony\Component\HttpFoundation\Response;

class AppUrlChangeResolverNotFoundHttpException extends ShopwareHttpException
{
    public function __construct(AppUrlChangeResolverNotFoundException $previous)
    {
        parent::__construct($previous->getMessage(), [], $previous);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorCode(): string
    {
        return 'SAAS_CONNECT__APP_URL_CHANGE_RESOLVER_NOT_FOUND';
    }
}

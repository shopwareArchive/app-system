<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Exception;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class ActionNotFoundException extends ShopwareHttpException implements SaasConnectException
{
    public function __construct()
    {
        parent::__construct('The requested action does not exist');
    }

    public function getErrorCode(): string
    {
        return 'SAASCONNECT_ACTION_NOT_FOUND';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}

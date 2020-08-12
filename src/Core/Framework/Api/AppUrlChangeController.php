<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\Api;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Swag\SaasConnect\Core\Framework\AppUrlChangeResolver\AppUrlChangeResolverNotFoundException;
use Swag\SaasConnect\Core\Framework\AppUrlChangeResolver\AppUrlChangeResolverStrategy;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class AppUrlChangeController extends AbstractController
{
    /**
     * @var AppUrlChangeResolverStrategy
     */
    private $appUrlChangeResolverStrategy;

    public function __construct(AppUrlChangeResolverStrategy $appUrlChangeResolverStrategy)
    {
        $this->appUrlChangeResolverStrategy = $appUrlChangeResolverStrategy;
    }

    /**
     * @Route("api/v{version}/app-system/app-url-change/strategies", name="api.app_system.app-url-change-strategies", methods={"GET"})
     */
    public function getAvailableStrategies(): JsonResponse
    {
        return new JsonResponse(
            $this->appUrlChangeResolverStrategy->getAvailableStrategies()
        );
    }

    /**
     * @Route("api/v{version}/app-system/app-url-change/resolve", name="api.app_system.app-url-change-resolve", methods={"POST"})
     */
    public function resolve(Request $request, Context $context): Response
    {
        $strategy = $request->get('strategy');

        if (!$strategy) {
            throw new MissingRequestParameterException('strategy');
        }

        try {
            $this->appUrlChangeResolverStrategy->resolve($strategy, $context);
        } catch (AppUrlChangeResolverNotFoundException $e) {
            throw new AppUrlChangeResolverNotFoundHttpException($e);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

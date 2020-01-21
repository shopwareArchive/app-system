<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Api;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Swag\SaasConnect\Core\Content\App\Action\ActionButtonLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class AppActionController extends AbstractController
{
    /**
     * @var ActionButtonLoader
     */
    private $actionButtonLoader;

    public function __construct(ActionButtonLoader $actionButtonLoader)
    {
        $this->actionButtonLoader = $actionButtonLoader;
    }

    /**
     * @Route("api/v{version}/app-system/action-button/{entity}/{view}", name="api.app_system.action_buttons", methods={"GET"})
     */
    public function getActionsPerView(string $entity, string $view, Context $context): Response
    {
        return new JsonResponse([
            'actions' => $this->actionButtonLoader->loadActionButtonsForView($entity, $view, $context),
        ]);
    }

    /**
     * @Route("api/v{version}/app-system/action-button/run/{id}", name="api.app_system.action_button.run", methods={"POST"})
     */
    public function runAction(): Response
    {
        return new JsonResponse();
    }
}

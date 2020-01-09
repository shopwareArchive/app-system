<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Api;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
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
     * @Route("api/v{version}/app-system/action-button/{entity}/{view}", name="api.app_system.action_buttons", methods={"GET"})
     */
    public function getActionsPerView(): Response
    {
        return new JsonResponse([
            [
                'app' => 'MyAction',
                'id' => 'fffffffffffff',
                'action' => 'test',
                'label' => [
                    'en-GB' => 'My Action',
                    'de-DE' => 'Meine Aktion',
                ],
                'url' => 'http://test.com/post',
                'openInNewTab' => true
            ],
            [
                'app' => 'MyAction',
                'id' => '000000000000',
                'action' => 'test 2',
                'label' => [
                    'en-GB' => 'My second Action',
                    'de-DE' => 'Meine zweite Aktion',
                ],
                'url' => 'http://test.com/post2',
                'openInNewTab' => false,
            ]
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

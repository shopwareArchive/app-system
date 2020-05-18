<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class TestAppServer
{
    const TEST_SETUP_SECRET = 's3cr3t';
    const CONFIRMATION_URL = 'https://my-app.com/confirm';
    const APP_SECRET = 'dont_tell';

    /**
     * @var MockHandler
     */
    private $inner;

    public function __construct(MockHandler $inner)
    {
        $this->inner = $inner;
    }

    public function __invoke(RequestInterface $request, array $options): Promise
    {
        if ($this->isRegistration($request)) {
            $promise = new Promise();
            $promise->resolve(new Response(200, [], $this->buildAppResponse($this->getAppname($request))));

            return $promise;
        }

        if ($this->isRegistrationConfirmation($request)) {
            $promise = new Promise();
            $promise->resolve(new Response(200));

            return $promise;
        }

        return \call_user_func($this->inner, $request, $options);
    }

    private function buildAppResponse(string $appName)
    {
        $shopUrl = (string) getenv('APP_URL');
        $proof = \hash_hmac('sha256', $shopUrl . $appName, self::TEST_SETUP_SECRET);

        return \json_encode(['proof' => $proof, 'secret' => self::APP_SECRET, 'confirmation_url' => self::CONFIRMATION_URL]);
    }

    private function isRegistration(RequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        $pathElems = explode('/', $path);

        return ($pathElems[2] ?? '') === 'registration';
    }

    private function isRegistrationConfirmation(RequestInterface $request): bool
    {
        return ((string) $request->getUri()) === self::CONFIRMATION_URL;
    }

    private function getAppname(RequestInterface $request): string
    {
        $path = $request->getUri()->getPath();
        $pathElems = explode('/', $path);

        return $pathElems[1] ?? '';
    }
}

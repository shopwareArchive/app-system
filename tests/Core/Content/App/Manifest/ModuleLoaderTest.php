<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Action;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Swag\SaasConnect\Core\Content\App\Manifest\ModuleLoader;

class ModuleLoaderTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var ModuleLoader
     */
    private $moduleLoader;

    public function setUp(): void
    {
        $this->appRepository = $this->getContainer()->get('app.repository');
        $this->moduleLoader = $this->getContainer()->get(ModuleLoader::class);
        $this->context = Context::createDefaultContext();
    }

    public function testLoadActionButtonsForView(): void
    {
        $this->registerModules();

        $loadedModules = $this->moduleLoader->loadModules($this->context);

        usort($loadedModules, function ($a, $b) {
            return $a['name'] <=> $b['name'];
        });

        static::assertEquals([
            [
                'name' => 'App1',
                'label' => [
                    'en-GB' => 'test App1',
                ],
                'modules' => [
                    [
                        'label' => [
                            'en-GB' => 'first App',
                            'de-DE' => 'Erste App',
                        ],
                        'source' => 'https://first.app.com',
                        'name' => 'first-module',
                    ],
                    [
                        'label' => [
                            'en-GB' => 'first App second Module',
                        ],
                        'source' => 'https://first.app.com/second',
                        'name' => 'second-module',
                    ],
                ],
            ],
            [
                'name' => 'App2',
                'label' => [
                    'en-GB' => 'test App2',
                ],
                'modules' => [
                    [
                        'label' => [
                            'en-GB' => 'second App',
                        ],
                        'source' => 'https://second.app.com',
                        'name' => 'second-app',
                    ],
                ],
            ],
        ], $loadedModules);
    }

    private function registerModules(): void
    {
        $this->appRepository->create([[
            'name' => 'App1',
            'path' => __DIR__ . '/Manifest/_fixtures/test',
            'version' => '0.0.1',
            'label' => 'test App1',
            'accessToken' => 'test',
            'modules' => [
                [
                    'label' => [
                        'en-GB' => 'first App',
                        'de-DE' => 'Erste App',
                    ],
                    'source' => 'https://first.app.com',
                    'name' => 'first-module',
                ],
                [
                    'label' => [
                        'en-GB' => 'first App second Module',
                    ],
                    'source' => 'https://first.app.com/second',
                    'name' => 'second-module',
                ],
            ],
            'integration' => [
                'label' => 'App1',
                'writeAccess' => false,
                'accessKey' => 'test',
                'secretAccessKey' => 'test',
            ],
            'aclRole' => [
                'name' => 'App1',
            ],
        ], [
            'name' => 'App2',
            'path' => __DIR__ . '/Manifest/_fixtures/test',
            'version' => '0.0.1',
            'label' => 'test App2',
            'accessToken' => 'test',
            'modules' => [
                [
                    'label' => [
                        'en-GB' => 'second App',
                    ],
                    'source' => 'https://second.app.com',
                    'name' => 'second-app',
                ],
            ],
            'integration' => [
                'label' => 'App2',
                'writeAccess' => false,
                'accessKey' => 'test',
                'secretAccessKey' => 'test',
            ],
            'aclRole' => [
                'name' => 'App2',
            ],
        ]], $this->context);
    }
}

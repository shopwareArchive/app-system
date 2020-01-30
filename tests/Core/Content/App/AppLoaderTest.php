<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Lifecycle\AppLoader;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;

class AppLoaderTest extends TestCase
{
    public function testLoad(): void
    {
        $appLoader = new AppLoader(__DIR__ . '/Manifest/_fixtures/test');

        $manifests = $appLoader->load();

        static::assertCount(1, $manifests);
        static::assertInstanceOf(Manifest::class, $manifests[0]);
    }

    public function testLoadIgnoresInvalid(): void
    {
        $appLoader = new AppLoader(__DIR__ . '/Manifest/_fixtures/invalid');

        $manifests = $appLoader->load();

        static::assertCount(0, $manifests);
    }

    public function testLoadCombinesFolders(): void
    {
        $appLoader = new AppLoader(__DIR__ . '/Manifest/_fixtures');

        $manifests = $appLoader->load();

        static::assertCount(2, $manifests);
        foreach ($manifests as $manifest) {
            static::assertInstanceOf(Manifest::class, $manifest);
        }
    }
}

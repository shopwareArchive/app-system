<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Command;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Symfony\Component\Console\Tester\CommandTester;

class VerifyManifestCommandTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testVerifyValidManifest(): void
    {
        $commandTester = new CommandTester($this->getContainer()->get(VerifyManifestCommand::class));
        $commandTester->execute(['manifests' => [__DIR__ . '/../Content/App/Manifest/_fixtures/test/manifest.xml']]);

        static::assertEquals(0, $commandTester->getStatusCode());
        static::assertStringContainsString('[OK]', $commandTester->getDisplay());
    }

    public function testVerifyInValidManifestFails(): void
    {
        $commandTester = new CommandTester($this->getContainer()->get(VerifyManifestCommand::class));
        $commandTester->execute(['manifests' => [__DIR__ . '/../Content/App/Manifest/_fixtures/invalid/manifest.xml']]);

        static::assertEquals(1, $commandTester->getStatusCode());
        static::assertStringContainsString('[ERROR]', $commandTester->getDisplay());
    }

    public function testVerifyServeralManifestsShowsOnlyErrors(): void
    {
        $commandTester = new CommandTester($this->getContainer()->get(VerifyManifestCommand::class));
        $files = [
            __DIR__ . '/../Content/App/Manifest/_fixtures/test/manifest.xml',
            __DIR__ . '/../Content/App/Manifest/_fixtures/invalid/manifest.xml',
            __DIR__ . '/../Content/App/Manifest/_fixtures/minimal/manifest.xml',
        ];
        $commandTester->execute(['manifests' => $files]);

        static::assertEquals(1, $commandTester->getStatusCode());
        static::assertStringContainsString('[ERROR]', $commandTester->getDisplay());
    }

    public function testVerifyServeralManifestsShowsIsSuccessfulWhenAllFilesAreValid(): void
    {
        $commandTester = new CommandTester($this->getContainer()->get(VerifyManifestCommand::class));
        $files = [
            __DIR__ . '/../Content/App/Manifest/_fixtures/test/manifest.xml',
            __DIR__ . '/../Content/App/Manifest/_fixtures/minimal/manifest.xml',
        ];
        $commandTester->execute(['manifests' => $files]);

        static::assertEquals(0, $commandTester->getStatusCode());
        static::assertStringContainsString('[OK]', $commandTester->getDisplay());
    }
}

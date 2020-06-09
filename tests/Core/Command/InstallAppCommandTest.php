<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Command;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Swag\SaasConnect\Core\Content\App\Lifecycle\AppLifecycle;
use Swag\SaasConnect\Test\StorefrontAppRegistryTestBehaviour;
use Symfony\Component\Console\Tester\CommandTester;

class InstallAppCommandTest extends TestCase
{
    use IntegrationTestBehaviour;
    use StorefrontAppRegistryTestBehaviour;

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    public function setUp(): void
    {
        $this->appRepository = $this->getContainer()->get('saas_app.repository');
    }

    public function testInstallWithoutPermissions(): void
    {
        $commandTester = new CommandTester($this->createCommand(__DIR__ . '/_fixtures'));
        $commandTester->setInputs(['yes']);

        $commandTester->execute(['name' => 'withoutPermissions']);

        static::assertEquals(0, $commandTester->getStatusCode());

        static::assertStringContainsString('[OK] App installed successfully.', $commandTester->getDisplay());
    }

    public function testInstallWithForce(): void
    {
        $commandTester = new CommandTester($this->createCommand(__DIR__ . '/_fixtures'));

        $commandTester->execute(['name' => 'withPermissions', '-f' => true]);

        static::assertEquals(0, $commandTester->getStatusCode());

        static::assertStringContainsString('[OK] App installed successfully.', $commandTester->getDisplay());
    }

    public function testInstallWithPermissions(): void
    {
        $commandTester = new CommandTester($this->createCommand(__DIR__ . '/_fixtures'));
        $commandTester->setInputs(['yes']);

        $commandTester->execute(['name' => 'withPermissions']);

        static::assertEquals(0, $commandTester->getStatusCode());
        $display = $commandTester->getDisplay();

        // header permissions
        static::assertRegExp('/.*Resource\s+Privileges\s+\n.*/', $display);
        // content permissions
        static::assertRegExp('/.*product\s+write, delete\s+\n.*/', $display);
        static::assertRegExp('/.*category\s+write\s+\n.*/', $display);
        static::assertRegExp('/.*order\s+read\s+\n.*/', $display);

        static::assertStringContainsString('[OK] App installed successfully.', $display);
    }

    public function testInstallWithPermissionsCancel(): void
    {
        $commandTester = new CommandTester($this->createCommand(__DIR__ . '/_fixtures'));
        $commandTester->setInputs(['no']);

        $commandTester->execute(['name' => 'withPermissions']);

        static::assertEquals(1, $commandTester->getStatusCode());
        $display = $commandTester->getDisplay();

        // header permissions
        static::assertRegExp('/.*Resource\s+Privileges\s+\n.*/', $display);
        // content permissions
        static::assertRegExp('/.*product\s+write, delete\s+\n.*/', $display);
        static::assertRegExp('/.*category\s+write\s+\n.*/', $display);
        static::assertRegExp('/.*order\s+read\s+\n.*/', $display);

        static::assertStringContainsString('Aborting due to user input.', $commandTester->getDisplay());
    }

    public function testInstallWithNotFoundApp(): void
    {
        $commandTester = new CommandTester($this->createCommand(__DIR__ . '/_fixtures'));

        $commandTester->execute(['name' => 'Test']);

        static::assertEquals(1, $commandTester->getStatusCode());
        $display = $commandTester->getDisplay();

        static::assertStringContainsString('[ERROR] No app with name "Test" found.', $commandTester->getDisplay());
    }

    private function createCommand(string $appFolder): InstallAppCommand
    {
        return new InstallAppCommand(
            $appFolder,
            $this->getContainer()->get(AppLifecycle::class),
            new AppPrinter($this->appRepository)
        );
    }
}

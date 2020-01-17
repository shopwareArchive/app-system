<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Command;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Swag\SaasConnect\Core\Command\RefreshAppCommand;
use Swag\SaasConnect\Core\Content\App\AppLifecycle;
use Swag\SaasConnect\Core\Content\App\AppLoader;
use Swag\SaasConnect\Core\Content\App\AppService;
use Symfony\Component\Console\Tester\CommandTester;

class RefreshAppCommandTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var RefreshAppCommand
     */
    private $command;

    public function setUp(): void
    {
        /** @var EntityRepositoryInterface $appRepository */
        $appRepository = $this->getContainer()->get('app.repository');
        $this->command = new RefreshAppCommand(
            new AppService(
                $appRepository,
                $this->getContainer()->get(AppLifecycle::class),
                new AppLoader(__DIR__ . '/_fixtures')
            ),
            $appRepository
        );
    }

    public function testRefresh(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([]);

        static::assertEquals(0, $commandTester->getStatusCode());
        $display = $commandTester->getDisplay();

        // header
        static::assertRegExp('/.*Plugin\s+Label\s+Version\s+Author\s+\n.*/', $display);
        // content
        static::assertRegExp('/.*SwagApp\s+Swag App Test\s+1.0.0\s+shopware AG\s+\n.*/', $display);
    }
}

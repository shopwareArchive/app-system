<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Command;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Swag\SaasConnect\Core\Command\DeactivateAppCommand;
use Swag\SaasConnect\Test\AppSystemTestBehaviour;
use Symfony\Component\Console\Tester\CommandTester;

class DeactivateAppCommandTest extends TestCase
{
    use IntegrationTestBehaviour;
    use AppSystemTestBehaviour;

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    public function setUp(): void
    {
        $this->appRepository = $this->getContainer()->get('saas_app.repository');
    }

    public function testDeactivateApp(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/_fixtures/withoutPermissions');
        $appName = 'SwagApp';
        $commandTester = new CommandTester($this->getContainer()->get(DeactivateAppCommand::class));

        $commandTester->execute(['name' => $appName]);

        static::assertEquals(0, $commandTester->getStatusCode());

        static::assertStringContainsString('[OK] App deactivated successfully.', $commandTester->getDisplay());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $appName));

        $app = $this->appRepository->search($criteria, Context::createDefaultContext())->first();

        static::assertFalse($app->isActive());
    }

    public function testDeactivateNonExistingAppFails(): void
    {
        $commandTester = new CommandTester($this->getContainer()->get(DeactivateAppCommand::class));

        $appName = 'NonExisting';
        $commandTester->execute(['name' => $appName]);

        static::assertEquals(1, $commandTester->getStatusCode());

        static::assertStringContainsString("[ERROR] No app found for \"$appName\".", $commandTester->getDisplay());
    }
}
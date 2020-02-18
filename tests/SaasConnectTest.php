<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Shopware\Core\Framework\Plugin\PluginLifecycleService;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;

class SaasConnectTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var PluginEntity
     */
    private $plugin;

    /**
     * @var PluginLifecycleService
     */
    private $lifecycleService;

    /**
     * @var Connection
     */
    private $connection;

    public function setUp(): void
    {
        /** @var EntityRepositoryInterface $pluginRepo */
        $pluginRepo = $this->getContainer()->get('plugin.repository');
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', 'SaasConnect'));

        $this->plugin = $pluginRepo->search($criteria, Context::createDefaultContext())->first();

        $this->lifecycleService = $this->getContainer()->get(PluginLifecycleService::class);
        $this->connection = $this->getContainer()->get(Connection::class);
    }

    public function testUninstallWithKeepUserData(): void
    {
        $this->lifecycleService->uninstallPlugin($this->plugin, Context::createDefaultContext(), true);

        $tables = $this->connection->fetchAll('SHOW TABLES LIKE "swag%"');

        // swag_app, swag_app_translation, swag_app_action_button, swag_app_action_button_translation, swag_webhook
        static::assertCount(5, $tables);
    }

    public function testUninstallWithoutKeepUserData(): void
    {
        try {
            $this->lifecycleService->uninstallPlugin($this->plugin, Context::createDefaultContext());

            $tables = $this->connection->fetchAll('SHOW TABLES LIKE "swag%"');

            static::assertCount(0, $tables);
        } finally {
            // the drop tables in uninstall method will break the transaction, so we have to reinstall our plugin
            $this->lifecycleService->installPlugin($this->plugin, Context::createDefaultContext());
            $this->lifecycleService->activatePlugin($this->plugin, Context::createDefaultContext());
        }
    }
}

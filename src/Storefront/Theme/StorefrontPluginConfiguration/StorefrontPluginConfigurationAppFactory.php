<?php declare(strict_types=1);

namespace Swag\SaasConnect\Storefront\Theme\StorefrontPluginConfiguration;

use Shopware\Storefront\Theme\StorefrontPluginConfiguration\StorefrontPluginConfiguration;
use Shopware\Storefront\Theme\StorefrontPluginConfiguration\StorefrontPluginConfigurationFactory;
use Swag\SaasConnect\Core\Content\App\AppEntity;

/**
 * @Decoratable
 */
class StorefrontPluginConfigurationAppFactory implements StorefrontPluginConfigurationAppFactoryInterface
{
    /**
     * @var StorefrontPluginConfigurationFactory
     */
    private $configurationFactory;

    public function __construct(StorefrontPluginConfigurationFactory $configurationFactory)
    {
        $this->configurationFactory = $configurationFactory;
    }

    public function createFromApp(AppEntity $app): StorefrontPluginConfiguration
    {
        if (file_exists($app->getPath() . '/Resources/theme.json')) {
            return $this->configurationFactory->createThemeConfig($app->getName(), $app->getPath());
        }

        return $this->configurationFactory->createPluginConfig($app->getName(), $app->getPath());
    }
}

<?php declare(strict_types=1);

namespace Swag\SaasConnect\Storefront\Theme\StorefrontPluginConfiguration;

use Shopware\Core\System\Annotation\Concept\ExtensionPattern\Decoratable;
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

    /**
     * @var string
     */
    private $projectDir;

    public function __construct(StorefrontPluginConfigurationFactory $configurationFactory, string $projectDir)
    {
        $this->configurationFactory = $configurationFactory;
        $this->projectDir = $projectDir;
    }

    public function createFromApp(AppEntity $app): StorefrontPluginConfiguration
    {
        $absolutePath = $this->projectDir . '/' . $app->getPath();
        if (file_exists($absolutePath . '/Resources/theme.json')) {
            return $this->configurationFactory->createThemeConfig($app->getName(), $absolutePath);
        }

        return $this->configurationFactory->createPluginConfig($app->getName(), $absolutePath);
    }
}

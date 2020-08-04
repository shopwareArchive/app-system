<?php declare(strict_types=1);

namespace Swag\SaasConnect\Storefront\Theme\Lifecycle;

use Shopware\Core\Framework\Context;
use Shopware\Storefront\Theme\StorefrontPluginRegistryInterface;
use Shopware\Storefront\Theme\ThemeLifecycleHandler as CoreThemeLifecycleHandler;
use Swag\SaasConnect\Core\Content\App\AppEntity;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Swag\SaasConnect\Storefront\Theme\StorefrontPluginConfiguration\StorefrontPluginConfigurationAppFactoryInterface;

class ThemeLifecycleHandler
{
    /**
     * @var StorefrontPluginRegistryInterface
     */
    private $themeRegistry;

    /**
     * @var StorefrontPluginConfigurationAppFactoryInterface
     */
    private $themeConfigFactory;

    /**
     * @var CoreThemeLifecycleHandler
     */
    private $themeLifecycleHandler;

    /**
     * @var string
     */
    private $projectDir;

    public function __construct(
        StorefrontPluginRegistryInterface $themeRegistry,
        StorefrontPluginConfigurationAppFactoryInterface $themeConfigFactory,
        CoreThemeLifecycleHandler $themeLifecycleHandler,
        string $projectDir
    ) {
        $this->themeRegistry = $themeRegistry;
        $this->themeConfigFactory = $themeConfigFactory;
        $this->themeLifecycleHandler = $themeLifecycleHandler;
        $this->projectDir = $projectDir;
    }

    public function handleAppUpdate(Manifest $app, Context $context): void
    {
        $configurationCollection = $this->themeRegistry->getConfigurations();
        $config = $configurationCollection->getByTechnicalName($app->getMetadata()->getName());

        if (!$config) {
            $appEntity = (new AppEntity())->assign([
                'name' => $app->getMetadata()->getName(),
                'path' => str_replace($this->projectDir . '/', '', $app->getPath()),
            ]);
            $config = $this->themeConfigFactory->createFromApp($appEntity);
            $configurationCollection = clone $configurationCollection;
            $configurationCollection->add($config);
        }

        $this->themeLifecycleHandler->handleThemeInstallOrUpdate(
            $config,
            $configurationCollection,
            $context
        );
    }

    public function handleAppActivation(AppEntity $app, Context $context): void
    {
        $configurationCollection = $this->themeRegistry->getConfigurations();
        $config = $configurationCollection->getByTechnicalName($app->getName());

        if (!$config) {
            $config = $this->themeConfigFactory->createFromApp($app);
            $configurationCollection = clone $configurationCollection;
            $configurationCollection->add($config);
        }

        $this->themeLifecycleHandler->handleThemeInstallOrUpdate(
            $config,
            $configurationCollection,
            $context
        );
    }

    public function handleUninstall(string $appName, Context $context): void
    {
        $config = $this->themeRegistry->getConfigurations()->getByTechnicalName($appName);

        if (!$config) {
            return;
        }

        $this->themeLifecycleHandler->handleThemeUninstall($config, $context);
    }
}

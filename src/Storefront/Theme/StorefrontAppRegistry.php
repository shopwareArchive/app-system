<?php declare(strict_types=1);

namespace Swag\SaasConnect\Storefront\Theme;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Annotation\Concept\ExtensionPattern\Decoratable;
use Shopware\Storefront\Theme\StorefrontPluginConfiguration\StorefrontPluginConfigurationCollection;
use Shopware\Storefront\Theme\StorefrontPluginRegistryInterface;
use Swag\SaasConnect\Core\Content\App\AppEntity;
use Swag\SaasConnect\Storefront\Theme\StorefrontPluginConfiguration\StorefrontPluginConfigurationAppFactoryInterface;

/**
 * @Decoratable
 */
class StorefrontAppRegistry implements StorefrontPluginRegistryInterface
{
    /**
     * @var StorefrontPluginRegistryInterface
     */
    private $inner;

    /**
     * @var StorefrontPluginConfigurationCollection|null
     */
    private $pluginConfigurations;

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    /**
     * @var StorefrontPluginConfigurationAppFactoryInterface
     */
    private $configurationFactory;

    public function __construct(
        StorefrontPluginRegistryInterface $inner,
        EntityRepositoryInterface $appRepository,
        StorefrontPluginConfigurationAppFactoryInterface $configurationFactory
    ) {
        $this->inner = $inner;
        $this->appRepository = $appRepository;
        $this->configurationFactory = $configurationFactory;
    }

    public function getConfigurations(): StorefrontPluginConfigurationCollection
    {
        if ($this->pluginConfigurations) {
            return $this->pluginConfigurations;
        }

        $configs = $this->inner->getConfigurations();
        $this->addAppConfigs($configs);

        return $this->pluginConfigurations = $configs;
    }

    private function addAppConfigs(StorefrontPluginConfigurationCollection $configs): void
    {
        $apps = $this->appRepository->search(new Criteria(), Context::createDefaultContext())->getEntities();
        /** @var AppEntity $app */
        foreach ($apps as $app) {
            $config = $this->configurationFactory->createFromApp($app);

            if (!$config->getIsTheme() && !$config->hasFilesToCompile()) {
                continue;
            }

            $configs->add($config);
        }
    }
}

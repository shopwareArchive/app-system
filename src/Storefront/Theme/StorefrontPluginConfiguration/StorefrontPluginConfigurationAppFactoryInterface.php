<?php declare(strict_types=1);

namespace Swag\SaasConnect\Storefront\Theme\StorefrontPluginConfiguration;

use Shopware\Core\System\Annotation\Concept\ExtensionPattern\Decoratable;
use Shopware\Storefront\Theme\StorefrontPluginConfiguration\StorefrontPluginConfiguration;
use Swag\SaasConnect\Core\Content\App\AppEntity;

/**
 * @Decoratable
 */
interface StorefrontPluginConfigurationAppFactoryInterface
{
    public function createFromApp(AppEntity $app): StorefrontPluginConfiguration;
}

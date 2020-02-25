<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\Template;

use Shopware\Core\System\Annotation\Concept\ExtensionPattern\Decoratable;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;

/**
 * @Decoratable
 */
interface TemplateLoaderInterface
{
    /**
     * Returns the list of template paths the given app ships
     * @return array<string>
     */
    public function getTemplatePathsForApp(Manifest $app): array;

    /**
     * Returns the content of the template
     */
    public function getTemplateContent(string $path, Manifest $app): string;
}

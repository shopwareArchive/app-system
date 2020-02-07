<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle;

use Shopware\Core\System\Annotation\Concept\ExtensionPattern\Decoratable;
use Shopware\Core\System\SystemConfig\Exception\XmlParsingException;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Symfony\Component\Finder\Finder;

/**
 * @Decoratable
 */
class AppLoader implements AppLoaderInterface
{
    /**
     * @var string
     */
    private $appDir;

    public function __construct(string $appDir)
    {
        $this->appDir = $appDir;
    }

    /**
     * @return array<Manifest>
     */
    public function load(): array
    {
        $finder = new Finder();
        $finder->in($this->appDir)
            ->name('manifest.xml');

        $manifests = [];
        foreach ($finder->files() as $xml) {
            try {
                $manifests[] = Manifest::createFromXmlFile($xml->getPathname());
            } catch (XmlParsingException $e) {
                //nth, if app is already registered it will be deleted
            }
        }

        return $manifests;
    }

    public function getIcon(Manifest $app): ?string
    {
        if (!$app->getMetadata()->getIcon()) {
            return null;
        }

        $iconPath = sprintf('%s/%s', $app->getPath(), $app->getMetadata()->getIcon() ?: '');
        $icon = @file_get_contents($iconPath);

        if (!$icon) {
            return null;
        }

        return $icon;
    }
}

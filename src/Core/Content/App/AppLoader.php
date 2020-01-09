<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App;

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
     * @return Manifest[]
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
}

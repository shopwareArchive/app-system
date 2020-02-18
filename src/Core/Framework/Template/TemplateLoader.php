<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\Template;

use Shopware\Core\System\Annotation\Concept\ExtensionPattern\Decoratable;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Symfony\Component\Finder\Finder;

/**
 * @Decoratable
 */
class TemplateLoader implements TemplateLoaderInterface
{
    private const ALLOWED_TEMPLATE_DIRS = [
        'storefront',
        'documents',
    ];

    public function getTemplatePathsForApp(Manifest $app): array
    {
        $viewDirectory = $app->getPath() . '/views';

        if (!is_dir($viewDirectory)) {
            return [];
        }

        $finder = new Finder();
        $finder->files()
            ->in($viewDirectory)
            ->name('*.html.twig')
            ->path(self::ALLOWED_TEMPLATE_DIRS)
            ->ignoreUnreadableDirs();

        return array_values(array_map(static function (\SplFileInfo $file) use ($viewDirectory): string {
            // remove viewDirectory + any leading slashes from pathname
            return ltrim(mb_substr($file->getPathname(), mb_strlen($viewDirectory)), '/');
        }, iterator_to_array($finder)));
    }

    /**
     * Returns the content of the template
     */
    public function getTemplateContent(string $path, Manifest $app): string
    {
        $content = @file_get_contents($app->getPath() . '/views/' . $path);

        if ($content === false) {
            throw new \RuntimeException(sprintf('Unable to read file from: %s.', $app->getPath() . '/views/' . $path));
        }

        return $content;
    }
}

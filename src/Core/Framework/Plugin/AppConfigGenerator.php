<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Framework\Plugin;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\BundleConfigGeneratorInterface;
use Shopware\Storefront\Theme\StorefrontPluginRegistryInterface;
use Swag\SaasConnect\Core\Content\App\AppCollection;
use Swag\SaasConnect\Core\Content\App\AppEntity;
use Symfony\Component\Finder\Finder;

class AppConfigGenerator implements BundleConfigGeneratorInterface
{
    /**
     * @var BundleConfigGeneratorInterface
     */
    private $inner;

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    /**
     * @var StorefrontPluginRegistryInterface|null
     */
    private $storefrontPluginRegistry;

    /**
     * @var string
     */
    private $projectDir;

    public function __construct(
        BundleConfigGeneratorInterface $inner,
        EntityRepositoryInterface $appRepository,
        ?StorefrontPluginRegistryInterface $storefrontPluginRegistry,
        string $projectDir
    ) {
        $this->inner = $inner;
        $this->appRepository = $appRepository;
        $this->storefrontPluginRegistry = $storefrontPluginRegistry;
        $this->projectDir = $projectDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return $this->getConfigForApps($this->inner->getConfig());
    }

    /**
     * @param array<string, array<string|array<string>|array<string, string|null|array<string>>>> $config
     * @return array<string, array<string|array<string>|array<string, string|null|array<string>>>>
     */
    private function getConfigForApps(array $config): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));

        /** @var AppCollection $apps */
        $apps = $this->appRepository->search($criteria, Context::createDefaultContext());

        /** @var AppEntity $app */
        foreach ($apps as $app) {
            $config[$app->getName()] = [
                'basePath' => $app->getPath() . '/',
                'views' => ['Resources/views'],
                'technicalName' => str_replace('_', '-', $app->getNameAsSnakeCase()),
                'storefront' => [
                    'path' => 'Resources/app/storefront/src',
                    'entryFilePath' => $this->getEntryFile($app, 'Resources/app/storefront/src'),
                    'webpack' => $this->getWebpackConfig($app, 'Resources/app/storefront'),
                    'styleFiles' => $this->getStyleFiles($app),
                ],
            ];
        }

        return $config;
    }

    private function getEntryFile(AppEntity $app, string $componentPath): ?string
    {
        $path = trim($componentPath, '/');
        $absolutePath = $this->projectDir . '/' . $app->getPath() . '/' . $path;

        return file_exists($absolutePath . '/main.ts') ? $path . '/main.ts'
            : (file_exists($absolutePath . '/main.js') ? $path . '/main.js'
                : null);
    }

    private function getWebpackConfig(AppEntity $app, string $componentPath): ?string
    {
        $path = trim($componentPath, '/');
        $absolutePath = $this->projectDir . '/' . $app->getPath() . '/' . $path;
        if (!file_exists($absolutePath . '/build/webpack.config.js')) {
            return null;
        }

        return $path . '/build/webpack.config.js';
    }

    /**
     * @return array<string>
     */
    private function getStyleFiles(AppEntity $app): array
    {
        $files = [];
        if ($this->storefrontPluginRegistry) {
            $config = $this->storefrontPluginRegistry->getConfigurations()->getByTechnicalName($app->getName());

            if ($config) {
                return $config->getStyleFiles()->getFilepaths();
            }
        }

        $path = $this->projectDir . '/' . $app->getPath() . '/Resources/app/storefront/src/scss';
        if (is_dir($path)) {
            $finder = new Finder();
            $finder->in($path)->files()->depth(0);

            foreach ($finder->getIterator() as $file) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}

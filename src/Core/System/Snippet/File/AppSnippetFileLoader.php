<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\System\Snippet\File;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Snippet\Files\GenericSnippetFile;
use Shopware\Core\System\Snippet\Files\SnippetFileCollection;
use Shopware\Core\System\Snippet\Files\SnippetFileLoaderInterface;
use Swag\SaasConnect\Core\Content\App\AppEntity;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class AppSnippetFileLoader implements SnippetFileLoaderInterface
{
    /**
     * @var SnippetFileLoaderInterface
     */
    private $inner;

    /**
     * @var EntityRepositoryInterface
     */
    private $appRepository;

    /**
     * @var string
     */
    private $projectDir;

    public function __construct(
        SnippetFileLoaderInterface $inner,
        EntityRepositoryInterface $appRepository,
        string $projectDir
    ) {
        $this->inner = $inner;
        $this->appRepository = $appRepository;
        $this->projectDir = $projectDir;
    }

    public function loadSnippetFilesIntoCollection(SnippetFileCollection $snippetFileCollection): void
    {
        $this->inner->loadSnippetFilesIntoCollection($snippetFileCollection);

        $apps = $this->appRepository->search(new Criteria(), Context::createDefaultContext())->getEntities();

        foreach ($apps as $app) {
            foreach ($this->loadSnippetFilesFromApp($app) as $snippetFile) {
                $snippetFileCollection->add($snippetFile);
            }
        }
    }

    /**
     * @return array<GenericSnippetFile>
     */
    private function loadSnippetFilesFromApp(AppEntity $app): array
    {
        $finder = $this->getSnippetFinder($app);

        $snippetFiles = [];

        foreach ($finder->getIterator() as $fileInfo) {
            $nameParts = explode('.', $fileInfo->getFilenameWithoutExtension());

            $snippetFile = $this->createSnippetFile($nameParts, $fileInfo, $app);

            if ($snippetFile) {
                $snippetFiles[] = $snippetFile;
            }
        }

        return $snippetFiles;
    }

    private function getSnippetFinder(AppEntity $app): Finder
    {
        $snippetDir = $this->projectDir . '/' . $app->getPath() . '/Resources/snippet';
        $finder = new Finder();
        $finder->in($snippetDir)
            ->files()
            ->name('*.json');

        return $finder;
    }

    /**
     * @param array<string> $nameParts
     */
    private function createSnippetFile(array $nameParts, SplFileInfo $fileInfo, AppEntity $app): ?GenericSnippetFile
    {
        switch (count($nameParts)) {
            case 2:
                return $this->getSnippetFile($nameParts, $fileInfo, $app);
            case 3:
                return $this->getBaseSnippetFile($nameParts, $fileInfo, $app);
        }

        return null;
    }

    /**
     * @param array<string> $nameParts
     */
    private function getSnippetFile(array $nameParts, SplFileInfo $fileInfo, AppEntity $app): GenericSnippetFile
    {
        return new GenericSnippetFile(
            implode('.', $nameParts),
            $fileInfo->getPathname(),
            $nameParts[1],
            $app->getAuthor() ?? '',
            false
        );
    }

    /**
     * @param array<string> $nameParts
     */
    private function getBaseSnippetFile(array $nameParts, SplFileInfo $fileInfo, AppEntity $app): GenericSnippetFile
    {
        return new GenericSnippetFile(
            implode('.', [$nameParts[0], $nameParts[1]]),
            $fileInfo->getPathname(),
            $nameParts[1],
            $app->getAuthor() ?? '',
            $nameParts[2] === 'base'
        );
    }
}

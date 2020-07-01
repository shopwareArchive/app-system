<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\System\Snippet\File;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Shopware\Core\System\Snippet\Files\GenericSnippetFile;
use Shopware\Core\System\Snippet\Files\SnippetFileCollection;
use Shopware\Core\System\Snippet\Files\SnippetFileLoaderInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class AppSnippetFileLoader implements SnippetFileLoaderInterface
{
    /**
     * @var SnippetFileLoaderInterface
     */
    private $inner;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        SnippetFileLoaderInterface $inner,
        Connection $connection,
        string $projectDir
    ) {
        $this->inner = $inner;
        $this->projectDir = $projectDir;
        $this->connection = $connection;
    }

    public function loadSnippetFilesIntoCollection(SnippetFileCollection $snippetFileCollection): void
    {
        $this->inner->loadSnippetFilesIntoCollection($snippetFileCollection);

        $apps = $this->getApps();
        foreach ($apps as $app) {
            foreach ($this->loadSnippetFilesFromApp($app) as $snippetFile) {
                $snippetFileCollection->add($snippetFile);
            }
        }
    }

    /**
     * @return array<array<string, string>>
     */
    private function getApps(): array
    {
        return $this->connection->executeQuery('
            SELECT `path`, `author`
            FROM `saas_app`
        ')->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * @param array<string, string> $app
     * @return array<GenericSnippetFile>
     */
    private function loadSnippetFilesFromApp(array $app): array
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

    /**
     * @param array<string, string> $app
     */
    private function getSnippetFinder(array $app): Finder
    {
        $snippetDir = $this->projectDir . '/' . $app['path'] . '/Resources/snippet';
        $finder = new Finder();
        $finder->in($snippetDir)
            ->files()
            ->name('*.json');

        return $finder;
    }

    /**
     * @param array<string> $nameParts
     * @param array<string, string> $app
     */
    private function createSnippetFile(array $nameParts, SplFileInfo $fileInfo, array $app): ?GenericSnippetFile
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
     * @param array<string, string> $app
     */
    private function getSnippetFile(array $nameParts, SplFileInfo $fileInfo, array $app): GenericSnippetFile
    {
        return new GenericSnippetFile(
            implode('.', $nameParts),
            $fileInfo->getPathname(),
            $nameParts[1],
            $app['author'] ?? '',
            false
        );
    }

    /**
     * @param array<string> $nameParts
     * @param array<string, string> $app
     */
    private function getBaseSnippetFile(array $nameParts, SplFileInfo $fileInfo, array $app): GenericSnippetFile
    {
        return new GenericSnippetFile(
            implode('.', [$nameParts[0], $nameParts[1]]),
            $fileInfo->getPathname(),
            $nameParts[1],
            $app['author'] ?? '',
            $nameParts[2] === 'base'
        );
    }
}

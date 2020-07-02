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
            foreach ($this->loadSnippetFilesFromApp($app['author'] ?? '', $app['path']) as $snippetFile) {
                $snippetFileCollection->add($snippetFile);
            }
        }
    }

    /**
     * @param bool $isAbsolutePath is used for remote app loading in cloud environments,
     *                             therefore it's always false for local apps
     * @return array<GenericSnippetFile>
     */
    public function loadSnippetFilesFromApp(string $author, string $appPath, bool $isAbsolutePath = false): array
    {
        $snippetDir = $this->getSnippetDir($appPath, $isAbsolutePath);
        if (!is_dir($snippetDir)) {
            return [];
        }

        $finder = $this->getSnippetFinder($snippetDir);

        $snippetFiles = [];

        foreach ($finder->getIterator() as $fileInfo) {
            $nameParts = explode('.', $fileInfo->getFilenameWithoutExtension());

            $snippetFile = $this->createSnippetFile($nameParts, $fileInfo, $author);

            if ($snippetFile) {
                $snippetFiles[] = $snippetFile;
            }
        }

        return $snippetFiles;
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

    private function getSnippetFinder(string $snippetDir): Finder
    {
        $finder = new Finder();
        $finder->in($snippetDir)
            ->files()
            ->name('*.json');

        return $finder;
    }

    /**
     * @param array<string> $nameParts
     */
    private function createSnippetFile(array $nameParts, SplFileInfo $fileInfo, string $author): ?GenericSnippetFile
    {
        switch (count($nameParts)) {
            case 2:
                return $this->getSnippetFile($nameParts, $fileInfo, $author);
            case 3:
                return $this->getBaseSnippetFile($nameParts, $fileInfo, $author);
        }

        return null;
    }

    /**
     * @param array<string> $nameParts
     */
    private function getSnippetFile(array $nameParts, SplFileInfo $fileInfo, string $author): GenericSnippetFile
    {
        return new GenericSnippetFile(
            implode('.', $nameParts),
            $fileInfo->getPathname(),
            $nameParts[1],
            $author,
            false
        );
    }

    /**
     * @param array<string> $nameParts
     */
    private function getBaseSnippetFile(array $nameParts, SplFileInfo $fileInfo, string $author): GenericSnippetFile
    {
        return new GenericSnippetFile(
            implode('.', [$nameParts[0], $nameParts[1]]),
            $fileInfo->getPathname(),
            $nameParts[1],
            $author,
            $nameParts[2] === 'base'
        );
    }

    private function getSnippetDir(string $path, bool $isAbsolute): string
    {
        // add project path if path is not absolute already
        if (!$isAbsolute) {
            $path = $this->projectDir . '/' . $path;
        }

        return $path . '/Resources/snippet';
    }
}

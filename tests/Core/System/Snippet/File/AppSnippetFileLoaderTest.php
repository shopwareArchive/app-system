<?php declare(strict_types=1);

namespace Swag\SaasRufus\Test\Core\System\Snippet\File;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\System\Snippet\Files\SnippetFileCollection;
use Shopware\Core\System\Snippet\Files\SnippetFileLoader;
use Shopware\Core\System\Snippet\Files\SnippetFileLoaderInterface;
use Swag\SaasConnect\Core\System\Snippet\File\AppSnippetFileLoader;
use Swag\SaasConnect\Test\AppSystemTestBehaviour;

class AppSnippetFileLoaderTest extends TestCase
{
    use IntegrationTestBehaviour;
    use AppSystemTestBehaviour;

    /**
     * @var AppSnippetFileLoader
     */
    private $appSnippetLoader;

    /**
     * @var MockObject|SnippetFileLoaderInterface
     */
    private $coreSnippetLoaderMock;

    public function setUp(): void
    {
        $this->coreSnippetLoaderMock = $this->createMock(SnippetFileLoaderInterface::class);

        $this->appSnippetLoader = new AppSnippetFileLoader(
            $this->coreSnippetLoaderMock,
            $this->getContainer()->get(Connection::class),
            $this->getContainer()->getParameter('kernel.project_dir')
        );
    }

    public function testDecoration(): void
    {
        static::assertInstanceOf(
            AppSnippetFileLoader::class,
            $this->getContainer()->get(SnippetFileLoader::class)
        );
    }

    public function testLoadSnippetFilesIntoCollectionWithoutSnippetFiles(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/../../../Content/App/Manifest/_fixtures/test');

        $collection = new SnippetFileCollection();

        $this->appSnippetLoader->loadSnippetFilesIntoCollection($collection);

        static::assertCount(0, $collection);
    }

    public function testLoadSnippetFilesIntoCollection(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/_fixtures/AppWithSnippets');

        $collection = new SnippetFileCollection();

        $this->appSnippetLoader->loadSnippetFilesIntoCollection($collection);

        static::assertCount(2, $collection);

        $snippetFile = $collection->getSnippetFilesByIso('de-DE')[0];
        static::assertEquals('storefront.de-DE', $snippetFile->getName());
        static::assertEquals(
            __DIR__ . '/_fixtures/AppWithSnippets/Resources/snippet/storefront.de-DE.json',
            $snippetFile->getPath()
        );
        static::assertEquals('de-DE', $snippetFile->getIso());
        static::assertEquals('shopware AG', $snippetFile->getAuthor());
        static::assertFalse($snippetFile->isBase());

        $snippetFile = $collection->getSnippetFilesByIso('en-GB')[0];
        static::assertEquals('storefront.en-GB', $snippetFile->getName());
        static::assertEquals(
            __DIR__ . '/_fixtures/AppWithSnippets/Resources/snippet/storefront.en-GB.json',
            $snippetFile->getPath()
        );
        static::assertEquals('en-GB', $snippetFile->getIso());
        static::assertEquals('shopware AG', $snippetFile->getAuthor());
        static::assertFalse($snippetFile->isBase());
    }

    public function testLLoadBaseSnippetFilesIntoCollection(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/_fixtures/AppWithBaseSnippets');

        $collection = new SnippetFileCollection();

        $this->appSnippetLoader->loadSnippetFilesIntoCollection($collection);

        static::assertCount(2, $collection);

        $snippetFile = $collection->getSnippetFilesByIso('de-DE')[0];
        static::assertEquals('storefront.de-DE', $snippetFile->getName());
        static::assertEquals(
            __DIR__ . '/_fixtures/AppWithBaseSnippets/Resources/snippet/storefront.de-DE.base.json',
            $snippetFile->getPath()
        );
        static::assertEquals('de-DE', $snippetFile->getIso());
        static::assertEquals('shopware AG', $snippetFile->getAuthor());
        static::assertTrue($snippetFile->isBase());

        $snippetFile = $collection->getSnippetFilesByIso('en-GB')[0];
        static::assertEquals('storefront.en-GB', $snippetFile->getName());
        static::assertEquals(
            __DIR__ . '/_fixtures/AppWithBaseSnippets/Resources/snippet/storefront.en-GB.base.json',
            $snippetFile->getPath()
        );
        static::assertEquals('en-GB', $snippetFile->getIso());
        static::assertEquals('shopware AG', $snippetFile->getAuthor());
        static::assertTrue($snippetFile->isBase());
    }

    public function testLoadSnippetFilesIntoCollectionIgnoresWrongFilenames(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/_fixtures/SnippetsWithWrongName');

        $collection = new SnippetFileCollection();

        $this->appSnippetLoader->loadSnippetFilesIntoCollection($collection);

        static::assertCount(0, $collection);
    }
}

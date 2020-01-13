<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest;

class Manifest
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $metadata;

    /**
     * @var array
     */
    private $admin = [];

    private function __construct(string $path, array $data)
    {
        $this->path = $path;
        $this->metadata = $data['metadata'];
        $this->admin = $data['admin'];
    }

    public static function createFromXmlFile(string $xmlFile): self
    {
        $reader = new ManifestReader();
        $data = $reader->read($xmlFile);

        return new self(dirname($xmlFile), $data);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getAdmin(): array
    {
        return $this->admin;
    }
}

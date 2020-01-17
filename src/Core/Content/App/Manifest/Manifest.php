<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest;

class Manifest
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var array<string, string|array<string, string>>
     */
    private $metadata;

    /**
     * @var array<string, array<array<string, string|int|bool|array<string, string>|null>>>
     */
    private $admin = [];

    /**
     * @var array<string, string>
     */
    private $permissions = [];

    /**
     * @param array<string, array<string, string|array<string, string>|array<array<string, string|int|bool|array<string, string>|null>>>> $data
     */
    private function __construct(string $path, array $data)
    {
        $this->path = $path;
        /** @var array<string, string|array<string, string>> $metadata */
        $metadata = $data['metadata'];
        $this->metadata = $metadata;
        /** @var array<string, array<array<string, string|int|bool|array<string, string>|null>>> $admin */
        $admin = $data['admin'];
        $this->admin = $admin;
        /** @var array<string, string> $permissions */
        $permissions = $data['permissions'];
        $this->permissions = $permissions;
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

    /**
     * @return array<string, string|array<string, string>>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @return array<string, array<array<string, string|int|bool|array<string, string>|null>>>
     */
    public function getAdmin(): array
    {
        return $this->admin;
    }

    /**
     * @return array<string, string>
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }
}

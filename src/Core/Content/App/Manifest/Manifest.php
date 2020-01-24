<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Manifest;

use Shopware\Core\System\SystemConfig\Exception\XmlParsingException;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\Admin;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\Metadata;
use Swag\SaasConnect\Core\Content\App\Manifest\Xml\Permissions;
use Symfony\Component\Config\Util\XmlUtils;

class Manifest
{
    private const XSD_FILE = __DIR__ . '/Schema/manifest-1.0.xsd';

    /**
     * @var string
     */
    private $path;

    /**
     * @var Metadata
     */
    private $metadata;

    /**
     * @var Admin|null
     */
    private $admin;

    /**
     * @var Permissions|null
     */
    private $permissions;

    private function __construct(string $path, Metadata $metadata, ?Admin $admin, ?Permissions $permissions)
    {
        $this->path = $path;
        $this->metadata = $metadata;
        $this->admin = $admin;
        $this->permissions = $permissions;
    }

    public static function createFromXmlFile(string $xmlFile): self
    {
        try {
            $doc = XmlUtils::loadFile($xmlFile, self::XSD_FILE);
        } catch (\Exception $e) {
            throw new XmlParsingException($xmlFile, $e->getMessage());
        }

        /** @var \DOMElement $meta */
        $meta = $doc->getElementsByTagName('meta')->item(0);
        $metadata = Metadata::fromXml($meta);

        /** @var \DOMElement|null $admin */
        $admin = $doc->getElementsByTagName('admin')->item(0);
        $admin = $admin === null ? null : Admin::fromXml($admin);

        /** @var \DOMElement|null $permissions */
        $permissions = $doc->getElementsByTagName('permissions')->item(0);
        $permissions = $permissions === null ? null : Permissions::fromXml($permissions);

        return new self(dirname($xmlFile), $metadata, $admin, $permissions);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function getAdmin(): ?Admin
    {
        return $this->admin;
    }

    public function getPermissions(): ?Permissions
    {
        return $this->permissions;
    }
}

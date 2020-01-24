<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Lifecycle;

use Shopware\Core\Framework\Context;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;

class RefreshableAppDryRun implements AppLifecycleInterface
{
    /**
     * @var array<Manifest>
     */
    private $toBeInstalled = [];

    /**
     * @var array<Manifest>
     */
    private $toBeUpdated = [];

    /**
     * @var array<string>
     */
    private $toBeDeleted = [];

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function install(Manifest $manifest, Context $context): void
    {
        $this->toBeInstalled[] = $manifest;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     *
     * @param array<string, string> $app
     */
    public function update(Manifest $manifest, array $app, Context $context): void
    {
        $this->toBeUpdated[] = $manifest;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     *
     * @param array<string, string> $app
     */
    public function delete(string $appName, array $app, Context $context): void
    {
        $this->toBeDeleted[] = $appName;
    }

    /**
     * @return array<Manifest>
     */
    public function getToBeInstalled(): array
    {
        return $this->toBeInstalled;
    }

    /**
     * @return array<Manifest>
     */
    public function getToBeUpdated(): array
    {
        return $this->toBeUpdated;
    }

    /**
     * @return array<string>
     */
    public function getToBeDeleted(): array
    {
        return $this->toBeDeleted;
    }

    public function isEmpty(): bool
    {
        return count($this->toBeInstalled) === 0
            && count($this->toBeUpdated) === 0
            && count($this->toBeDeleted) === 0;
    }
}

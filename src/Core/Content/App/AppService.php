<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App;

use Shopware\Core\Framework\Context;

class AppService
{
    /**
     * @var AppLifecycleIterator
     */
    private $appLifecycleIterator;

    /**
     * @var AppLifecycle
     */
    private $appLifecycle;

    public function __construct(
        AppLifecycleIterator $appLifecycleIterator,
        AppLifecycle $appLifecycle
    ) {
        $this->appLifecycleIterator = $appLifecycleIterator;
        $this->appLifecycle = $appLifecycle;
    }

    public function refreshApps(Context $context): void
    {
        $this->appLifecycleIterator->iterate($this->appLifecycle, $context);
    }

    public function getRefreshableAppInfo(Context $context): RefreshableAppDryRun
    {
        $appInfo = new RefreshableAppDryRun();

        $this->appLifecycleIterator->iterate($appInfo, $context);

        return $appInfo;
    }
}

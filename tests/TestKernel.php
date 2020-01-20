<?php

namespace Swag\SaasConnect\Test;

use Shopware\Core\Kernel;

class TestKernel extends Kernel
{
    /**
     * Returns an array of bundles to register.
     *
     * @return iterable|BundleInterface[] An iterable of bundle instances
     */
    public function registerBundles()
    {
        yield from parent::registerBundles();

        yield new TestBundle();
    }

    public function getProjectDir()
    {
        return __DIR__ . '/../../../../';
    }
}

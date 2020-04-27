<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test\Core\Content\App\Lifecycle\Registration;

use PHPUnit\Framework\TestCase;
use Swag\SaasConnect\Core\Content\App\Lifecycle\Registration\StoreHandshake;

class StoreHandshakeTest extends TestCase
{
    public function testGetHandshakeFetchUrlIsUnimplemented(): void
    {
        $storeHandshake = new StoreHandshake();
        static::expectException(\RuntimeException::class);
        $storeHandshake->fetchUrl();
    }

    public function testGetHandshakeFetchAppProofIsUnimplemented(): void
    {
        $storeHandshake = new StoreHandshake();
        static::expectException(\RuntimeException::class);
        $storeHandshake->fetchAppProof();
    }
}

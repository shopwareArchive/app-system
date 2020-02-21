<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test;

use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

trait TwigResetCacheTestBehaviour
{
    /**
     * @before
     */
    public function clearTwigCache(): void
    {
        $twigEnv = $this->getContainer()
            ->get(Environment::class);

        $reflection = new \ReflectionClass($twigEnv);
        $prop = $reflection->getProperty('loadedTemplates');

        $prop->setAccessible(true);
        $prop->setValue($twigEnv, null);

        $reflection = new \ReflectionClass($twigEnv);
        $prop = $reflection->getProperty('templateClassPrefix');

        $prop->setAccessible(true);
        $prop->setValue($twigEnv, '__TwigTemplate_' . Uuid::randomHex() . '_');
    }

    abstract protected function getContainer(): ContainerInterface;
}

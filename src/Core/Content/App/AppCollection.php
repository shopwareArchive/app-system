<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @psalm-suppress MoreSpecificImplementedParamType
 *
 * @method void                  add(AppEntity $entity)
 * @method void                  set(string $key, AppEntity $entity)
 * @method \Generator<AppEntity> getIterator()
 * @method AppEntity[]           getElements()
 * @method AppEntity|null        get(string $key)
 * @method AppEntity|null        first()
 * @method AppEntity|null        last()
 */
class AppCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return AppEntity::class;
    }
}

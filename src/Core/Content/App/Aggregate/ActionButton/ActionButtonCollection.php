<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Aggregate\ActionButton;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @psalm-suppress MoreSpecificImplementedParamType
 *
 * @method void                  add(ActionButtonEntity $entity)
 * @method void                  set(string $key, ActionButtonEntity $entity)
 * @method \Generator<ActionButtonEntity> getIterator()
 * @method ActionButtonEntity[]           getElements()
 * @method ActionButtonEntity|null        get(string $key)
 * @method ActionButtonEntity|null        first()
 * @method ActionButtonEntity|null        last()
 */
class ActionButtonCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ActionButtonEntity::class;
    }
}

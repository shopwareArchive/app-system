<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Aggregate\ActionButtonTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\SaasConnect\Core\Content\App\Aggregate\ActionButton\ActionButtonDefinition;

class ActionButtonTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'saas_app_action_button_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ActionButtonTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ActionButtonTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return ActionButtonDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('label', 'label'))->addFlags(new Required()),
        ]);
    }
}

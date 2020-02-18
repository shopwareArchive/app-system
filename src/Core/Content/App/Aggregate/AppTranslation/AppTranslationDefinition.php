<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Aggregate\AppTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\SaasConnect\Core\Content\App\AppDefinition;

class AppTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'swag_app_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return AppTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return AppTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return AppDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('label', 'label'))->addFlags(new Required()),
            new LongTextField('description', 'description'),
        ]);
    }
}

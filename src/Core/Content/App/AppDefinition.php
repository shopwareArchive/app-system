<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App;

use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BlobField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ReadProtected;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\SaasConnect\Core\Content\App\Aggregate\ActionButton\ActionButtonDefinition;
use Swag\SaasConnect\Core\Content\App\Aggregate\AppTranslation\AppTranslationDefinition;

class AppDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'app';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return AppEntity::class;
    }

    public function getCollectionClass(): string
    {
        return AppCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('name', 'name'))->addFlags(new Required()),
            (new StringField('path', 'path'))->addFlags(new Required()),
            new StringField('author', 'author'),
            new StringField('copyright', 'copyright'),
            new StringField('license', 'license'),
            (new StringField('version', 'version'))->addFlags(new Required()),
            (new BlobField('icon', 'iconRaw'))->addFlags(
                new ReadProtected(SalesChannelApiSource::class,
                    AdminApiSource::class)
            ),
            (new StringField('icon', 'icon'))->addFlags(new WriteProtected(), new Runtime()),

            (new TranslationsAssociationField(AppTranslationDefinition::class, 'app_id'))->addFlags(
                new Required(),
                new CascadeDelete()
            ),
            new TranslatedField('label'),
            new TranslatedField('description'),

            new OneToManyAssociationField('actionButtons', ActionButtonDefinition::class, 'app_id'),
        ]);
    }
}

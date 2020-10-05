<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\System\CustomField\Aggregate\CustomFieldSet;

use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetDefinition;
use Swag\SaasConnect\Core\Content\App\AppDefinition;

class CustomFieldSetExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new FkField('saas_app_id', 'saasAppId', AppDefinition::class)
        );

        $collection->add(
            new ManyToOneAssociationField('saasApp', 'saas_app_id', AppDefinition::class)
        );
    }

    public function getDefinitionClass(): string
    {
        return CustomFieldSetDefinition::class;
    }
}

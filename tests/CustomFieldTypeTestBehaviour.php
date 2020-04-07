<?php declare(strict_types=1);

namespace Swag\SaasConnect\Test;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\CustomField\CustomFieldEntity;
use Swag\SaasConnect\Core\Content\App\AppCollection;
use Swag\SaasConnect\Core\Content\App\Lifecycle\AppLifecycle;
use Swag\SaasConnect\Core\Content\App\Manifest\Manifest;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait CustomFieldTypeTestBehaviour
{
    abstract protected function getContainer(): ContainerInterface;

    protected function importCustomField(string $manifestPath): CustomFieldEntity
    {
        $manifest = Manifest::createFromXmlFile($manifestPath);

        $context = Context::createDefaultContext();
        /** @var AppLifecycle $appLifecycle */
        $appLifecycle = $this->getContainer()->get(AppLifecycle::class);
        $appLifecycle->install($manifest, $context);

        /** @var EntityRepositoryInterface $appRepository */
        $appRepository = $this->getContainer()->get('saas_app.repository');
        $criteria = new Criteria();
        $criteria->addAssociation('customFieldSets.customFields');

        /** @var AppCollection $apps */
        $apps = $appRepository->search($criteria, $context)->getEntities();

        static::assertCount(1, $apps);
        static::assertEquals('SwagApp', $apps->first()->getName());

        static::assertCount(1, $apps->first()->getCustomFieldSets());
        $customFieldSet = $apps->first()->getCustomFieldSets()->first();
        static::assertEquals('custom_field_test', $customFieldSet->getName());

        static::assertCount(1, $customFieldSet->getCustomFields());

        return $customFieldSet->getCustomFields()->first();
    }
}

<?php declare(strict_types=1);

namespace Swag\SaasConnect\Core\Content\App\Subscriber;

use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\InsertCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetDefinition;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetEntity;
use Swag\SaasConnect\Core\Content\App\AppEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class CustomFieldProtectionSubscriber implements EventSubscriberInterface
{
    public const VIOLATION_NO_PERMISSION = 'no_permission_violation';

    /**
     * @var EntityRepositoryInterface
     */
    private $customFieldSetRepo;

    public function __construct(EntityRepositoryInterface $customFieldSetRepo)
    {
        $this->customFieldSetRepo = $customFieldSetRepo;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PreWriteValidationEvent::class => 'checkWrite',
        ];
    }

    public function checkWrite(PreWriteValidationEvent $event): void
    {
        $context = $event->getContext();

        if ($context->getSource() instanceof SystemSource) {
            return;
        }

        $integrationId = $this->getIntegrationId($context);
        $violationList = new ConstraintViolationList();

        foreach ($event->getCommands() as $command) {
            if (
                !($command->getDefinition() instanceof CustomFieldSetDefinition)
                || $command instanceof InsertCommand
            ) {
                continue;
            }

            $app = $this->fetchAssociatedApp($command, $context);
            if (!$app) {
                continue;
            }

            if ($integrationId !== $app->getIntegrationId()) {
                $this->addViolation($violationList, $command);
            }
        }
        if ($violationList->count() > 0) {
            $event->getExceptions()->add(new WriteConstraintViolationException($violationList));
        }
    }

    private function getIntegrationId(Context $context): ?string
    {
        $source = $context->getSource();
        if (!($source instanceof AdminApiSource)) {
            return null;
        }

        return $source->getIntegrationId();
    }

    private function fetchAssociatedApp(WriteCommand $command, Context $context): ?AppEntity
    {
        $id = Uuid::fromBytesToHex($command->getPrimaryKey()['id']);
        $fieldSet = $this->fetchCustomFieldSet($id, $context);
        /** @var AppEntity | null $app */
        $app = $fieldSet->getExtension('saasApp');

        return $app;
    }

    private function fetchCustomFieldSet(string $id, Context $context): CustomFieldSetEntity
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('saasApp');
        /** @var CustomFieldSetEntity $fieldSet */
        $fieldSet = $this->customFieldSetRepo->search($criteria, $context)->first();

        return $fieldSet;
    }

    private function addViolation(ConstraintViolationList $violationList, WriteCommand $command): void
    {
        $violationList->add(
            $this->buildViolation(
                'No permissions to %privilege%".',
                ['%privilege%' => 'write:custom_field_set'],
                '/' . $command->getDefinition()->getEntityName(),
                self::VIOLATION_NO_PERMISSION
            )
        );
    }

    /**
     * @param array<string, string> $parameters
     */
    private function buildViolation(
        string $messageTemplate,
        array $parameters,
        ?string $propertyPath = null,
        ?string $code = null
    ): ConstraintViolationInterface {
        return new ConstraintViolation(
            str_replace(array_keys($parameters), array_values($parameters), $messageTemplate),
            $messageTemplate,
            $parameters,
            null,
            $propertyPath,
            null,
            null,
            $code
        );
    }
}

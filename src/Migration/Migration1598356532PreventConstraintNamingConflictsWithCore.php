<?php declare(strict_types=1);

namespace Swag\SaasConnect\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1598356532PreventConstraintNamingConflictsWithCore extends MigrationStep
{
    /**
     * constrained to be renamed, indexed by table name
     * followed by assoc array of new constraint name and constraint indexed by old constraint name
     */
    private const RENAME_CONSTRAINT = [
        'saas_app' => [
            'json.app.modules' => [
                'newName' => 'json.saas_app.modules',
                'constraint' => '`json.saas_app.modules` CHECK (JSON_VALID(`modules`))',
            ],
            'fk.app.integration_id' => [
                'newName' => 'fk.saas_app.integration_id',
                'constraint' => '`fk.saas_app.integration_id` FOREIGN KEY (`integration_id`) REFERENCES `integration` (`id`) ON DELETE CASCADE ON UPDATE CASCADE',
            ],
            'fk.app.acl_role_id' => [
                'newName' => 'fk.saas_app.acl_role_id',
                'constraint' => '`fk.saas_app.acl_role_id` FOREIGN KEY (`acl_role_id`) REFERENCES `acl_role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE',
            ],
        ],
        'saas_app_translation' => [
            'fk.app_translation.saas_app_id' => [
                'newName' => 'fk.saas_app_translation.saas_app_id',
                'constraint' => '`fk.saas_app_translation.saas_app_id` FOREIGN KEY (`saas_app_id`) REFERENCES `saas_app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE',
            ],
            'fk.app_translation.language_id' => [
                'newName' => 'fk.saas_app_translation.language_id',
                'constraint' => '`fk.saas_app_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE',
            ],
        ],
        'saas_app_action_button' => [
            'fk.app_action_button.app_id' => [
                'newName' => 'fk.saas_app_action_button.app_id',
                'constraint' => '`fk.saas_app_action_button.app_id` FOREIGN KEY (`app_id`) REFERENCES `saas_app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE',
            ],
            'uniq.app_action_button.action' => [
                'newName' => 'uniq.saas_app_action_button.action',
                'constraint' => '`uniq.saas_app_action_button.action` UNIQUE (`action`, `app_id`)',
            ],
        ],
        'saas_app_action_button_translation' => [
            'fk.app_action_button_translation.saas_app_action_button_id' => [
                'newName' => 'fk.saas_app_action_button_translation.saas_app_action_button_id',
                'constraint' => '`fk.saas_app_action_button_translation.saas_app_action_button_id` FOREIGN KEY (`saas_app_action_button_id`) REFERENCES `saas_app_action_button` (`id`) ON DELETE CASCADE ON UPDATE CASCADE',
            ],
            'fk.app_action_button_translation.language_id' => [
                'newName' => 'fk.saas_app_action_button_translation.language_id',
                'constraint' => '`fk.saas_app_action_button_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE',
            ],
        ],
        'saas_webhook' => [
            'fk.webhook.app_id' => [
                'newName' => 'fk.saas_webhook.app_id',
                'constraint' => '`fk.saas_webhook.app_id` FOREIGN KEY (`app_id`) REFERENCES `saas_app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE',
            ],
            'uniq.webhook.name' => [
                'newName' => 'uniq.saas_webhook.name',
                'constraint' => '`uniq.saas_webhook.name` UNIQUE (`name`, `app_id`)',
            ],
        ],
        'saas_template' => [
            'fk.template.app_id' => [
                'newName' => 'fk.saas_template.app_id',
                'constraint' => '`fk.saas_template.app_id` FOREIGN KEY (`app_id`) REFERENCES `saas_app` (`id`) ON DELETE CASCADE ON UPDATE CASCADE',
            ],
        ],
    ];

    public function getCreationTimestamp(): int
    {
        return 1598356532;
    }

    /**
     * We needed to rename the column in the original migration to prevent compatibility issues with platform
     * For updates where the old column name is in use we migrate it to the new name
     */
    public function update(Connection $connection): void
    {
        foreach (self::RENAME_CONSTRAINT as $table => $constraints) {
            $existingConstraints = array_flip($connection->executeQuery('
                SELECT `CONSTRAINT_NAME`
                FROM `information_schema`.`table_constraints`
                WHERE `table_name` = :tableName AND `table_schema` = schema();
            ', ['tableName' => $table])->fetchAll(FetchMode::COLUMN));

            foreach ($constraints as $old => $new) {
                // Don't update constraint names if either old name does not exist or new name already exists
                if (!array_key_exists($old, $existingConstraints) ||
                    array_key_exists($new['newName'], $existingConstraints)) {
                    continue;
                }

                $connection->executeQuery(sprintf('
                        ALTER TABLE `%s`
                        DROP CONSTRAINT `%s`,
                        ADD CONSTRAINT %s;
                    ', $table, $old, $new['constraint']));
            }
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }
}

<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\console;

use yii\db\Connection;
use yii\di\Instance;

/**
 * Class Migration trait
 *
 *
 * @property Connection $db
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
trait MigrationTrait
{
    
    /**
     * @see \yii\console\controllers\MigrateController::truncateDatabase()
     */
    protected function truncateDatabase()
    {
        $schemas = $this->db->schema->getTableSchemas();
        
        // First drop all foreign keys,
        foreach ($schemas as $schema) {
            if ($schema->foreignKeys) {
                foreach ($schema->foreignKeys as $name => $foreignKey) {
                    $this->db->createCommand()->dropForeignKey($name, $schema->name)->execute();
                    $this->stdout("Foreign key $name dropped.\n");
                }
            }
        }
        
        // Then drop the tables:
        foreach ($schemas as $schema) {
            try {
                $this->db->createCommand()->dropTable($schema->name)->execute();
                $this->stdout("Table {$schema->name} dropped.\n");
            } catch (\Exception $e) {
                if ($this->isViewRelated($e->getMessage())) {
                    $this->db->createCommand()->dropView($schema->name)->execute();
                    $this->stdout("View {$schema->name} dropped.\n");
                } else {
                    $this->stdout("Cannot drop {$schema->name} Table .\n");
                }
            }
        }
    }
    
    /**
     * Determines whether the error message is related to deleting a view or not
     *
     * @param string $errorMessage
     *
     * @return bool
     *
     * @see \yii\console\controllers\MigrateController::isViewRelated()
     */
    private function isViewRelated($errorMessage)
    {
        $dropViewErrors = [
            'DROP VIEW to delete view', // SQLite
            'SQLSTATE[42S02]', // MySQL
        ];
        
        foreach ($dropViewErrors as $dropViewError) {
            if (strpos($errorMessage, $dropViewError) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
}
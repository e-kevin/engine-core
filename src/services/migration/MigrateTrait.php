<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\services\migration;

use Yii;
use yii\console\controllers\MigrateController;
use yii\db\Connection;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class MigrateTrait
 *
 * @property Connection $db
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
trait MigrateTrait
{
    
    /**
     * Upgrades with the specified migration class.
     *
     * @param string $class the migration class name
     *
     * @see \yii\console\controllers\BaseMigrateController::migrateUp()
     *
     * @return bool whether the migration is successful
     */
    protected function migrateUp($class)
    {
        if ($class === MigrateController::BASE_MIGRATION) {
            return true;
        }
        
        $this->stdout("*** applying $class\n", Console::FG_YELLOW);
        $start = microtime(true);
        $migration = $this->createMigration($class);
        if ($migration->up() !== false) {
            $this->addMigrationHistory($class);
            $time = microtime(true) - $start;
            $this->stdout("*** applied $class (time: " . sprintf('%.3f', $time) . "s)\n\n", Console::FG_GREEN);
            
            return true;
        }
        
        $time = microtime(true) - $start;
        $this->stdout("*** failed to apply $class (time: " . sprintf('%.3f', $time) . "s)\n\n", Console::FG_RED);
        
        return false;
    }
    
    /**
     * Downgrades with the specified migration class.
     *
     * @param string $class the migration class name
     *
     * @see \yii\console\controllers\BaseMigrateController::migrateDown()
     *
     * @return bool whether the migration is successful
     */
    protected function migrateDown($class)
    {
        if ($class === MigrateController::BASE_MIGRATION) {
            return true;
        }
        
        $this->stdout("*** reverting $class\n", Console::FG_YELLOW);
        $start = microtime(true);
        $migration = $this->createMigration($class);
        if ($migration->down() !== false) {
            $this->removeMigrationHistory($class);
            $time = microtime(true) - $start;
            $this->stdout("*** reverted $class (time: " . sprintf('%.3f', $time) . "s)\n\n", Console::FG_GREEN);
            
            return true;
        }
        
        $time = microtime(true) - $start;
        $this->stdout("*** failed to revert $class (time: " . sprintf('%.3f', $time) . "s)\n\n", Console::FG_RED);
        
        return false;
    }
    
    /**
     * Removes existing migration from the history.
     *
     * @param string $version migration version name.
     *
     * @see \yii\console\controllers\MigrateController::removeMigrationHistory()
     */
    protected function removeMigrationHistory($version)
    {
        $command = $this->db->createCommand();
        $command->delete($this->migrationTable, [
            'version' => $version,
        ])->execute();
    }
    
    /**
     * Creates a new migration instance.
     *
     * @param string $class the migration class name
     *
     * @see MigrateController::createMigration()
     *
     * @return \yii\db\Migration|object the migration instance
     */
    protected function createMigration($class)
    {
        $this->includeMigrationFile($class);
        
        return Yii::createObject([
            'class'   => $class,
            'db'      => $this->db,
            'compact' => $this->compact,
        ]);
    }
    
    /**
     * Includes the migration file for a given migration class name.
     *
     * This function will do nothing on namespaced migrations, which are loaded by
     * autoloading automatically. It will include the migration file, by searching
     * [[migrationPath]] for classes without namespace.
     *
     * @param string $class the migration class name.
     *
     * @see   MigrateController::includeMigrationFile()
     *
     * @since 2.0.12
     */
    protected function includeMigrationFile($class)
    {
        $class = trim($class, '\\');
        if (strpos($class, '\\') === false) {
            if (is_array($this->migrationPath)) {
                foreach ($this->migrationPath as $path) {
                    $file = $path . DIRECTORY_SEPARATOR . $class . '.php';
                    if (is_file($file)) {
                        require_once $file;
                        break;
                    }
                }
            } else {
                $file = $this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php';
                require_once $file;
            }
        }
    }
    
    /**
     * Adds new migration entry to the history.
     *
     * @param string $version migration version name.
     *
     * @see MigrateController::addMigrationHistory()
     */
    protected function addMigrationHistory($version)
    {
        $command = $this->db->createCommand();
        $command->insert($this->migrationTable, [
            'version'    => $version,
            'apply_time' => time(),
        ])->execute();
    }
    
    /**
     * Returns the file path matching the give namespace.
     *
     * @param string $namespace namespace.
     *
     * @see \yii\console\controllers\BaseMigrateController::getNamespacePath()
     *
     * @return string file path.
     */
    private function getNamespacePath($namespace)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, Yii::getAlias('@' . str_replace('\\', '/', $namespace)));
    }
    
    /**
     * Returns the migrations that are not applied.
     *
     * @see \yii\console\controllers\BaseMigrateController::getNewMigrations()
     *
     * @return array list of new migrations
     */
    protected function getNewMigrations()
    {
        $applied = [];
        foreach ($this->getMigrationHistory(null) as $class => $time) {
            $applied[trim($class, '\\')] = true;
        }
        
        $migrationPaths = [];
        if (is_array($this->migrationPath)) {
            foreach ($this->migrationPath as $path) {
                $migrationPaths[] = [$path, ''];
            }
        } elseif (!empty($this->migrationPath)) {
            $migrationPaths[] = [$this->migrationPath, ''];
        }
        foreach ($this->migrationNamespaces as $namespace) {
            $migrationPaths[] = [$this->getNamespacePath($namespace), $namespace];
        }
        
        $migrations = [];
        foreach ($migrationPaths as $item) {
            list($migrationPath, $namespace) = $item;
            if (!file_exists($migrationPath)) {
                continue;
            }
            $handle = opendir($migrationPath);
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $migrationPath . DIRECTORY_SEPARATOR . $file;
                if (preg_match('/^(m(\d{6}_?\d{6})\D.*?)\.php$/is', $file, $matches) && is_file($path)) {
                    $class = $matches[1];
                    if (!empty($namespace)) {
                        $class = $namespace . '\\' . $class;
                    }
                    $time = str_replace('_', '', $matches[2]);
                    if (!isset($applied[$class])) {
                        $migrations[$time . '\\' . $class] = $class;
                    }
                }
            }
            closedir($handle);
        }
        ksort($migrations);
        
        return array_values($migrations);
    }
    
    /**
     * Returns the migration history.
     *
     * @param int $limit the maximum number of records in the history to be returned. `null` for "no limit".
     *
     * @see MigrateController::getMigrationHistory()
     *
     * @return array the migration history
     */
    protected function getMigrationHistory($limit)
    {
        if ($this->db->schema->getTableSchema($this->migrationTable, true) === null) {
            $this->createMigrationHistoryTable();
        }
        $query = (new Query())
            ->select(['version', 'apply_time'])
            ->from($this->migrationTable)
            ->orderBy(['apply_time' => SORT_DESC, 'version' => SORT_DESC]);
        
        if (empty($this->migrationNamespaces)) {
            $query->limit($limit);
            $rows = $query->all($this->db);
            $history = ArrayHelper::map($rows, 'version', 'apply_time');
            unset($history[MigrateController::BASE_MIGRATION]);
            
            return $history;
        }
        
        $rows = $query->all($this->db);
        
        $history = [];
        foreach ($rows as $key => $row) {
            if ($row['version'] === MigrateController::BASE_MIGRATION) {
                continue;
            }
            if (preg_match('/m?(\d{6}_?\d{6})(\D.*)?$/is', $row['version'], $matches)) {
                $time = str_replace('_', '', $matches[1]);
                $row['canonicalVersion'] = $time;
            } else {
                $row['canonicalVersion'] = $row['version'];
            }
            $row['apply_time'] = (int)$row['apply_time'];
            $history[] = $row;
        }
        
        usort($history, function ($a, $b) {
            if ($a['apply_time'] === $b['apply_time']) {
                if (($compareResult = strcasecmp($b['canonicalVersion'], $a['canonicalVersion'])) !== 0) {
                    return $compareResult;
                }
                
                return strcasecmp($b['version'], $a['version']);
            }
            
            return ($a['apply_time'] > $b['apply_time']) ? -1 : +1;
        });
        
        $history = array_slice($history, 0, $limit);
        
        $history = ArrayHelper::map($history, 'version', 'apply_time');
        
        return $history;
    }
    
    /**
     * @see MigrateController::createMigrationHistoryTable()
     */
    protected function createMigrationHistoryTable()
    {
        $tableName = $this->db->schema->getRawTableName($this->migrationTable);
        $this->stdout("Creating migration history table \"$tableName\"...", Console::FG_YELLOW);
        $this->db->createCommand()->createTable($this->migrationTable, [
            'version'    => 'varchar(' . MigrateController::MAX_NAME_LENGTH . ') NOT NULL PRIMARY KEY',
            'apply_time' => 'integer',
        ])->execute();
        $this->db->createCommand()->insert($this->migrationTable, [
            'version'    => MigrateController::BASE_MIGRATION,
            'apply_time' => time(),
        ])->execute();
        $this->stdout("Done.\n", Console::FG_GREEN);
    }
    
    private $_migrationNameLimit;
    
    /**
     * @see MigrateController::getMigrationNameLimit()
     */
    protected function getMigrationNameLimit()
    {
        if ($this->_migrationNameLimit !== null) {
            return $this->_migrationNameLimit;
        }
        $tableSchema = $this->db->schema ? $this->db->schema->getTableSchema($this->migrationTable, true) : null;
        if ($tableSchema !== null) {
            return $this->_migrationNameLimit = $tableSchema->columns['version']->size;
        }
        
        return MigrateController::MAX_NAME_LENGTH;
    }
    
}
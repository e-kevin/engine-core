<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\services\migration;

use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\console\Application;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\di\Instance;
use yii\helpers\Console;

/**
 * 数据库迁移助手类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class MigrateHelper extends BaseObject
{
    
    use MigrateTrait;
    
    /**
     * @var string the name of the table for keeping applied migration information.
     */
    public $migrationTable = '{{%migration}}';
    
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection to use
     * when applying migrations. Starting from version 2.0.3, this can also be a configuration array
     * for creating the object.
     */
    public $db = 'db';
    
    /**
     * @var bool indicates whether the console output should be compacted.
     * If this is set to true, the individual commands ran within the migration will not be output to the console.
     * Default is false, in other words the output is fully verbose by default.
     * @since 2.0.13
     */
    public $compact = false;
    
    /**
     * @var array list of namespaces containing the migration classes.
     *
     * Migration namespaces should be resolvable as a [path alias](guide:concept-aliases) if prefixed with `@`, e.g. if
     * you specify the namespace `app\migrations`, the code `Yii::getAlias('@app/migrations')` should be able to return
     * the file path to the directory this namespace refers to. This corresponds with the [autoloading
     * conventions](guide:concept-autoloading) of Yii.
     *
     * For example:
     *
     * ```php
     * [
     *     'app\migrations',
     *     'some\extension\migrations',
     * ]
     * ```
     *
     * @since 2.0.10
     * @see   $migrationPath
     */
    public $migrationNamespaces = [];
    
    /**
     * @var string|array the directory containing the migration classes. This can be either
     * a [path alias](guide:concept-aliases) or a directory path.
     *
     * Migration classes located at this path should be declared without a namespace.
     * Use [[migrationNamespaces]] property in case you are using namespaced migrations.
     *
     * If you have set up [[migrationNamespaces]], you may set this field to `null` in order
     * to disable usage of migrations that are not namespaced.
     *
     * Since version 2.0.12 you may also specify an array of migration paths that should be searched for
     * migrations to load. This is mainly useful to support old extensions that provide migrations
     * without namespace and to adopt the new feature of namespaced migrations while keeping existing migrations.
     *
     * In general, to load migrations from different locations, [[migrationNamespaces]] is the preferable solution
     * as the migration name contains the origin of the migration in the history, which is not the case when
     * using multiple migration paths.
     *
     * @see $migrationNamespaces
     */
    public $migrationPath = [];
    
    /**
     * @var bool whether to run the command interactively.
     */
    public $interactive = true;
    
    private $_isConsoleApp;
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        if (empty($this->migrationNamespaces) && empty($this->migrationPath)) {
            throw new InvalidConfigException('At least one of `migrationPath` or `migrationNamespaces` should be specified.');
        }
        
        $this->migrationNamespaces = (array)$this->migrationNamespaces;
        
        foreach ($this->migrationNamespaces as $key => $value) {
            $this->migrationNamespaces[$key] = trim($value, '\\');
        }
        
        if (is_array($this->migrationPath)) {
            foreach ($this->migrationPath as $i => $path) {
                $this->migrationPath[$i] = Yii::getAlias($path);
            }
        } elseif ($this->migrationPath !== null) {
            $path = Yii::getAlias($this->migrationPath);
            if (!is_dir($path)) {
                throw new InvalidConfigException("Migration failed. Directory specified in migrationPath doesn't exist: {$this->migrationPath}");
            }
            $this->migrationPath = $path;
        }
        
        $this->_isConsoleApp = Yii::$app instanceof Application;
        $this->db = Instance::ensure($this->db, Connection::class);
    }
    
    private $_controller;
    
    /**
     * @return Controller|object|\yii\console\Controller
     */
    public function getController()
    {
        if (null === $this->_controller) {
            if ($this->_isConsoleApp) {
                $this->_controller = Yii::$app->controller;
                $this->_controller->interactive = $this->interactive;
            } else {
                $this->_controller = Yii::createObject([
                    'class' => Controller::class,
                ]);
            }
        }
        
        return $this->_controller;
    }
    
    /**
     * Upgrades the application by applying new migrations.
     *
     * For example,
     *
     * ```
     * yii migrate     # apply all new migrations
     * yii migrate 3   # apply the first 3 new migrations
     * ```
     *
     * @param int $limit the number of new migrations to be applied. If 0, it means
     *                   applying all available new migrations.
     *
     * @see \yii\console\controllers\BaseMigrateController::actionUp()
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function up($limit = 0)
    {
        $migrations = $this->getNewMigrations();
        if (empty($migrations)) {
            $this->stdout("No new migrations found. Your system is up-to-date.\n", Console::FG_GREEN, Console::BOLD, Console::UNDERLINE);
            
            return ExitCode::OK;
        }
        
        $total = count($migrations);
        $limit = (int)$limit;
        if ($limit > 0) {
            $migrations = array_slice($migrations, 0, $limit);
        }
        
        $n = count($migrations);
        if ($n === $total) {
            $this->stdout("Total $n new " . ($n === 1 ? 'migration' : 'migrations') . " to be applied:\n", Console::FG_YELLOW);
        } else {
            $this->stdout("Total $n out of $total new " . ($total === 1 ? 'migration' : 'migrations') . " to be applied:\n", Console::FG_YELLOW);
        }
        
        foreach ($migrations as $migration) {
            $nameLimit = $this->getMigrationNameLimit();
            if ($nameLimit !== null && strlen($migration) > $nameLimit) {
                $this->stdout("\nThe migration name '$migration' is too long. Its not possible to apply this migration.\n", Console::FG_RED);
                
                return ExitCode::UNSPECIFIED_ERROR;
            }
            $this->stdout("\t$migration\n");
        }
        $this->stdout("\n");
        
        $applied = 0;
        if ($this->confirm('Apply the above ' . ($n === 1 ? 'migration' : 'migrations') . '?')) {
            foreach ($migrations as $migration) {
                if (!$this->migrateUp($migration)) {
                    $this->stdout("\n$applied from $n " . ($applied === 1 ? 'migration was' : 'migrations were') . " applied.\n", Console::FG_RED);
                    $this->stdout("\nMigration failed. The rest of the migrations are canceled.\n", Console::FG_RED);
                    
                    return ExitCode::UNSPECIFIED_ERROR;
                }
                $applied++;
            }
            
            $this->stdout("\n$n " . ($n === 1 ? 'migration was' : 'migrations were') . " applied.\n", Console::FG_GREEN);
            $this->stdout("\nMigrated up successfully.\n", Console::FG_GREEN);
        } else {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        return ExitCode::OK;
    }
    
    /**
     * Downgrades the application by reverting old migrations.
     *
     * For example,
     *
     * ```
     * yii migrate/down     # revert the last migration
     * yii migrate/down 3   # revert the last 3 migrations
     * yii migrate/down all # revert all migrations
     * ```
     *
     * @param int|string $limit the number of migrations to be reverted. Defaults to 1,
     *                          meaning the last applied migration will be reverted. When value is "all", all
     *                          migrations will be reverted.
     *
     * @see \yii\console\controllers\BaseMigrateController::actionDown()
     *
     * @throws Exception if the number of the steps specified is less than 1.
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function down($limit = 1)
    {
        if ($limit === 'all') {
            $limit = null;
        } else {
            $limit = (int)$limit;
            if ($limit < 1) {
                throw new Exception('The step argument must be greater than 0.');
            }
        }
        
        $migrations = $this->getMigrationHistory($limit);
        
        if (empty($migrations)) {
            $this->stdout("No migration has been done before.\n", Console::FG_YELLOW);
            
            return ExitCode::OK;
        }
        
        $migrations = array_keys($migrations);
        
        $n = count($migrations);
        $this->stdout("Total $n " . ($n === 1 ? 'migration' : 'migrations') . " to be reverted:\n", Console::FG_YELLOW);
        foreach ($migrations as $migration) {
            $this->stdout("\t$migration\n");
        }
        $this->stdout("\n");
        
        $reverted = 0;
        if ($this->confirm('Revert the above ' . ($n === 1 ? 'migration' : 'migrations') . '?')) {
            foreach ($migrations as $migration) {
                if (!$this->migrateDown($migration)) {
                    $this->stdout("\n$reverted from $n " . ($reverted === 1 ? 'migration was' : 'migrations were') . " reverted.\n", Console::FG_RED);
                    $this->stdout("\nMigration failed. The rest of the migrations are canceled.\n", Console::FG_RED);
                    
                    return ExitCode::UNSPECIFIED_ERROR;
                }
                $reverted++;
            }
            $this->stdout("\n$n " . ($n === 1 ? 'migration was' : 'migrations were') . " reverted.\n", Console::FG_GREEN);
            $this->stdout("\nMigrated down successfully.\n", Console::FG_GREEN);
        } else {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        return ExitCode::OK;
    }
    
    /**
     * Prints a string to STDOUT.
     *
     * You may optionally format the string with ANSI codes by
     * passing additional parameters using the constants defined in [[\yii\helpers\Console]].
     *
     * Example:
     *
     * ```
     * $this->stdout('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
     * ```
     *
     * @param string $string  the string to print
     * @param int    ...$args additional parameters to decorate the output
     *
     * @return int|bool Number of bytes printed or false on error
     */
    public function stdout($string)
    {
        if ($this->_isConsoleApp) {
            if ($this->getController()->isColorEnabled()) {
                $args = func_get_args();
                array_shift($args);
                $string = Console::ansiFormat($string, $args);
            }
        }
        
        return $this->getController()->stdout($string);
    }
    
    /**
     * Asks user to confirm by typing y or n.
     *
     * A typical usage looks like the following:
     *
     * ```php
     * if ($this->confirm("Are you sure?")) {
     *     echo "user typed yes\n";
     * } else {
     *     echo "user typed no\n";
     * }
     * ```
     *
     * @param string $message to echo out before waiting for user input
     * @param bool   $default this value is returned if no selection is made.
     *
     * @return bool whether user confirmed.
     * Will return true if [[interactive]] is false.
     */
    public function confirm($message, $default = false)
    {
        return $this->getController()->confirm($message, $default);
    }
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\services;

use EngineCore\base\Service;
use EngineCore\services\migration\MigrateHelper;
use Yii;
use yii\console\ExitCode;
use yii\db\Connection;

/**
 * 数据库迁移服务类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Migration extends Service
{
    
    private $table = '{{%migration}}';
    
    /**
     * the name of the table for keeping applied migration information.
     *
     * @param string $table
     *
     * @return $this
     */
    public function table(string $table)
    {
        $this->table = $table;
        
        return $this;
    }
    
    private $namespaces = [];
    
    /**
     * @see \yii\console\controllers\BaseMigrateController::$migrationNamespaces
     *
     * @param array $namespaces
     *
     * @return $this
     */
    public function namespaces(array $namespaces)
    {
        $this->namespaces = $namespaces;
        
        return $this;
    }
    
    private $path = ['@app/migrations'];
    
    /**
     * @see \yii\console\controllers\BaseMigrateController::$migrationPath
     *
     * @param string|array $path
     *
     * @return $this
     */
    public function path($path)
    {
        $this->path = $path;
        
        return $this;
    }
    
    private $interactive = true;
    
    /**
     * @see \yii\console\Controller::$interactive
     *
     * @param bool $interactive
     *
     * @return $this
     */
    public function interactive(bool $interactive)
    {
        $this->interactive = $interactive;
        
        return $this;
    }
    
    private $db = 'db';
    
    /**
     * the DB connection object or the application component ID of the DB connection to use
     * when applying migrations. Starting from version 2.0.3, this can also be a configuration array
     * for creating the object.
     *
     * @param Connection|array|string $db
     *
     * @return $this
     */
    public function db($db)
    {
        $this->db = $db;
        
        return $this;
    }
    
    private $compact = false;
    
    /**
     * indicates whether the console output should be compacted.
     * If this is set to true, the individual commands ran within the migration will not be output to the console.
     * Default is false, in other words the output is fully verbose by default.
     * @since 2.0.13
     *
     * @param bool $compact
     *
     * @return $this
     */
    public function compact(bool $compact)
    {
        $this->compact = $compact;
        
        return $this;
    }
    
    /**
     * Upgrades the application by applying new migrations.
     *
     * @param int $limit
     *
     * @return bool
     */
    public function up($limit = 0): bool
    {
        /**
         * fixme 获取到异常时会输出内容，修正为根据不用应用是否显示输出内容
         * @see \yii\db\Migration::printException()
         */
        return $this->getMigrate()->up($limit) === ExitCode::OK ? true : false;
    }
    
    /**
     * Downgrades the application by reverting old migrations.
     *
     * @param int $limit
     *
     * @return bool
     */
    public function down($limit = 1): bool
    {
        return $this->getMigrate()->down($limit) === ExitCode::OK ? true : false;
    }
    
    /**
     * @return object|MigrateHelper
     */
    public function getMigrate()
    {
        return Yii::createObject([
            'class'               => MigrateHelper::class,
            'migrationTable'      => $this->table,
            'migrationNamespaces' => $this->namespaces,
            'migrationPath'       => $this->path,
            'db'                  => $this->db,
            'interactive'         => $this->interactive,
            'compact'             => $this->compact,
        ]);
    }
    
}
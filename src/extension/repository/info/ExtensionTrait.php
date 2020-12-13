<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository\info;

use EngineCore\{
    helpers\FileHelper, helpers\ConsoleHelper
};
use Yii;
use yii\base\InvalidArgumentException;
use yii\db\Connection;
use yii\di\Instance;

/**
 * Class ExtensionTrait
 *
 * @property Connection $db 数据库连接组件，读写属性
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
trait ExtensionTrait
{
    
    private $_db;
    
    /**
     * 获取数据库连接组件
     *
     * @return Connection
     */
    public function getDb()
    {
        if (null === $this->_db) {
            $this->setDb();
        }
        
        return $this->_db;
    }
    
    /**
     * 设置数据库连接组件
     *
     * @param string|array $db DB连接的组件ID或配置，默认为'db'组件
     */
    public function setDb($db = 'db')
    {
        $this->_db = Instance::ensure($db, Connection::class);
        $this->_db->getSchema()->refresh();
        $this->_db->enableSlaves = false;
    }
    
    /**
     * 执行migrate操作
     *
     * @param string $type 操作类型
     */
    protected function runMigrate($type)
    {
        $migrationPath = [];
        foreach ($this->getMigrationPath() as $path) {
            if (FileHelper::isDir(Yii::getAlias($path, false))) {
                $migrationPath[] = Yii::getAlias($path);
            }
        }
        if (!empty($migrationPath)) {
            $action = "migrate/";
            switch ($type) {
                case 'up':
                    $action .= 'up';
                    break;
                case 'down':
                    $action .= 'down';
                    break;
                default:
                    throw new InvalidArgumentException('The "type" property is invalid.');
            }
            //执行
            ConsoleHelper::run(sprintf("%s {$action} %s",
                Yii::getAlias(ConsoleHelper::getCommander()),
                implode(' ', $this->migrateParams($migrationPath))
            ), false);
        }
    }
    
    /**
     * 构造migrate参数
     *
     * @param array $migrationPath
     *
     * @return array
     */
    protected function migrateParams(array $migrationPath)
    {
        $params['interactive'] = '--interactive=0';
        $params['limit'] = 'all';
        $params['migrationPath'] = '--migrationPath=' . implode(',', $migrationPath);
        
        return $params;
    }
    
}
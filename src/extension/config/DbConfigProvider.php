<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\config;

use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\caching\Cache;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;

/**
 * 数据库形式的配置提供者实现类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class DbConfigProvider extends BaseObject implements ConfigProviderInterface
{
    
    use ConfigTrait;
    
    /**
     * @var Connection|string|array 数据库组件配置
     */
    public $db = 'db';
    
    /**
     * @var Cache|string|array 缓存组件名
     */
    public $cache = 'commonCache';
    
    /**
     * @var int 缓存间隔，默认为'0'，代表不过期
     */
    public $cacheDuration = 0;
    
    /**
     * @var string 配置数据库表名
     */
    public $tableName;
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if ($this->tableName === null) {
            throw new InvalidConfigException('The `$tableName` property must be set.');
        }
        $this->db = Instance::ensure($this->db, Connection::class);
        $this->cache = Instance::ensure(Yii::$app->has($this->cache) ?
            $this->cache
            : [
                'class'     => 'yii\\caching\\FileCache',
                'cachePath' => '@common/runtime/cache',
            ], Cache::class);
    }
    
    private $_all;
    
    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        if ($this->_all === null) {
            $this->_all = $this->cache->getOrSet($this->configKey, function () {
                return (new Query())
                    ->select([$this->nameField, $this->valueField, $this->extraField])
                    ->from($this->tableName)
                    ->indexBy($this->nameField)
                    ->all($this->db);
            }, $this->cacheDuration);
        }
        
        return $this->_all;
    }
    
    /**
     * {@inheritdoc}
     */
    public function clearCache()
    {
        $this->_all = null;
        $this->cache->delete($this->configKey);
    }
    
}
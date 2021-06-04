<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\setting;

use EngineCore\Ec;
use EngineCore\extension\repository\info\ExtensionInfo;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\db\ExpressionInterface;
use yii\db\Query;
use yii\di\Instance;

/**
 * 数据库方式的设置数据提供器
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class DbProvider extends BaseObject implements SettingProviderInterface
{
    
    use SettingProviderTrait;
    
    /**
     * @var string 设置数据库表名
     */
    public $tableName = '{{%' . ExtensionInfo::EXT_RAND_CODE . 'setting}}';
    
    /**
     * @var Connection|string|array 数据库组件配置
     */
    public $db = 'db';
    
    /**
     * 查询条件
     *
     * @var string|array|ExpressionInterface
     * @see QueryInterface::where()
     */
    public $condition;
    
    /**
     * 查询条件绑定参数
     *
     * @var array
     */
    public $params = [];
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        if (null === $this->tableName) {
            throw new InvalidConfigException(get_called_class() . ': The `$tableName` property must be set.');
        }
        $this->db = Instance::ensure($this->db, Connection::class);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        return Ec::$service->getSystem()->getCache()->getOrSet(self::SETTING_KEY, function () {
            $query = new Query();
            if (null !== $this->condition) {
                $query->where($this->condition, $this->params);
            }
            
            return $query->select($this->getFieldMap())->from($this->tableName)->indexBy($this->getNameField())->all($this->db);
        }, $this->getCacheDuration());
    }
    
    /**
     * {@inheritdoc}
     */
    public function clearCache()
    {
        Ec::$service->getSystem()->getCache()->getComponent()->delete(self::SETTING_KEY);
    }
    
}
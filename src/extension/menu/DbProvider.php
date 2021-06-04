<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\menu;

use EngineCore\Ec;
use EngineCore\extension\repository\info\ExtensionInfo;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\db\ExpressionInterface;
use yii\db\Query;
use yii\di\Instance;

/**
 * 数据库方式的菜单数据提供器
 *
 * 通过数据库获取菜单数据
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class DbProvider extends BaseObject implements MenuProviderInterface
{
    
    use MenuProviderTrait;
    
    /**
     * @var string 菜单数据库表名
     */
    public $tableName = '{{%' . ExtensionInfo::EXT_RAND_CODE . 'menu}}';
    
    /**
     * @var Connection|string|array 数据库组件配置
     */
    public $db = 'db';
    
    /**
     * @var string|array|ExpressionInterface
     * @see QueryInterface::where()
     */
    public $condition;
    
    /**
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
    public function getAll(): array
    {
        return Ec::$service->getSystem()->getCache()->getOrSet(self::MENU_KEY, function () {
            $query = new Query();
            if (null !== $this->condition) {
                $query->where($this->condition, $this->params);
            }
            $list = $query->select($this->getFieldMap())->from($this->tableName)->indexBy('id')->orderBy('order')->all($this->db);
            // 获取菜单层级数
            $getLevel = function ($list, $hasParent) {
                $level = 1;
                while ($hasParent) {
                    $hasParent = isset($list[$hasParent]) ? $list[$hasParent]['parent_id'] : false;
                    $level++;
                }
                
                return $level;
            };
            // 特定参数处理
            foreach ($list as &$row) {
                foreach ($row as $field => &$value) {
                    // 特殊字段处理
                    switch ($field) {
                        case 'config':
                            $value = $value ? json_decode($value, true) : [];
                            break;
                        case 'params':
                            if ($value) {
                                parse_str($value, $value);
                            } else {
                                $value = [];
                            }
                            break;
                    }
                }
                $row['level'] = $getLevel($list, $row['parent_id']);
            }
            
            return $list;
            
        }, $this->getCacheDuration());
    }
    
    /**
     * {@inheritdoc}
     */
    public function clearCache()
    {
        Ec::$service->getSystem()->getCache()->getComponent()->delete(self::MENU_KEY);
    }
    
}
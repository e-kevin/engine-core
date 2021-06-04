<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\menu;

use EngineCore\db\ActiveRecord;
use EngineCore\helpers\ArrayHelper;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * 菜单配置提供器实现类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class MenuProvider extends BaseObject implements MenuProviderInterface
{
    
    use MenuProviderTrait;
    
    /**
     * @var ActiveRecord
     */
    private $_model;
    
    /**
     * 菜单配置数据
     *
     * @var array
     */
    private $_menuConfig;
    
    private $_all;
    
    private $_allGroupByLevel;
    
    /**
     * {@inheritdoc}
     */
    public function getAll($level = null): array
    {
        if (null !== $level) {
            if (null === $this->_allGroupByLevel) {
                $this->_allGroupByLevel = ArrayHelper::index(
                    $this->_getAll(),
                    'id',
                    'level'
                );
            }
            
            return $this->_allGroupByLevel[$level] ?? [];
        }
        
        return $this->_getAll();
    }
    
    /**
     * 获取所有菜单数据
     *
     * @return array
     */
    protected function _getAll()
    {
        if (null === $this->_all) {
            $this->_all = [];
            if (empty($this->getMenuConfig())) {
                return $this->_all;
            }
            $increaseId = 0;
            foreach ($this->getMenuConfig() as $app => $items) {
                $this->_all = array_merge($this->_all, $this->_buildData($items, $increaseId, 0));
            }
            $this->_all = ArrayHelper::index($this->_all, 'id');
        }
        
        return $this->_all;
    }
    
    /**
     * {@inheritdoc}
     */
    public function clearCache()
    {
        $this->_all = $this->_allGroupByLevel = null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        if (null === $this->_model) {
            $this->setModel();
        }
        
        return $this->_model;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setModel($config = [])
    {
        if (!isset($config['class'])) {
            throw new InvalidConfigException('The `$model` property must contain the `class` key name.');
        }
        $this->_model = Yii::createObject($config);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMenuConfig()
    {
        if (null === $this->_menuConfig) {
            $this->setMenuConfig();
        }
        
        return $this->_menuConfig;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setMenuConfig($config = [])
    {
        foreach ($config ?: $this->getDefaultConfig() as $app => $row) {
            $this->_menuConfig[$app] = $this->_initMenu($row, $app, 1);
        }
    }
    
    
}
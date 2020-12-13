<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\base;

use EngineCore\helpers\ArrayHelper;
use Yii;
use yii\base\{
    InvalidConfigException, BaseObject
};

/**
 * 服务定位器，主要作用：
 * 1. 检测服务定位器是否符合EngineCore的服务类标准。
 * 2. 支持IDE代码提示功能。
 * 3. 支持`Yii::debug()`调试信息，方便调试。
 * 4. 约束服务组件仅能通过服务定位器来调用其子服务。
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ServiceLocator extends BaseObject
{
    
    /**
     * @var array 服务定位器配置
     */
    public $locators;
    
    /**
     * @var Service[] 服务定位器实例列表数据
     */
    private $_instance;
    
    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        // 补全服务定位器配置信息
        foreach ($this->coreLocators() as $id => $service) {
            if (!isset($config['locators'][$id])) {
                $config['locators'][$id] = $service;
            } elseif (is_array($config['locators'][$id]) && !isset($config['locators'][$id]['class'])) {
                $config['locators'][$id] = ArrayHelper::merge($service, $config['locators'][$id]);
            }
        }
        parent::__construct($config);
    }
    
    /**
     * 默认核心服务定位器配置
     *
     * @return array
     */
    public function coreLocators()
    {
        return [];
    }
    
    /**
     * 获取服务
     *
     * @param string $id 服务定位器ID，即'$locators'或'coreLocators()'配置中服务定位器的键名
     *
     * @return Service
     * @throws InvalidConfigException
     */
    public function get($id)
    {
        if (!isset($this->_instance[$id])) {
            if ($this->has($id)) {
                $locator = Yii::createObject($this->locators[$id]);
                if (!$locator instanceof Service) {
                    throw new InvalidConfigException("The required service locator `{$id}` must return an object extends `" . Service::class . '`.');
                }
                
                // 设置服务定位器ID
                $locator->setUniqueId($id);
                
                Yii::debug("Loading service: {$id}", __METHOD__);
                
                $this->_instance[$id] = $locator;
            } else {
                throw new InvalidConfigException("Unknown service locator ID: $id");
            }
        }
        
        return $this->_instance[$id];
    }
    
    /**
     * 是否存在指定服务定位器ID的配置
     *
     * @param string $id 服务ID
     *
     * @return bool
     */
    public function has($id)
    {
        return isset($this->locators[$id]);
    }
    
    /**
     * 递归获取服务，提供一种快捷的方式调用所需服务
     *
     * 目前仅支持两层服务
     *
     * @param string $id 需要获取的多层服务ID，如：'system.cache','extension.db'
     *
     * @return Service
     * @throws InvalidConfigException
     */
    public function getRecursive($id)
    {
        if (strpos($id, '.') === false) {
            throw new InvalidConfigException('Invalid param: ' . $id);
        }
        $arr = explode('.', $id);
        
        return $this->get(array_shift($arr))->getService(array_shift($arr));
    }
    
}
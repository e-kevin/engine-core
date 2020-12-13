<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\base;

use EngineCore\Ec;
use EngineCore\helpers\StringHelper;
use yii\base\InvalidConfigException;
use yii\base\Module;

/**
 * 支持系统调度功能（Dispatch）的基础模块类
 *
 * @property ServiceLocator $service 服务定位器
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Modularity extends Module
{
    
    /**
     * 更改默认路由是为了防止在系统使用调度服务时调度器命名空间不支持`default|public`等字符时的问题
     *
     * {@inheritdoc}
     */
    public $defaultRoute = 'common';
    
    /**
     * @var string 调度器命名空间
     */
    public $dispatchNamespace;
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if (null === $this->dispatchNamespace) {
            $this->dispatchNamespace = StringHelper::replace($this->controllerNamespace, 'controllers', 'dispatches');
        }
    }
    
    private $_service;
    
    /**
     * 获取服务定位器，用于管理模块内的服务组件
     *
     * @return ServiceLocator
     */
    public function getService()
    {
        if (null === $this->_service) {
            $this->setService(ServiceLocator::class);
        }
        
        return $this->_service;
    }
    
    /**
     * 设置服务定位器
     *
     * @param $serviceLocator
     *
     * @throws InvalidConfigException
     */
    public function setService($serviceLocator)
    {
        $this->_service = Ec::createObject($serviceLocator, [], ServiceLocator::class);
    }
    
}
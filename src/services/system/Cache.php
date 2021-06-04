<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\system;

use EngineCore\base\Service;
use EngineCore\services\System;
use Yii;

/**
 * 系统缓存服务类
 *
 * @property \yii\caching\Cache $component 缓存组件，可读写
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Cache extends Service
{
    
    /**
     * @var System 父级服务类
     */
    public $service;
    
    /**
     * @var string 缓存组件ID
     */
    public $cacheComponentId = 'commonCache';
    
    /**
     * @var \yii\caching\Cache
     */
    private $_cache;
    
    /**
     * 获取缓存组件
     *
     * @return \yii\caching\Cache
     */
    public function getComponent()
    {
        if (null === $this->_cache) {
            // 系统没有配置缓存组件则使用默认配置
            if (!Yii::$app->has($this->cacheComponentId)) {
                $this->setComponent([
                    'class'     => 'yii\caching\FileCache',
                    'cachePath' => '@common/runtime/cache',
                ]);
            }
            
            $this->_cache = Yii::$app->get($this->cacheComponentId);
        }
        
        return $this->_cache;
    }
    
    /**
     * 设置缓存组件
     *
     * @param mixed $definition
     */
    public function setComponent($definition)
    {
        $this->_cache = null;
        Yii::$app->set($this->cacheComponentId, $definition);
    }
    
    /**
     * 存取缓存
     *
     * @param mixed          $key
     * @param mixed          $callable
     * @param int|null|false $duration
     * @param null           $dependency
     *
     * @return mixed
     */
    public function getOrSet($key, $callable, $duration = null, $dependency = null)
    {
        // 当缓存时间为'false'时，先删除再创建数据
        if (false === $duration) {
            $this->getComponent()->delete($key);
        }
        
        return $this->getComponent()->getOrSet($key, $callable, $duration, $dependency);
    }
    
}
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
 * @author E-Kevin <e-kevin@qq.com>
 */
class CacheService extends Service
{
    
    /**
     * @var System 父级服务类
     */
    public $service;
    
    /**
     * @var string 缓存组件
     */
    public $cacheComponent = 'commonCache';
    
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
        /** @var \yii\caching\Cache $component */
        $component = Yii::$app->get($this->cacheComponent);
        if (false === $duration) {
            $component->delete($key);
        }
        
        return $component->getOrSet($key, $callable, $duration, $dependency);
    }
    
    /**
     * 删除缓存
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function delete($key)
    {
        /** @var \yii\caching\Cache $component */
        $component = Yii::$app->get($this->cacheComponent);
        
        return $component->delete($key);
    }
    
}
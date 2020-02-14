<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\Ec;
use EngineCore\services\Extension;
use EngineCore\base\Service;

/**
 * 扩展缓存服务类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Cache extends Service
{
    
    /**
     * @var Extension 父级服务类
     */
    public $service;
    
    /**
     * @var string 扩展缓存组件
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
     *
     */
    public function getOrSet($key, $callable, $duration = null, $dependency = null)
    {
        return $this->cacheService()->getOrSet($key, $callable, $duration, $dependency);
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
        return $this->cacheService()->delete($key);
    }
    
    /**
     * 获取系统缓存服务
     *
     * @return \EngineCore\services\system\CacheService
     */
    protected function cacheService()
    {
        $cacheService = Ec::$service->getSystem()->getCache();
        $cacheService->cacheComponent = $this->cacheComponent;
        
        return $cacheService;
    }
    
    /**
     * @inheritdoc
     * 删除扩展有关的所有缓存信息
     */
    public function clearCache()
    {
        $this->service->getRepository()->getFinder()->clearCache();
        $this->service->getRepository()->clearCache();
        $this->service->getDependent()->clearCache();
    }
    
}
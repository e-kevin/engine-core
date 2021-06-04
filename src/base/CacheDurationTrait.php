<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\base;

/**
 * Class CacheDurationTrait
 *
 * @see    DataCacheInterface
 *
 * @property int|false $cacheDuration 缓存时间间隔
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
trait CacheDurationTrait
{
    
    private $_cacheDuration;
    
    /**
     * 获取缓存时间间隔
     *
     * @return false|int
     */
    public function getCacheDuration()
    {
        if (null === $this->_cacheDuration) {
            $this->setCacheDuration();
        }
        
        return $this->_cacheDuration;
    }
    
    /**
     * 设置缓存时间间隔，默认缓存`一天`
     *
     * @param false|int $cacheDuration 缓存时间间隔，默认缓存`一天`
     *
     * @return self
     */
    public function setCacheDuration($cacheDuration = 86400)
    {
        $this->_cacheDuration = $cacheDuration;
        
        return $this;
    }
    
}
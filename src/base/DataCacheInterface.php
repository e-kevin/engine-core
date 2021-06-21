<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\base;

/**
 * 数据缓存操作接口
 *
 * @property false|int $cacheDuration 缓存时间间隔
 * @property mixed     $all           获取所有数据
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface DataCacheInterface
{
    
    /**
     * 获取所有数据，通常结合缓存使用
     *
     * @return mixed
     */
    public function getAll();
    
    /**
     * 删除缓存
     */
    public function clearCache();
    
    /**
     * 获取缓存时间间隔
     *
     * @return false|int
     */
    public function getCacheDuration();
    
    /**
     * 设置缓存时间间隔，默认缓存`一天`
     *
     * @param false|int $cacheDuration 缓存时间间隔
     *
     * `false`: 禁用缓存
     * 0: 永不过期
     *
     * @return self
     */
    public function setCacheDuration($cacheDuration = 86400);
    
}
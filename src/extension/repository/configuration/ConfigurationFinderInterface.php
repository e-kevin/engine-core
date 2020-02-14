<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension\repository\configuration;

/**
 * 扩展配置文件搜索器接口类
 *
 * @property string $searchFileName
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface ConfigurationFinderInterface
{
    
    /**
     * @var string 缓存所有扩展的配置文件信息
     */
    const CACHE_LOCAL_EXTENSION_CONFIG_FILE = 'local_extension_config_file';
    
    /**
     * 搜索本地目录，获取所有扩展配置文件信息
     *
     * @return array
     */
    public function getConfigFiles();
    
    /**
     * 清除缓存
     *
     * @return mixed
     */
    public function clearCache();
    
    /**
     * 设置需要搜索的配置文件名
     *
     * @param string $name
     *
     * @return
     */
    public function setSearchFileName(string $name);
    
    /**
     * 获取需要搜索的配置文件名
     *
     * @return string
     */
    public function getSearchFileName(): string;
    
}
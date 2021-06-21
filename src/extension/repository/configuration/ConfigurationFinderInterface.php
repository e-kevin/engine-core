<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\configuration;

/**
 * 扩展配置文件搜索器接口
 *
 * @property Configuration[] $configuration
 * @property array           $aliases
 * @property array           $namespaceMap
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface ConfigurationFinderInterface
{
    
    /**
     * @var string 缓存所有扩展的配置数据
     */
    const CACHE_LOCAL_EXTENSION_CONFIGURATION = 'local_extension_configuration';
    
    /**
     * @var string 扩展配置文件里'extra'配置的配置键名
     */
    const EXTENSION_CONFIGURATION_KEY = 'extension-config';
    
    /**
     * 获取扩展配置文件的配置数据
     *
     * @return Configuration[] 返回以扩展名为索引的数组
     */
    public function getConfiguration();
    
    /**
     * 读取指定文件的配置信息
     *
     * @param string $file
     * @param bool   $throwException
     *
     * @return Configuration|null
     */
    public function getConfigurationByFile($file, $throwException = true);
    
    /**
     * 读取已安装的配置文件
     *
     * @param string $file 配置文件
     *
     * @return array
     */
    public function readInstalledFile($file);
    
    /**
     * 获取所有扩展的别名配置信息
     *
     * @return array
     */
    public function getAliases(): array;
    
    /**
     * 获取所有扩展的命名空间与扩展名的映射关系
     *
     * @return array
     */
    public function getNamespaceMap(): array;
    
    /**
     * 清除缓存
     *
     * @return mixed
     */
    public function clearCache();
    
}
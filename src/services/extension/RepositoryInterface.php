<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension;

/**
 * 扩展仓库管理服务接口类，主要管理扩展的本地和数据库数据
 *
 * @property array $localConfiguration
 * @property array $dbConfiguration
 * @property array $installed
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface RepositoryInterface
{
    
    /**
     * @var string 缓存扩展的配置数据
     */
    const CACHE_LOCAL_EXTENSION_CONFIGURATION_PREFIX = 'local_extension_configuration_';
    
    /**
     * 获取本地所有【未安装、已安装】扩展的配置数据，以数据库信息为准
     *
     * @return array
     * ```php
     * [
     *  {uniqueName} => [
     *      'class' => {class}, // 主题扩展不存在该项
     *      'infoInstance' => {infoInstance},
     *      'data' => [], // 数据库配置数据
     *  ],
     * ]
     * ```
     */
    public function getLocalConfiguration(): array;
    
    /**
     * 获取【已安装】的扩展的配置数据
     *
     * @return array
     * ```php
     * [
     *  {uniqueName} => [
     *      'class' => {class}, // 主题扩展不存在该项
     *      'infoInstance' => {infoInstance},
     *      'data' => [], // 数据库配置数据
     *  ],
     * ]
     * ```
     */
    public function getDbConfiguration(): array;
    
    /**
     * 获取【已安装】的扩展的数据库配置数据
     *
     * @return array
     * [
     *  {uniqueName} => [],
     * ]
     */
    public function getInstalled(): array;
    
    /**
     * 获取指定应用【所有|已安装】扩展的配置数据
     *
     * @param bool   $installed
     * @param string $app
     *
     * @return array
     * [
     *  {uniqueName} => [
     *      'class' => {class}, // 主题扩展不存在该项
     *      'infoInstance' => {infoInstance},
     *      'data' => [], // 数据库配置数据
     *  ],
     * ]
     */
    public function getConfigurationByApp($installed = false, $app = null): array;
    
}
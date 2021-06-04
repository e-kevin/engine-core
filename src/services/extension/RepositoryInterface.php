<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\services\extension;

use EngineCore\extension\repository\info\ExtensionInfo;

/**
 * 扩展仓库管理服务接口，主要管理扩展的本地和数据库数据
 *
 * 注意：
 * {uniqueName} = {vendorName} + {extensionName}
 *
 * @property array $localConfiguration
 * @property array $installedConfiguration
 * @property array $dbConfiguration
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface RepositoryInterface
{
    
    /**
     * 获取本地所有【未安装、已安装】扩展的配置数据，以数据库信息为准
     *
     * @return array
     * ```php
     * [
     *  {app} => [
     *      {uniqueName} => {infoInstance}
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
     *  {app} => [
     *      {uniqueName} => {infoInstance}
     *  ],
     * ]
     * ```
     */
    public function getInstalledConfiguration(): array;
    
    /**
     * 获取【已安装】的扩展的数据库配置数据
     *
     * @return array
     * ```php
     * [
     *  {app} => [
     *      {uniqueName} => [
     *      ],
     *  ]
     * ]
     * ```
     */
    public function getDbConfiguration(): array;
    
    /**
     * 获取指定应用【所有|已安装】扩展的配置数据
     *
     * @param bool   $installed
     * @param string $app
     *
     * @return ExtensionInfo[]
     * ```php
     * [
     *  {uniqueName} => {infoInstance},
     * ]
     * ```
     */
    public function getConfigurationByApp($installed = false, $app = null);
    
}
<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\services\menu\components;

use EngineCore\extension\menu\MenuProviderInterface;

/**
 * 配置服务接口
 *
 * @property MenuProviderInterface $provider      菜单数据提供者
 * @property array                 $all           所有菜单数据
 * @property array                 $createdByList 创建方式列表
 * @property array                 $menuConfig    已安装扩展未被格式化的菜单配置数据，即树形菜单数据
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface ConfigServiceInterface
{
    
    /**
     * 获取所有菜单数据
     *
     * @return array
     */
    public function getAll();
    
    /**
     * 获取菜单数据提供者
     *
     * @return MenuProviderInterface
     */
    public function getProvider(): MenuProviderInterface;
    
    /**
     * 设置菜单数据提供器
     *
     * @param string|array|callable $provider
     */
    public function setProvider($provider);
    
    /**
     * 同步扩展菜单数据
     *
     * @return bool
     */
    public function sync(): bool;
    
    /**
     * 获取创建方式列表
     *
     * @return array
     */
    public function getCreatedByList(): array;
    
    /**
     * 初始化菜单
     *
     * @param array  $items    菜单数据
     * @param string $category 菜单分类
     * @param int    $level    菜单层级数，用于定位菜单
     *
     * @return array
     */
    public function initMenu($items, $category, $level): array;
    
    /**
     * 获取已安装扩展未被格式化的菜单配置数据，即树形菜单数据
     *
     * @return array
     */
    public function getMenuConfig(): array;
    
}
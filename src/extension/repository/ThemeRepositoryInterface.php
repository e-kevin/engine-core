<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository;

/**
 * 主题扩展仓库接口类
 *
 * @property string $currentTheme 当前主题名，只读属性
 * @property array  $allActiveTheme 所有激活的主题，只读属性
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface ThemeRepositoryInterface extends CategoryRepositoryInterface
{
    
    /**
     * 获取当前主题名
     *
     * @return string
     */
    public function getCurrentTheme(): string;
    
    /**
     * 获取所有激活的主题
     *
     * 注意：
     * 必须返回以扩展名为索引格式的数组
     *
     * @return array
     * [
     *  {uniqueName} => [],
     * ]
     */
    public function getAllActiveTheme(): array;
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository\models;

/**
 * 主题扩展仓库模型接口类
 *
 * ==== 数据库字段
 * @property string            $theme_id 主题ID
 * ====
 * @property false|null|string $currentUniqueName
 * @property array             $allActiveTheme
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface ThemeModelInterface extends RepositoryModelInterface
{
    
    /**
     * 获取当前应用激活的主题扩展名
     *
     * @return false|null|string
     */
    public function getCurrentUniqueName();
    
    /**
     * 获取所有激活的主题
     *
     * 注意：
     * 必须返回以应用名为索引格式的数组
     *
     * @return array
     * [
     *  {app} => [],
     * ]
     */
    public function getAllActiveTheme(): array;
    
}
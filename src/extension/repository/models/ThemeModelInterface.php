<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\models;

/**
 * 主题扩展仓库模型接口
 *
 * ==== 数据库字段
 * @property string            $theme_id 主题ID
 * ====
 * @property array             $allActiveTheme
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface ThemeModelInterface extends RepositoryModelInterface
{
    
    /**
     * 获取应用激活的主题扩展名
     *
     * @param string $app 应用ID
     *
     * @return false|null|string
     */
    public function getActiveTheme($app = null);
    
    /**
     * 获取所有激活的主题
     *
     * 注意：
     * 必须返回以应用名为索引格式的数组
     *
     * @return array
     * ```
     * [
     *      {app} => [],
     * ]
     * ```
     */
    public function getAllActiveTheme(): array;
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

/**
 * 调度器主题管理器接口
 *
 * @property string $default 默认主题
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface DispatchThemeInterface
{
    
    /**
     * 是否开启主题功能
     *
     * @return bool
     */
    public function isEnableTheme(): bool;
    
    /**
     * 是否开启严谨模式
     *
     * @return bool
     */
    public function isStrict(): bool;
    
    /**
     * 获取默认主题
     *
     * @return string
     * @see NamespaceHelper::normalizeStringForNamespace()
     */
    public function getDefault(): string;
    
    /**
     * 设置主题路径映射
     */
    public function setPathMap();
    
}
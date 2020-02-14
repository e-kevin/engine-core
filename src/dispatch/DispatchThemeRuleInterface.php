<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

/**
 * 调度器主题规则接口类，主要用于判断调度器是否启用了主题功能
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface DispatchThemeRuleInterface
{
    
    /**
     * 是否开启主题功能
     *
     * @return bool
     */
    public function isEnableTheme(): bool;
    
}
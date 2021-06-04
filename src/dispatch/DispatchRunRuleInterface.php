<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

/**
 * 调度器运行模式规则接口
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface DispatchRunRuleInterface
{
    
    /**
     * 是否启用开发者运行模式，只有当前控制器属于系统扩展控制器才生效。
     * 当控制器为开发者控制器或用户自定义控制器时，将禁用开发者运行模式。
     *
     * @return bool
     */
    public function isDeveloperMode(): bool;
    
}
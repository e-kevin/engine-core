<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

/**
 * 调度器配置解析器接口类
 *
 * 主要把各种格式的配置数据转换为调度管理器（DispatchManager）能够理解的数据
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface DispatchConfigParserInterface
{
    
    /**
     * 标准化调度器配置数据
     *
     * @param array $config 调度器配置数据
     *
     * @return array
     */
    public function normalize(array $config): array;
    
}
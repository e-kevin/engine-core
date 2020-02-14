<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;

/**
 * 调度器生成器接口类
 *
 * @property DispatchRunRuleInterface   $runRule 调度器运行模式规则
 * @property DispatchThemeRuleInterface $parser 调度器配置解析器
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface DispatchGeneratorInterface
{
    
    /**
     * 根据当前请求调度器ID的调度器配置，生成所需的调度器
     *
     * @param array $config 当前请求调度器ID的调度器配置
     *
     * @return Dispatch|null
     * @throws InvalidConfigException
     */
    public function createDispatch(array $config);
    
    
    /**
     * 获取调度器配置解析器
     *
     * @return DispatchConfigParserInterface
     */
    public function getParser();
    
    /**
     * 设置调度器配置解析器
     *
     * @param string|array|callable $parser
     *
     * @throws InvalidConfigException
     */
    public function setParser($parser);
    
    /**
     * 获取调度器运行模式规则
     *
     * @return DispatchRunRuleInterface
     */
    public function getRunRule();
    
    /**
     * 设置调度器运行模式规则
     *
     * @param string|array|callable $runRule
     *
     * @throws InvalidConfigException
     */
    public function setRunRule($runRule);
    
    /**
     * 调度器是否支持视图渲染功能
     *
     * @param bool $throwException 是否抛出异常
     *
     * @return bool
     * @throws NotSupportedException 不支持视图渲染功能则根据[[$throwException]]判断是否抛出异常
     */
    public function isSupportRender($throwException = false): bool;
    
}
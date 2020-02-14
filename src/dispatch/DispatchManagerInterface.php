<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

use yii\base\InvalidConfigException;
use yii\base\InvalidRouteException;
use yii\base\NotSupportedException;

/**
 * 系统调度功能（Dispatch）管理接口类
 *
 * 系统的调度功能由实现该接口的调度管理类提供支持
 *
 * 约定：
 *  - 系统扩展控制器，位于项目内'@extensions'目录下的控制器
 *  - 开发者控制器，位于项目内'@developer'目录下的控制器
 *  - 用户自定义控制器，位于项目内任何地方，如'@backend/controllers'、'@frontend/controllers'目录下的控制器
 * @see \EngineCore\dispatch\RunRule 简述了解更多有关‘调度器运行模式规则’
 *
 * @property array                                                     $config 全局调度器配置
 * @property array                                                     $controllerDispatchMap 当前控制器的调度器配置
 * @property \EngineCore\web\Controller|\EngineCore\console\Controller $controller 当前控制器的调度器配置
 * @property array|null                                                $currentDispatchMap 当前调度器配置信息
 * @property string                                                    $requestDispatchId 当前控制器请求的调度器ID
 * @property DispatchGeneratorInterface                                $generator 调度器生成器
 * @property DispatchThemeRuleInterface                                $themeRule 调度器主题规则
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface DispatchManagerInterface
{
    
    /**
     * 调用该管理器的控制器
     *
     * @return \EngineCore\console\Controller|\EngineCore\web\Controller
     */
    public function getController();
    
    /**
     * 获取当前控制器的调度器配置
     *
     * 调度管理类的全局调度配置数据优先级最高，优先级由高向低：
     * 'DispatchManager::$config' > 'Controller::$dispatchMap' > 'Controller::$defaultDispatchMap'
     *
     * @return array
     */
    public function getControllerDispatchMap(): array;
    
    /**
     * 获取当前调度器配置信息
     *
     * @return array|null
     */
    public function getCurrentDispatchMap();
    
    /**
     * 获取全局调度器配置
     *
     * @return array
     */
    public function getConfig(): array;
    
    /**
     * 设置全局调度器配置
     *
     * @param array $config 调度器配置数据
     *
     * @return array
     */
    public function setConfig(array $config): array;
    
    /**
     * 根据路由地址获取调度器
     *
     * @param string $route 调度路由，支持以下格式：'view', 'config-manager/view'，'system/config-manager/view'
     *
     * @return null|Dispatch
     * @throws InvalidRouteException 当调度路由无法获取到调度时，抛出异常
     * @throws NotSupportedException 当所调用的调度器所属控制器不支持调度管理功能，则抛出异常
     */
    public function getDispatch($route);
    
    /**
     * 创建调度器
     *
     * @param string $id 调度器ID
     *
     * @return null|Dispatch
     */
    public function createDispatch($id);
    
    /**
     * 获取当前控制器请求的调度器ID
     *
     * @return string
     */
    public function getRequestDispatchId();
    
    /**
     * 重置当前控制器请求的调度器ID
     */
    public function resetRequestDispatchId();
    
    /**
     * 获取调度器生成器
     *
     * @return DispatchGeneratorInterface
     */
    public function getGenerator();
    
    /**
     * 设置调度器生成器
     *
     * @param string|array|callable $generator
     *
     * @throws InvalidConfigException
     */
    public function setGenerator($generator);
    
    /**
     * 获取调度器主题规则
     *
     * @return DispatchThemeRuleInterface
     */
    public function getThemeRule();
    
    /**
     * 设置调度器主题规则
     *
     * @param string|array|callable $themeRule
     *
     * @throws InvalidConfigException
     */
    public function setThemeRule($themeRule);
    
}
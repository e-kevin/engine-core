<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

namespace EngineCore\dispatch;

use EngineCore\Ec;
use Yii;
use yii\base\BaseObject;
use yii\base\Controller;
use yii\base\InvalidConfigException;
use yii\base\InvalidRouteException;
use yii\base\NotSupportedException;
use yii\web\Application;

/**
 * 系统调度功能（Dispatch）管理抽象类
 *
 * 系统的调度功能由调度管理类负责管理和调度所需调度器
 *
 * 约定：
 *  - 系统扩展控制器，位于项目内'@extensions'目录下的控制器
 *  - 开发者控制器，位于项目内'@developer'目录下的控制器
 *  - 用户自定义控制器，位于项目内任何地方，如'@backend/controllers'、'@frontend/controllers'目录下的控制器
 * @see    \EngineCore\dispatch\RunRule 简述了解更多有关‘调度器运行模式规则’
 *
 * @property array                                                     $config                全局调度器配置
 * @property array                                                     $controllerDispatchMap 当前控制器的调度器配置
 * @property \EngineCore\web\Controller|\EngineCore\console\Controller $controller            当前控制器
 * @property array|null                                                $currentDispatchMap    当前调度器配置信息
 * @property string                                                    $requestDispatchId     当前控制器请求的调度器ID
 * @property DispatchGeneratorInterface                                $generator             调度器生成器
 * @property DispatchThemeInterface                                    $theme                 调度器主题管理器
 * @property DispatchThemeInterface                                    $parser                调度器配置解析器
 * @property DispatchRunRuleInterface                                  $runRule               调度器运行模式规则
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
abstract class AbstractDispatchManager extends BaseObject
{
    
    /**
     * @var bool 开启主题功能
     * @see \EngineCore\dispatch\Theme::isEnableTheme() 查看优先级
     */
    public $enableTheme;
    
    /**
     * @var bool 开启主题严谨模式
     * @see \EngineCore\dispatch\Theme::isStrict() 查看优先级
     */
    public $strict;
    
    /**
     * 运行模式，可选值有：
     *  - 0: 运行系统扩展，运行在'@extensions'目录下的扩展
     * @see \EngineCore\extension\repository\info\ExtensionInfo::RUN_MODULE_EXTENSION
     *  - 1: 运行开发者扩展，运行在'@developer'目录下的扩展
     * @see \EngineCore\extension\repository\info\ExtensionInfo::RUN_MODULE_DEVELOPER
     *
     * @see \EngineCore\dispatch\RunRule::isDeveloperMode() 查看优先级
     *
     * @var int
     */
    public $run;
    
    /**
     * @param Controller $controller 调用该管理器的控制器类
     * @param array      $config
     */
    public function __construct(Controller $controller, array $config = [])
    {
        $this->_controller = $controller;
        parent::__construct($config);
    }
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        Yii::configure($this->controller, $this->config[$this->controller->getUniqueId()] ?? []);
    }
    
    /**
     * 调用该管理器的控制器
     *
     * @var \EngineCore\web\Controller|\EngineCore\console\Controller
     */
    private $_controller;
    
    /**
     * 调用该管理器的控制器
     *
     * @return \EngineCore\console\Controller|\EngineCore\web\Controller
     */
    final public function getController()
    {
        return $this->_controller;
    }
    
    /**
     * 获取当前控制器的调度器配置
     *
     * 调度管理类的全局调度配置数据优先级最高，优先级由高向低：
     * 'DispatchManager::$config' > 'Controller::$dispatchMap' > 'Controller::$defaultDispatchMap'
     *
     * @return array
     */
    abstract public function getControllerDispatchMap(): array;
    
    /**
     * 获取当前调度器配置信息
     *
     * @return array|null
     */
    public function getCurrentDispatchMap()
    {
        // 开启主题功能，则优先获取主题调度配置
        if ($this->getTheme()->isEnableTheme()) {
            $themeName = '@' . Ec::$service->getExtension()->getThemeRepository()->getConfig('name');
            $config = $this->controllerDispatchMap[$themeName][$this->getRequestDispatchId()] ?? null;
            if (null !== $config) {
                return $config;
            }
        }
        
        return $this->controllerDispatchMap[$this->getRequestDispatchId()] ?? null;
    }
    
    /**
     * 获取全局调度器配置
     *
     * @return array
     */
    abstract public function getConfig(): array;
    
    /**
     * 设置全局调度器配置
     *
     * @param array $config 调度器配置数据
     *
     * @return array
     */
    abstract public function setConfig(array $config): array;
    
    /**
     * 根据路由地址获取调度器
     *
     * @param string $route 调度路由，支持以下格式：'view', 'config-manager/view'，'system/config-manager/view'
     *
     * @return null|Dispatch
     * @throws InvalidRouteException 当调度路由无法获取到调度时，抛出异常
     * @throws NotSupportedException 当所调用的调度器所属控制器不支持调度管理功能，则抛出异常
     */
    abstract public function getDispatch($route);
    
    /**
     * 创建调度器
     *
     * @param string $id 调度器ID
     *
     * @return null|Dispatch
     */
    public function createDispatch($id)
    {
        if ('' === $id) {
            $id = $this->getController()->defaultAction;
        }
        
        $this->_requestDispatchId = $id;
        
        // 不存在调度配置信息则终止调度行为
        if (null === ($config = $this->getCurrentDispatchMap())) {
            $this->resetRequestDispatchId();
            
            return null;
        }
        
        // 生成调度器
        if (null !== $dispatch = $this->getGenerator()->createDispatch($config)) {
            if ($this->isSupportRender()) {
                // 设置主题路径映射
                $this->getTheme()->setPathMap();
            }
        }
        
        $this->resetRequestDispatchId();
        
        return $dispatch;
    }
    
    /**
     * @var string 当前控制器请求的调度器ID
     */
    private $_requestDispatchId;
    
    /**
     * 获取当前控制器请求的调度器ID
     *
     * @return string
     */
    final public function getRequestDispatchId()
    {
        return $this->_requestDispatchId;
    }
    
    /**
     * 重置当前控制器请求的调度器ID
     */
    final public function resetRequestDispatchId()
    {
        $this->_requestDispatchId = null;
    }
    
    /**
     * @var Generator 调度器生成器
     */
    private $_generator;
    
    /**
     * 获取调度器生成器
     *
     * @return DispatchGeneratorInterface
     */
    final public function getGenerator()
    {
        if (null === $this->_generator) {
            $this->setGenerator(Generator::class);
        }
        
        return $this->_generator;
    }
    
    /**
     * 设置调度器生成器
     *
     * @param string|array|callable $generator
     *
     * @throws InvalidConfigException
     */
    final public function setGenerator($generator)
    {
        $this->_generator = Ec::createObject($generator, [$this], DispatchGeneratorInterface::class);
    }
    
    /**
     * @var Theme 调度器主题管理器
     */
    private $_theme;
    
    /**
     * 获取调度器主题
     *
     * @return DispatchThemeInterface
     */
    final public function getTheme()
    {
        if (null === $this->_theme) {
            $this->setTheme(Theme::class);
        }
        
        return $this->_theme;
    }
    
    /**
     * 设置调度器主题管理器
     *
     * @param string|array|callable $themeRule
     *
     * @throws InvalidConfigException
     */
    final public function setTheme($themeRule)
    {
        $this->_theme = Ec::createObject($themeRule, [$this], DispatchThemeInterface::class);
    }
    
    /**
     * @var DispatchConfigParserInterface 调度器配置解析器
     */
    private $_parser;
    
    /**
     * 获取调度器配置解析器
     *
     * @return DispatchConfigParserInterface
     */
    final public function getParser()
    {
        if (null === $this->_parser) {
            $this->setParser(SimpleParser::class);
        }
        
        return $this->_parser;
    }
    
    /**
     * 设置调度器配置解析器
     *
     * @param string|array|callable $parser
     *
     * @throws InvalidConfigException
     */
    final public function setParser($parser)
    {
        $this->_parser = Ec::createObject($parser, [$this], DispatchConfigParserInterface::class);
    }
    
    /**
     * @var RunRule 调度器运行模式规则
     */
    private $_runRule;
    
    /**
     * 获取调度器运行模式规则
     *
     * @return DispatchRunRuleInterface
     */
    final public function getRunRule()
    {
        if (null === $this->_runRule) {
            $this->setRunRule(RunRule::class);
        }
        
        return $this->_runRule;
    }
    
    /**
     * 设置调度器运行模式规则
     *
     * @param string|array|callable $runRule
     *
     * @throws InvalidConfigException
     */
    final public function setRunRule($runRule)
    {
        $this->_runRule = Ec::createObject($runRule, [$this], DispatchRunRuleInterface::class);
    }
    
    /**
     * 调度器是否支持视图渲染功能
     *
     * @param bool $throwException 是否抛出异常
     *
     * @return bool
     * @throws NotSupportedException 不支持视图渲染功能则根据`$throwException`判断是否抛出异常
     */
    public function isSupportRender($throwException = false): bool
    {
        if (!Yii::$app instanceof Application) {
            if ($throwException) {
                throw new NotSupportedException('The current application does not support the dispatch response render function.');
            } else {
                return false;
            }
        }
        
        return true;
    }
    
}
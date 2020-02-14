<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

use EngineCore\Ec;
use Yii;
use yii\base\BaseObject;
use yii\base\Controller;

/**
 * 系统调度功能（Dispatch）管理抽象类
 *
 * 系统的调度功能由调度管理类负责管理和调度所需调度器
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
abstract class BaseDispatchManager extends BaseObject implements DispatchManagerInterface
{
    
    /**
     * 调用该管理器的控制器
     *
     * @var \EngineCore\web\Controller|\EngineCore\console\Controller
     */
    private $_controller;
    
    /**
     * @var bool 开启主题功能
     * @see \EngineCore\dispatch\ThemeRule::isEnableTheme() 查看优先级
     */
    public $enableTheme;
    
    /**
     * 获取不到调度器时，将在调度器目录内的{$defaultThemeName}目录下查找，该功能仅在{{$enableTheme}}开启后生效。
     *
     * @var string 默认主题名
     */
    public $defaultThemeName = 'bootstrap-v3';
    
    /**
     * 运行模式，可选值有：
     *  - 0: 运行系统扩展，运行在'@extensions'目录下的扩展
     * @see \EngineCore\extension\ExtensionInfo::RUN_MODULE_EXTENSION
     *  - 1: 运行开发者扩展，运行在'@developer'目录下的扩展
     * @see \EngineCore\extension\ExtensionInfo::RUN_MODULE_DEVELOPER
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
     * @inheritdoc
     */
    public function init()
    {
        Yii::configure($this->controller, $this->config[$this->controller->getUniqueId()] ?? []);
    }
    
    /**
     * @inheritdoc
     */
    final public function getController()
    {
        return $this->_controller;
    }
    
    /**
     * @inheritdoc
     */
    abstract public function getControllerDispatchMap(): array;
    
    /**
     * @inheritdoc
     */
    final public function getCurrentDispatchMap()
    {
        return $this->controllerDispatchMap[$this->getRequestDispatchId()] ?? null;
    }
    
    /**
     * @inheritdoc
     */
    abstract public function getConfig(): array;
    
    /**
     * @inheritdoc
     */
    abstract public function setConfig(array $config): array;
    
    /**
     * @inheritdoc
     */
    abstract public function getDispatch($route);
    
    /**
     * @inheritdoc
     */
    final public function createDispatch($id)
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
        $dispatch = $this->getGenerator()->createDispatch($config);
        
        $this->resetRequestDispatchId();
        
        return $dispatch;
    }
    
    /**
     * @var string 当前控制器请求的调度器ID
     */
    private $_requestDispatchId;
    
    /**
     * @inheritdoc
     */
    final public function getRequestDispatchId()
    {
        return $this->_requestDispatchId;
    }
    
    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    final public function getGenerator()
    {
        if (null === $this->_generator) {
            $this->setGenerator(Generator::class);
        }
        
        return $this->_generator;
    }
    
    /**
     * @inheritdoc
     */
    final public function setGenerator($generator)
    {
        $this->_generator = Ec::createObject($generator, [$this], DispatchGeneratorInterface::class);
    }
    
    /**
     * @var ThemeRule 调度器主题规则
     */
    private $_themeRule;
    
    /**
     * @inheritdoc
     */
    final public function getThemeRule()
    {
        if (null === $this->_themeRule) {
            $this->setThemeRule(ThemeRule::class);
        }
        
        return $this->_themeRule;
    }
    
    /**
     * @inheritdoc
     */
    final public function setThemeRule($themeRule)
    {
        $this->_themeRule = Ec::createObject($themeRule, [$this], DispatchThemeRuleInterface::class);
    }
    
}
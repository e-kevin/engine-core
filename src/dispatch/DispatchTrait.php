<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

use EngineCore\base\Modularity;
use EngineCore\Ec;
use EngineCore\extension\entity\ExtensionEntityInterface;
use Yii;
use yii\base\InvalidConfigException;

/**
 * 让Controller控制器类支持系统（Dispatch）调度功能
 *
 * @property Modularity                                   $module
 * @property Dispatch                                     $action
 * @property \EngineCore\dispatch\AbstractDispatchManager $dispatchManager    调度器管理器
 * @property array                                        $defaultDispatchMap 默认调度器配置
 * @property ExtensionEntityInterface                     $extension          当前控制器所属的扩展信息
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
trait DispatchTrait
{
    
    /**
     * @var int 运行模式
     * @see \EngineCore\dispatch\DispatchManager::$run
     */
    public $run;
    
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
     * @var array 调度器配置，该配置会覆盖默认调度器配置[[$defaultDispatchMap]]，二次开发时可配置该值更改默认调度器配置
     *
     * @see \EngineCore\dispatch\DispatchManager::getControllerDispatchMap() 查看配置优先级
     * @see \EngineCore\dispatch\SimpleParser::normalizeArrayConfig() 配置格式参考
     * @see \EngineCore\dispatch\SimpleParser::normalizeStringConfig() 配置格式参考
     */
    public $dispatchMap;
    
    /**
     * @var array 默认调度器配置
     * @see \EngineCore\dispatch\DispatchManager::getControllerDispatchMap() 查看配置优先级
     * @see \EngineCore\dispatch\SimpleParser::normalizeArrayConfig() 配置格式参考
     * @see \EngineCore\dispatch\SimpleParser::normalizeStringConfig() 配置格式参考
     */
    protected $defaultDispatchMap = [];
    
    /**
     * 获取默认调度器配置
     *
     * @return array
     */
    public function getDefaultDispatchMap()
    {
        return $this->defaultDispatchMap ?: [];
    }
    
    /**
     * {@inheritdoc}
     * @return null|\yii\base\Action|Dispatch
     */
    public function createAction($id)
    {
        return parent::createAction($id) ?: $this->getDispatchManager()->createDispatch($id);
    }
    
    /**
     * @var DispatchManager
     */
    private $_dispatchManager;
    
    /**
     * 获取调度管理器
     *
     * @return DispatchManager
     * @throws InvalidConfigException
     */
    public function getDispatchManager()
    {
        if (null === $this->_dispatchManager) {
            $this->_dispatchManager = Ec::createObject($this->dispatchManagerDefinition(), [$this], AbstractDispatchManager::class);
        }
        
        return $this->_dispatchManager;
    }
    
    /**
     * 调度管理器默认配置
     *
     * @return string|array|callable
     */
    private function dispatchManagerDefinition()
    {
        // 存在自定义调度管理器则优先获取该调度管理器，否则使用系统自带的默认调度管理器
        if (Yii::$container->has('DispatchManager')) {
            $definition = Yii::$container->definitions['DispatchManager'];
        } else {
            $definition['class'] = DispatchManager::class;
        }
        
        return $definition;
    }
    
    /**
     * @var ExtensionEntityInterface 当前控制器所属的扩展信息
     */
    private $_runningExtension;
    
    /**
     * 获取当前控制器所属的扩展信息
     *
     * @return ExtensionEntityInterface
     */
    public function getExtension()
    {
        if (null === $this->_runningExtension) {
            $this->_runningExtension = Ec::$service->getExtension()->entity($this);
        }
        
        return $this->_runningExtension;
    }
    
}
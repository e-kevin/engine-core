<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension;

use EngineCore\Ec;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\helpers\ArrayHelper;
use EngineCore\helpers\NamespaceHelper;
use Yii;
use yii\base\BaseObject;
use yii\base\Controller;

/**
 * 当前控制器所属的扩展抽象类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
abstract class BaseRunningExtension extends BaseObject implements RunningExtensionInterface
{
    
    /**
     * 当前运行的控制器
     *
     * @var \EngineCore\web\Controller|\EngineCore\console\Controller|\yii\base\Controller
     */
    protected $controller;
    
    /**
     * @var string 当前控制器所属扩展的根命名空间
     */
    protected $_namespace;
    
    /**
     * @var string 是否为扩展路径
     *
     * 判断符合以下其中一条即可：
     *  - 控制器位于'@extensions'目录下
     *  - 控制器别名配置的键值位于'@extensions'目录下
     */
    private $_isExtensionPath;
    
    /**
     * {@inheritdoc}
     */
    public function __construct(Controller $controller, array $config = [])
    {
        $this->controller = $controller;
        parent::__construct($config);
    }
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $refController = new \ReflectionClass($this->controller);
        $aliases = NamespaceHelper::getAliasesKeyByNamespace($refController->getNamespaceName());
        $this->_namespace = NamespaceHelper::aliases2Namespace($aliases);
        $this->_isExtensionPath = strpos($refController->getFileName(), Yii::getAlias('@extensions')) !== false;
        if (!$this->_isExtensionPath) {
            $this->_isExtensionPath = strpos($refController->getFileName(), Yii::getAlias($aliases)) !== false;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function getNamespace(): string
    {
        return $this->_namespace;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isExtensionController(): bool
    {
        return $this->_isExtensionPath;
    }
    
    /**
     * {@inheritdoc}
     */
    abstract public function getInfo();
    
    /**
     * {@inheritdoc}
     */
    public function loadConfig()
    {
        $arr = [];
        $config = Ec::$service->getExtension()->getEnvironment()->getConfig($this->getInfo());
        foreach ($config as $app => $row) {
            $arr = ArrayHelper::merge($arr, $row);
        }
        
        foreach ($arr as $type => $config) {
            foreach ($config as $id => $cfg) {
                switch ($type) {
                    case 'modules':
                        if (!$this->controller->module->hasModule($id)) {
                            $this->controller->module->setModule($id, $cfg);
                        }
                        break;
                    case 'components':
                        if (!$this->controller->module->has($id)) {
                            $this->controller->module->set($id, $cfg);
                        }
                        break;
                    case 'params':
                        Yii::$app->params = ArrayHelper::merge(Yii::$app->params, $cfg);
                        break;
                }
            }
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDbConfig(): array
    {
        return [
            'run' => ExtensionInfo::RUN_MODULE_EXTENSION,
        ];
    }
    
    /**
     * {@inheritdoc}
     * 默认为EngineCore核心构架扩展
     */
    public function defaultExtension()
    {
        return new EngineCoreExtension($this->controller);
    }
    
}
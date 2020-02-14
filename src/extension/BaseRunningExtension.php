<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension;

use EngineCore\Ec;
use EngineCore\helpers\NamespaceHelper;
use Yii;
use yii\base\BaseObject;
use yii\base\Controller;
use yii\helpers\Json;

/**
 * 当前控制器所属的扩展基础类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
abstract class BaseRunningExtension extends BaseObject implements RunningExtensionInterface
{
    
    /**
     * 调用该类的控制器
     *
     * @var \EngineCore\web\Controller|\EngineCore\console\Controller|\yii\base\Controller
     */
    protected $controller;
    
    /**
     * @var string 当前控制器所属模块的命名空间
     */
    protected $_namespace;
    
    /**
     * @inheritdoc
     */
    public function __construct(Controller $controller, array $config = [])
    {
        $this->controller = $controller;
        parent::__construct($config);
    }
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        $aliases = NamespaceHelper::getAliasesKeyByNamespace((new \ReflectionClass($this->controller))->getNamespaceName());
        $this->_namespace = NamespaceHelper::aliases2Namespace($aliases);
    }
    
    /**
     * @inheritdoc
     */
    final public function getNamespace(): string
    {
        return $this->_namespace;
    }
    
    /**
     * @inheritdoc
     */
    final public function isExtensionController(): bool
    {
        return strpos($this->_namespace, 'extensions') === 0;
    }
    
    /**
     * @inheritdoc
     */
    abstract public function getInfo();
    
    /**
     * @inheritdoc
     */
    abstract public function getDbConfig(): array;
    
    /**
     * @inheritdoc
     */
    abstract public function getExtensionUniqueName(): string;
    
    /**
     * @inheritdoc
     * 默认为EngineCore核心构架扩展
     */
    public function defaultExtension()
    {
        $info = Json::decode(file_get_contents(Yii::getAlias('@EngineCore/composer.json')));
        
        return Yii::createObject([
            'class' => EngineCoreInfo::class,
            'app' => 'EngineCore',
            'id' => 'EngineCore',
        ], [
            'vendor/' . $info['name'],
            'vendor/' . $info['name'],
            $info['version'] ?? Ec::getVersion(),
        ]);
    }
    
}
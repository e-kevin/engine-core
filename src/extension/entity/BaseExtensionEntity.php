<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\entity;

use EngineCore\Ec;
use EngineCore\EngineCoreExtension;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\helpers\ArrayHelper;
use EngineCore\helpers\NamespaceHelper;
use Yii;
use yii\base\BaseObject;
use yii\base\Controller;
use yii\base\Module;

/**
 * 获取指定对象所属的扩展实体抽象类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
abstract class BaseExtensionEntity extends BaseObject implements ExtensionEntityInterface
{
    
    /**
     * @var Controller|Module|object 被检测的对象
     */
    protected $object;
    
    /**
     * @var string 对象所属扩展的根命名空间
     */
    protected $_namespace;
    
    /**
     * 对象是否属于系统扩展
     *
     * 判断符合以下其中一条即可：
     *  - 对象位于'@extensions'目录下
     *  - 对象别名配置的键值位于'@extensions'目录下
     *
     * 约定：
     *  - 系统扩展，位于项目内'@extensions'目录下的扩展
     *  - 开发者扩展，位于项目内'@developer'目录下的扩展
     *  - 用户自定义扩展，位于项目内任何地方，如'@backend/extensions'、'@frontend/extensions'目录下的扩展
     *
     * @var bool
     */
    private $_isSystemExtension;
    
    /**
     * {@inheritdoc}
     */
    public function __construct($object, array $config = [])
    {
        $this->object = $object;
        parent::__construct($config);
    }
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $ref = new \ReflectionClass($this->object);
        $aliases = NamespaceHelper::getAliasesKeyByNamespace($ref->getNamespaceName());
        $this->_namespace = NamespaceHelper::aliases2Namespace($aliases);
        $this->_isSystemExtension = strpos($ref->getFileName(), Yii::getAlias('@extensions')) !== false;
        if (!$this->_isSystemExtension) {
            $this->_isSystemExtension = strpos($ref->getFileName(), Yii::getAlias($aliases)) !== false;
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
    public function isSystemExtension(): bool
    {
        return $this->_isSystemExtension;
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
        $config = Ec::$service->getExtension()->getEnvironment()->getConfig($this->getInfo());
        if (empty($config)) {
            return;
        }

        // 合并不同应用的配置数据
        $arr = [];
        foreach ($config as $app => $row) {
            $arr = ArrayHelper::merge($arr, $row);
        }
        
        $isController = $this->object instanceOf Controller;
        $isModule = $this->object instanceOf Module;

        foreach ($arr as $type => $config) {
            if ('params' === $type) {
                Yii::$app->params = ArrayHelper::merge($config, Yii::$app->params);
            } else {
                foreach ($config as $id => $cfg) {
                    switch ($type) {
                        case 'controllerMap':
                            if (!isset(Yii::$app->controllerMap[$id])) {
                                Yii::$app->controllerMap[$id] = $cfg;
                            } else {
                                if ($isController) {
                                    if ($this->object->id === $id) {
                                        unset($cfg['class']);
                                        Yii::configure($this->object, $cfg);
                                    } else {
                                        Yii::$app->controllerMap[$id] = ArrayHelper::merge($cfg, Yii::$app->controllerMap[$id]);
                                    }
                                }
                            }
                            break;
                        case 'modules':
                            if (!Yii::$app->hasModule($id)) {
                                Yii::$app->setModule($id, $cfg);
                            } else {
                                if ($isModule) {
                                    if ($id === $this->object->getUniqueId()) {
                                        $controllerMap = ArrayHelper::remove($cfg, 'controllerMap', []);
                                        $controllerMap = ArrayHelper::merge($controllerMap, $this->object->controllerMap);
                                        unset($cfg['class']);
                                        Yii::configure($this->object, $cfg);
                                        if (!empty($controllerMap)) {
                                            Yii::configure($this->object, [
                                                'controllerMap' => ArrayHelper::merge($cfg, $controllerMap),
                                            ]);
                                        }
                                    } else {
                                        Yii::$app->setModule($id, ArrayHelper::merge($cfg, Yii::$app->getModules()[$id]));
                                    }
                                } elseif ($isController) {
                                    if (null !== $module = Yii::$app->getModule($id, false)) {
                                        $controllerMap = ArrayHelper::remove($cfg, 'controllerMap', []);
                                        $controllerMap = ArrayHelper::merge($controllerMap, $module->controllerMap);
                                        unset($cfg['class']);
                                        Yii::configure($module, $cfg);
                                        if (!empty($controllerMap)) {
                                            if ($isController) {
                                                if (isset($controllerMap[$this->object->id])) {
                                                    unset($controllerMap[$this->object->id]['class']);
                                                    Yii::configure($this->object, $controllerMap[$this->object->id]);
                                                    unset($controllerMap[$this->object->id]);
                                                } else {
                                                    Yii::configure($module, [
                                                        'controllerMap' => ArrayHelper::merge($cfg, $controllerMap),
                                                    ]);
                                                }
                                            }
                                        }
                                    } else {
                                        Yii::$app->setModule($id, ArrayHelper::merge($cfg, Yii::$app->getModules()[$id]));
                                    }
                                }
                            }
                            break;
                        case 'components':
                            if (!Yii::$app->has($id)) {
                                Yii::$app->set($id, $cfg);
                            } // 加载翻译文件配置
                            elseif ('i18n' === $id) {
                                Yii::$app->getI18n()->translations = ArrayHelper::merge(
                                    $cfg['translations'],
                                    Yii::$app->getI18n()->translations
                                );
                            }
                            break;
                        case 'container':
                            Yii::$container->setDefinitions(ArrayHelper::merge($cfg, Yii::$container->getDefinitions()));
                            break;
                    }
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
        return new EngineCoreExtension($this->object);
    }
    
}
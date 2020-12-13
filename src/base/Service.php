<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\base;

use Closure;
use EngineCore\helpers\ArrayHelper;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * 系统服务类
 *
 * @property string    $uniqueId      服务ID
 * @property bool      $status        服务执行后的状态
 * @property mixed     $info          服务执行后的相关信息
 * @property mixed     $data          服务执行后的相关数据
 * @property array     $result        服务执行后的结果数据
 * @property int|false $cacheDuration 缓存时间间隔
 * @property Service[] $services      服务配置或已经实例化的服务实例列表数据
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Service extends Component
{
    
    /**
     * @var string 服务ID
     */
    private $_uniqueId;
    
    /**
     * @var Service|null 当前服务的父级服务，默认为null，即当前服务为顶级服务
     */
    public $service;
    
    /**
     * @var Service[] 已经实例化的服务
     */
    private $_services = [];
    
    /**
     * @var array 服务配置数据
     */
    private $_definitions = [];
    
    /**
     * @var boolean 是否禁用服务功能，默认不禁用
     */
    public $disabled = false;
    
    /**
     * @var mixed 服务执行后的相关信息
     */
    protected $_info = '';
    
    /**
     * @var mixed 服务执行后的相关数据
     */
    protected $_data = '';
    
    /**
     * @var boolean 服务执行后的状态结果，默认为false，表示执行失败
     */
    protected $_status = false;
    
    /**
     * @var array 必须设置的属性值
     */
    protected $mustBeSetProps = [];
    
    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        // 补全服务配置信息
        foreach ($this->coreServices() as $id => $service) {
            if (!isset($config['services'][$id])) {
                $config['services'][$id] = $service;
            } elseif (is_array($config['services'][$id]) && !isset($config['services'][$id]['class'])) {
                $config['services'][$id] = ArrayHelper::merge($service, $config['services'][$id]);
            }
        }
        parent::__construct($config);
    }
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        foreach ($this->mustBeSetProps as $prop) {
            if ($this->{$prop} === null) {
                throw new InvalidConfigException(get_called_class() . ': The `$' . $prop . '` property must be set.');
            }
        }
    }
    
    /**
     * 获取服务ID，不带后缀`Service`
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function getUniqueId()
    {
        // 设置服务ID，便于调试并限制服务类仅能通过服务定位器来调用其子服务类
        if (null === $this->_uniqueId) {
            throw new InvalidConfigException(get_called_class() . ': The `$uniqueId` property must be set or ' .
                'use the `' . ServiceLocator::class . '` class or its derived class to call the service component.');
        }
        
        return $this->service ? $this->service->getUniqueId() . '/' . $this->_uniqueId : $this->_uniqueId;
    }
    
    /**
     * 设置服务ID
     *
     * @param string $id 服务ID，不带后缀`Service`
     */
    public function setUniqueId($id)
    {
        $this->_uniqueId = $id;
    }
    
    /**
     * 核心服务配置
     *
     * @return array
     */
    public function coreServices()
    {
        return [];
    }
    
    /**
     * 从本地移除指定服务ID的所有相关信息，包括服务配置和服务实例
     *
     * @param string $id 服务ID
     */
    public function clearService($id)
    {
        unset($this->_definitions[$id], $this->_services[$id]);
    }
    
    /**
     * 获取服务配置或已经实例化的服务实例列表数据
     *
     * @param bool $returnDefinitions 是否返回服务配置，默认只返回服务配置定义列表
     *
     * @return array|Service[]
     */
    public function getServices($returnDefinitions = true)
    {
        return $returnDefinitions ? $this->_definitions : $this->_services;
    }
    
    /**
     * 批量注册服务
     *
     * ```php
     * [
     *     'local' => [
     *         'class' => '{namespace}\{className}',
     *     ],
     *     'cache' => [
     *         'class' => 'namespace}\{className}',
     *     ],
     * ]
     * ```
     *
     * @param array $services 服务配置数据
     *
     */
    public function setServices($services)
    {
        foreach ($services as $id => $service) {
            $this->setService($id, $service);
        }
    }
    
    /**
     * 检索指定服务ID是否存在服务配置或服务实例
     *
     * @param string $id 服务ID
     * @param bool   $checkInstance 是否从已经实例化的服务列表里检索，默认为'false'，只检索是否存在服务配置
     *
     * @return bool
     * @see setService()
     */
    public function hasService($id, $checkInstance = false)
    {
        return $checkInstance ? isset($this->_services[$id]) : isset($this->_definitions[$id]);
    }
    
    /**
     * 获取指定服务ID的实例
     *
     * @param string $id 服务ID
     * @param bool   $throwException 是否抛出异常，默认为'true'，抛出异常
     *
     * @return Service|null
     * @throws InvalidConfigException 如果服务ID没有被注册，则抛出异常
     * @see hasService()
     * @see setService()
     */
    public function getService($id, $throwException = true)
    {
        if (isset($this->_services[$id])) {
            return $this->_services[$id];
        }
        
        if (isset($this->_definitions[$id])) {
            $definition = $this->_definitions[$id];
            if (is_object($definition) && !$definition instanceof Closure) {
                $service = $definition;
            } else {
                $service = Yii::createObject($definition);
            }
            if (!$service instanceof Service) {
                throw new InvalidConfigException(get_called_class() . ": The required sub service component `{$id}` must return an object extends `" . Service::class . '`.');
            }
            
            // 设置父级服务类
            $service->service = $this;
            // 设置服务ID，子服务必须设置服务ID，便于调试
            $service->setUniqueId($id);
            $uniqueId = $this->getUniqueId() . '/' . $id;
            
            Yii::debug('Loading sub service: ' . $uniqueId, __METHOD__);
            
            return $this->_services[$id] = $service;
        } elseif ($throwException) {
            throw new InvalidConfigException("The Service:`{$this->getUniqueId()}` required sub service component `{$id}` is not found.");
        }
        
        return null;
    }
    
    /**
     * 注册服务
     *
     * @param string $id 服务ID
     * @param mixed  $definition 服务配置
     *
     * @throws InvalidConfigException
     */
    public function setService($id, $definition)
    {
        unset($this->_services[$id]);
        
        if ($definition === null) {
            unset($this->_definitions[$id]);
            
            return;
        }
        
        if (is_object($definition) || is_callable($definition, true)) {
            // an object, a class name, or a PHP callable
            $this->_definitions[$id] = $definition;
        } elseif (is_array($definition)) {
            // a configuration array
            if (isset($definition['class'])) {
                $this->_definitions[$id] = $definition;
            } else {
                throw new InvalidConfigException("The configuration for the \"$id\" service must contain a \"class\" element.");
            }
        } else {
            throw new InvalidConfigException("Unexpected configuration type for the \"$id\" service: " . gettype($definition));
        }
    }
    
    /**
     * 服务执行后的状态
     *
     * @return bool
     */
    public function getStatus()
    {
        return $this->_status;
    }
    
    /**
     * 服务执行后的相关信息
     *
     * @return mixed
     */
    public function getInfo()
    {
        return $this->_info;
    }
    
    /**
     * 服务执行后的相关数据
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->_data;
    }
    
    /**
     * 服务执行后的结果数组
     *
     * @return array ['status', 'info', 'data']
     */
    public function getResult()
    {
        return [
            'status' => $this->_status,
            'info'   => $this->getInfo(),
            'data'   => $this->getData(),
        ];
    }
    
    /**
     * @var integer|false 缓存时间间隔
     */
    private $_cacheDuration;
    
    /**
     * 获取缓存时间间隔
     *
     * @return false|int
     */
    public function getCacheDuration()
    {
        if (null === $this->_cacheDuration) {
            if (null !== $this->service) {
                return $this->service->getCacheDuration();
            }
            $this->setCacheDuration();
        }
        
        return $this->_cacheDuration;
    }
    
    /**
     * 设置缓存时间间隔，默认缓存`一天`
     *
     * @param false|int $cacheDuration 缓存时间间隔
     *
     * @return $this
     */
    public function setCacheDuration($cacheDuration = 86400)
    {
        $this->_cacheDuration = $cacheDuration;
        
        return $this;
    }
    
    /**
     * 删除缓存
     */
    public function clearCache()
    {
    }
    
    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if ($this->hasService($name)) {
            return $this->getService($name);
        }
        
        return parent::__get($name);
    }
    
    /**
     * {@inheritdoc}
     */
    public function __isset($name)
    {
        if ($this->hasService($name)) {
            return true;
        }
        
        return parent::__isset($name);
    }
    
}
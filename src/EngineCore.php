<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore;

use EngineCore\{
    services\ServiceLocator, helpers\ArrayHelper
};
use Yii;
use yii\{
    base\BaseObject, base\InvalidConfigException, helpers\VarDumper
};

/**
 * Class EngineCore
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class EngineCore extends BaseObject
{
    
    /**
     * @var ServiceLocator 服务类实例，用于调用系统服务
     */
    public static $service;
    
    /**
     * EngineCore 当前版本
     *
     * @return string
     */
    public static function getVersion()
    {
        return '0.4';
    }
    
    /**
     * EngineCore constructor.
     *
     * @param ServiceLocator $service
     * @param array          $config
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct(ServiceLocator $service, $config = [])
    {
        static::$service = $service;
        
        parent::__construct($config);
    }
    
    /**
     * 输出调试信息
     *
     * @param string|array $var
     * @param string       $category
     */
    public static function traceInfo($var, $category = 'Ec::traceInfo')
    {
        Yii::debug(VarDumper::dumpAsString($var), $category);
    }
    
    /**
     * 浏览器友好的变量输出
     *
     * @param mixed   $arr            变量
     * @param string  $getCalledClass 触发该方法的类名
     * @param boolean $echo           是否输出 默认为True 如果为false 则返回输出字符串
     * @param string  $label          标签 默认为空
     * @param boolean $strict         是否严谨 默认为true
     *
     * @return string|void
     */
    public static function dump($arr, $getCalledClass = null, $echo = true, $label = null, $strict = true)
    {
        if (YII_DEBUG && $getCalledClass) {
            echo '<pre>================ START: ' . $getCalledClass . ' ================</pre>';
        }
        ArrayHelper::dump($arr, $echo, $label, $strict);
        if (YII_DEBUG && $getCalledClass) {
            echo '<pre>================ END: ' . $getCalledClass . ' ================</pre>';
        }
    }
    
    /**
     * 支持抛出模型类（Model|ActiveRecord）验证错误的事务操作
     *
     * 事务操作默认只抛出异常错误，如果需要抛出模型类产生的验证错误，`$callback`函数内需要被获取到的模型类必须使用
     * [[EngineCore\base\ExtendModelTrait()]]用以支持该方法
     * @see \EngineCore\base\ExtendModelTrait::afterValidate()
     *
     * @param callable    $callback       a valid PHP callback that performs the job. Accepts connection instance as
     *                                    parameter.
     * @param string|null $isolationLevel The isolation level to use for this transaction.
     *
     * @throws \Exception
     * @return mixed result of callback function
     */
    public static function transaction(callable $callback, $isolationLevel = null)
    {
        self::setThrowException();
        $result = Yii::$app->getDb()->transaction($callback, $isolationLevel);
        self::setThrowException(false);
        
        return $result;
    }
    
    /**
     * 抛出异常，默认不抛出
     *
     * @var boolean
     */
    protected static $_throwException = false;
    
    /**
     * 获取是否允许抛出异常
     *
     * @return boolean
     */
    public static function getThrowException()
    {
        return static::$_throwException;
    }
    
    /**
     * 设置是否允许抛出异常，默认为`true`(允许)
     *
     * @param boolean $throw
     */
    public static function setThrowException($throw = true)
    {
        static::$_throwException = $throw;
    }
    
    /**
     * 根据配置数据创建对象，可检测对象是否继承某个类或实现某个接口
     *
     * @param string|array|callable $type
     * @param array                 $params    构造函数参数
     * @param null                  $reference 检测对象是否继承某个类或实现某个接口
     *
     * @return object
     * @throws InvalidConfigException
     */
    public static function createObject($type, array $params = [], $reference = null)
    {
        $object = Yii::createObject($type, $params);
        if (null === $reference || $object instanceof $reference) {
            return $object;
        }
        $string = '`%s` class must extend from or implement `%s`.';
        throw new InvalidConfigException(sprintf($string, get_class($object), $reference));
    }
    
}
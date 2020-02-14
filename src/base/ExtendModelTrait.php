<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\base;

use EngineCore\Ec;
use EngineCore\helpers\ArrayHelper;
use Exception;
use Yii;

/**
 * Class ExtendModelTrait
 * 扩展模型功能
 *
 * @property int|false $cacheDuration
 * @property boolean   $throwException
 * @property array     $all
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
trait ExtendModelTrait
{
    
    /**
     * 获取是否允许抛出异常
     *
     * @return boolean
     */
    public function getThrowException()
    {
        return $this->_throwException;
    }
    
    /**
     * 设置是否允许抛出异常，默认允许抛出异常
     *
     * @param boolean $throw
     *
     * @return $this
     */
    public function setThrowException($throw = true)
    {
        $this->_throwException = $throw;
        
        return $this;
    }
    
    /**
     * 获取模型所有数据，通常结合缓存使用
     *
     * @return array
     */
    public function getAll()
    {
        return [];
    }
    
    /**
     * 清除缓存
     */
    public function clearCache()
    {
    }
    
    /**
     * 获取缓存时间间隔
     *
     * @return false|int
     */
    public function getCacheDuration()
    {
        if (null === $this->_cacheDuration) {
            $this->setCacheDuration();
        }
        
        return $this->_cacheDuration;
    }
    
    /**
     * 设置缓存时间间隔，默认缓存`一天`
     *
     * @param false|int $cacheDuration 缓存时间间隔，默认缓存`一天`
     *
     * @return $this
     */
    public function setCacheDuration($cacheDuration = 86400)
    {
        $this->_cacheDuration = $cacheDuration;
        
        return $this;
    }
    
    /**
     * 储存验证后的错误信息
     * @throws Exception
     */
    public function afterValidate()
    {
        parent::afterValidate();
        if ($this->hasErrors()) {
            // 格式化信息数组
            $errors = $this->getFirstErrors();
            if (count($errors) > 1) {
                $i = 1;
                foreach ($errors as &$value) {
                    $value = $i++ . ') ' . $value;
                }
            }
            /**
             * 添加事务支持
             *
             * 如果存在事务操作且事务内的模型启用抛出异常，则把获取到的错误信息以异常方式抛出，
             * 否则可通过`$_result`属性获取相关执行结果信息。
             */
            if (Yii::$app->getDb()->getIsActive() && $this->_throwException()) {
                throw new Exception(ArrayHelper::arrayToString($errors, ''));
            } else {
                $this->_result = ArrayHelper::arrayToString($errors, "</br>");
            }
        }
    }
    
    /**
     * 是否允许抛出异常
     *
     * @return bool
     */
    private function _throwException()
    {
        return $this->getThrowException() === true
            || (Ec::getThrowException() && $this->getThrowException() !== false);
    }
    
    /**
     * @var string 储存反馈数据，如通过模型类向客户端传递执行后的相关信息
     */
    public $_result;
    protected $_throwException;
    private $_cacheDuration;
    
}

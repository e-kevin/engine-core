<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\base;

use EngineCore\Ec;
use Exception;
use Yii;

/**
 * Class ExtendModelTrait
 * 扩展模型功能
 *
 * @property int|false                         $cacheDuration
 * @property boolean                           $throwException
 * @property array                             $all
 * @property \EngineCore\services\system\Error $errorService
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
trait ExtendModelTrait
{
    
    protected $_throwException;
    
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
    
    private $_cacheDuration;
    
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
     * {@inheritdoc}
     * 添加对模型外其它错误信息的判断
     */
    public function hasErrors($attribute = null)
    {
        $has = parent::hasErrors($attribute);
        if (!$has) {
            $has = $this->getErrorService()->hasModelOtherErrors();
        }
        
        return $has;
    }
    
    /**
     * 处理验证后的错误信息
     * @throws Exception
     */
    public function afterValidate()
    {
        parent::afterValidate();
        if ($this->hasErrors()) {
            $errorService = $this->getErrorService();
            /**
             * 储存Model错误信息，交由调度响应器处理呈现问题
             * @see \EngineCore\services\system\Error::addModelOtherErrors() 描述
             */
            $errorService->addModelErrors($this->getErrors(), get_called_class(), __METHOD__);
            /**
             * 添加事务支持
             *
             * 通常在事务过程中，如果事务内的某个模型存在错误信息（验证出错等），因为错误信息储存
             * 在[[Model::$errors]]属性里，并未触发异常，将会导致该模型方法被中断后事务依然继续执行。
             * 面对这种情况，除了自行添加判断外，使用EngineCore对模型的事务异常抛出功能支持可以
             * 很方便地解决这个问题，非常适用于调试排错。
             *
             * 如果事务中的模型方法对业务影响不大，可以忽略不计，则不需要理会。
             *
             * 以下是启用方法：
             * 在需要的模型内，开启抛出异常即可：
             * ```php
             *  Model()->setThrowException()->{method};
             * ```
             * 事务异常判断优先级查看：
             * @see _allowThrowException()
             */
            if (null !== Yii::$app->getDb()->getTransaction() && $this->_allowThrowException()) {
                $errors = parent::hasErrors()
                    ? $errorService->getModelFirstErrors()
                    : $errorService->getModelOtherErrors();
                throw new Exception($errorService->getFormatErrors($errors, "\r\n"));
            }
        }
    }
    
    /**
     * 是否允许抛出异常
     *
     * @return bool
     */
    private function _allowThrowException()
    {
        return $this->getThrowException() === true
            || (Ec::getThrowException() && $this->getThrowException() !== false);
    }
    
    /**
     * 获取错误服务类
     *
     * @return \EngineCore\services\system\Error
     */
    public function getErrorService()
    {
        return Ec::$service->getSystem()->getError();
    }
    
}
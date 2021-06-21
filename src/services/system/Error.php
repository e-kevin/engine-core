<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\services\system;

use EngineCore\base\Service;
use EngineCore\helpers\ArrayHelper;
use EngineCore\helpers\SessionFlashHelper;
use EngineCore\services\System;
use Yii;

/**
 * 系统错误信息管理服务类
 *
 * 管理模型验证错误信息和模型其它错误信息，带调试信息，方便调试排错
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Error extends Service
{
    
    /**
     * @var System 父级服务类
     */
    public $service;
    
    private $_errors;
    
    /**
     *
     * 是否存在【任一|指定】属性的模型错误信息
     *
     * @param string|null $attribute
     *
     * @return bool
     */
    public function hasModelErrors($attribute = null): bool
    {
        return $attribute === null ? !empty($this->_errors) : isset($this->_errors[$attribute]);
    }
    
    /**
     * 获取【所有|指定属性】的模型错误信息
     *
     * @param string $attribute
     *
     * @return array
     *
     * ```php
     * [
     *     'username' => [
     *         'Username is required.',
     *         'Username must contain only word characters.',
     *     ],
     *     'email' => [
     *         'Email address is invalid.',
     *     ],
     * ]
     * ```
     *
     * @see getModelFirstErrors()
     * @see getModelFirstError()
     */
    public function getModelErrors($attribute = null): array
    {
        if ($attribute === null) {
            return $this->_errors ?: [];
        }
        
        return $this->_errors[$attribute] ?? [];
    }
    
    /**
     * 获取模型内每个属性的第一个错误信息
     *
     * @return array
     * @see getModelErrors()
     */
    public function getModelFirstErrors(): array
    {
        if (empty($this->_errors)) {
            return [];
        }
        
        $errors = [];
        foreach ($this->_errors as $name => $error) {
            if (!empty($error)) {
                $errors[$name] = reset($error);
            }
        }
        
        return $errors;
    }
    
    /**
     * 获取模型指定属性的第一个错误信息
     *
     * @param string $attribute
     *
     * @return string|null
     */
    public function getModelFirstError($attribute)
    {
        return isset($this->_errors[$attribute]) ? reset($this->_errors[$attribute]) : null;
    }
    
    /**
     * 直接添加模型的所有错误信息，即模型的`\yii\base\Model::getErrors()`
     * @see \yii\base\Model::getErrors()
     *
     * @param array  $errors
     * @param string $object 操作触发的对象名，一般用get_called_class()或get_class()获取
     * @param string $method 触发的方法名，一般用__METHOD__获取
     */
    public function addModelErrors(array $errors, $object, $method)
    {
        $operator = $object . '::' . $method;
        Yii::debug('This operation triggered by ' . $operator, __METHOD__);
        $this->_errors = $errors;
    }
    
    protected $_otherErrors;
    
    /**
     * 是否存在模型其它错误信息
     *
     * @return bool
     */
    public function hasModelOtherErrors()
    {
        return !empty($this->_otherErrors);
    }
    
    /**
     * 获取模型其它错误信息
     *
     * @return array
     */
    public function getModelOtherErrors()
    {
        return $this->_otherErrors ?: [];
    }
    
    /**
     * 添加其它错误信息
     *
     * 通常情况下，模型错误信息储存在[[Model::$errors]]属性里，其它错误信息会习惯用闪存方式反馈给客户端，如：
     * ```php
     *  Yii::$app->session->setFlash('error', 'this is an other error message.');
     * ```
     * 但这种情况有个弊端，则是这些闪存方式的错误信息内容，在AJAX请求模式下，无法被捕捉到并反馈给客户端，
     * 需要另外处理。如果多处出现这种情况，工作量和效率都是个问题。
     *
     * 因此，我们建议模型内的其它错误信息，如：[[Model::beforeValidate()]]、[[Model::beforeDelete()]]等处执行
     * 逻辑判断时，当判断失败后，需要设置错误提示信息反馈到客户端，我们可以调用[[addModelOtherErrors()]]方法来
     * 储存错误信息，剩下的工作交由调度响应器处理即可。
     *
     * 调度响应器会根据主题呈现方式、请求方式等不同来决定如何把这些错误信息反馈到客户端。
     * @see \EngineCore\web\DispatchResponse::dispatchJump()
     *
     * @param string $errors
     * @param string $object 操作触发的对象名，一般用get_called_class()或get_class()获取
     * @param string $method 触发的方法名，一般用__METHOD__获取
     */
    public function addModelOtherErrors(string $errors, $object, $method)
    {
        $operator = $object . '::' . $method;
        Yii::debug('This is an out-of-model error message triggered by ' . $operator, __METHOD__);
        $this->_otherErrors[$operator] = $errors;
    }
    
    /**
     * 删除模型所有其它错误信息
     */
    public function clearModelOtherErrors()
    {
        $this->_otherErrors = [];
    }
    
    /**
     * 获取格式化后的错误信息
     *
     * @param array|null  $errors 一维数组
     * @param null|string $glue   不为'null'时，用该字符串格式化数组
     *
     * @return array|string
     */
    public function getFormatErrors($errors = null, $glue = null)
    {
        // 格式化信息数组
        $errors = $errors ?: $this->getModelFirstErrors();
        if (count($errors) > 1) {
            $i = 1;
            foreach ($errors as &$value) {
                $value = $i++ . ') ' . $value;
            }
        }
        
        return $glue ? ArrayHelper::arrayToString($errors, $glue) : $errors;
    }
    
    /**
     * 是否存在错误闪存信息
     *
     * @return bool
     */
    public function hasFlashErrors()
    {
        return SessionFlashHelper::hasErrors();
    }
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\helpers;

use Yii;

/**
 * 闪存信息助手类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class SessionFlashHelper
{
    
    /**
     * 是否存在错误闪存信息
     *
     * @return bool
     */
    public static function hasErrors()
    {
        return Yii::$app->getSession()->has('error') || Yii::$app->getSession()->has('danger');
    }
    
    /**
     * 设置闪存信息
     *
     * @param string $key
     * @param mixed  $value
     * @param bool   $removeAfterAccess
     */
    public static function setFlash($key, $value = true, $removeAfterAccess = true)
    {
        Yii::$app->getSession()->setFlash($key, $value, $removeAfterAccess);
    }
    
    /**
     * 获取闪存信息
     *
     * @param string $key
     * @param null   $defaultValue
     * @param bool   $delete
     *
     * @return mixed
     */
    public static function getFlash($key, $defaultValue = null, $delete = false)
    {
        return Yii::$app->getSession()->getFlash($key, $defaultValue, $delete);
    }
    
    /**
     * 设置错误闪存信息
     *
     * @param mixed $value
     * @param bool  $removeAfterAccess
     */
    public static function setError($value, $removeAfterAccess = true)
    {
        self::setFlash('error', $value, $removeAfterAccess);
    }
    
    /**
     * 设置成功闪存信息
     *
     * @param mixed $value
     * @param bool  $removeAfterAccess
     */
    public static function setSuccess($value, $removeAfterAccess = true)
    {
        self::setFlash('success', $value, $removeAfterAccess);
    }
    
    /**
     * 设置提示闪存信息
     *
     * @param mixed $value
     * @param bool  $removeAfterAccess
     */
    public static function setInfo($value, $removeAfterAccess = true)
    {
        self::setFlash('info', $value, $removeAfterAccess);
    }
    
    /**
     * 设置危险闪存信息
     *
     * @param mixed $value
     * @param bool  $removeAfterAccess
     */
    public static function setDanger($value, $removeAfterAccess = true)
    {
        self::setFlash('danger', $value, $removeAfterAccess);
    }
    
    /**
     * 设置警告闪存信息
     *
     * @param mixed $value
     * @param bool  $removeAfterAccess
     */
    public static function setWarning($value, $removeAfterAccess = true)
    {
        self::setFlash('warning', $value, $removeAfterAccess);
    }
    
    /**
     * 获取错误闪存信息
     *
     * @param mixed $defaultValue
     * @param bool  $delete
     *
     * @return mixed
     */
    public static function getError($defaultValue = null, $delete = false)
    {
        return self::getFlash('error', $defaultValue, $delete);
    }
    
    /**
     * 获取成功闪存信息
     *
     * @param mixed $defaultValue
     * @param bool  $delete
     *
     * @return mixed
     */
    public static function getSuccess($defaultValue = null, $delete = false)
    {
        return self::getFlash('success', $defaultValue, $delete);
    }
    
    /**
     * 获取提示闪存信息
     *
     * @param mixed $defaultValue
     * @param bool  $delete
     *
     * @return mixed
     */
    public static function getInfo($defaultValue = null, $delete = false)
    {
        return self::getFlash('info', $defaultValue, $delete);
    }
    
    /**
     * 获取危险闪存信息
     *
     * @param mixed $defaultValue
     * @param bool  $delete
     *
     * @return mixed
     */
    public static function getDanger($defaultValue = null, $delete = false)
    {
        return self::getFlash('danger', $defaultValue, $delete);
    }
    
    /**
     * 获取警告闪存信息
     *
     * @param mixed $defaultValue
     * @param bool  $delete
     *
     * @return mixed
     */
    public static function getWarning($defaultValue = null, $delete = false)
    {
        return self::getFlash('warning', $defaultValue, $delete);
    }
    
}
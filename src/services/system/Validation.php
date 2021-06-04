<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\system;

use EngineCore\base\Service;
use EngineCore\extension\setting\SettingProviderInterface;
use EngineCore\helpers\ArrayHelper;
use EngineCore\services\System;
use Yii;
use yii\validators\EmailValidator;

/**
 * 规则验证服务类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Validation extends Service
{
    
    /**
     * @var System 父级服务类
     */
    public $service;
    
    /**
     * 检测邮箱是否被禁止使用
     *
     * @param string $email 邮箱
     *
     * @return boolean true - 可以使用，false - 禁止使用
     */
    public function validateEmail($email)
    {
        return true;
    }
    
    /**
     * 检测用户名是否被禁用
     *
     * @param string $username 用户名
     *
     * @return boolean true - 可以使用，false - 禁止使用
     */
    public function validateUsername($username)
    {
        return true;
    }
    
    /**
     * 检测手机是否被禁止使用
     *
     * @param string $mobile 手机
     *
     * @return boolean true - 可以使用，false - 禁止使用
     */
    public function validateMobile($mobile)
    {
        return true;
    }
    
    /**
     * 检测手机是否合法
     *
     * @param string $mobile 手机
     *
     * @return boolean true - 可以使用，false - 禁止使用
     */
    public function validateMobileFormat($mobile)
    {
        return preg_match('/^((13[0-9])|147|(15[0-35-9])|180|(18[2-9]))[0-9]{8}$/A', $mobile) ? true : false;
    }
    
    /**
     * 检测邮箱地址是否合法
     *
     * @param string $email 邮箱
     *
     * @return boolean true - 可以使用，false - 禁止使用
     */
    public function validateEmailFormat($email)
    {
        return (new EmailValidator())->validate($email);
    }
    
    /**
     * Validates password
     *
     * @param string $password password to validate
     * @param string $passwordHash
     *
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password, $passwordHash)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $passwordHash);
    }
    
}
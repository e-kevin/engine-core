<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\services;

use EngineCore\services\system\Cache;
use EngineCore\base\Service;
use EngineCore\services\system\Setting;
use EngineCore\services\system\Error;
use EngineCore\services\system\Mailer;
use EngineCore\services\system\Validation;
use EngineCore\services\system\Version;

/**
 * 系统管理服务类
 *
 * @property Setting|Service    $setting
 * @property Validation|Service $validation
 * @property Mailer|Service     $mailer
 * @property Version|Service    $version
 * @property Cache|Service      $cache
 * @property Error|Service      $error
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class System extends Service
{
    
    const
        SETTING_SERVICE = 'setting', // 系统设置服务类
        VALIDATION_SERVICE = 'validation', // 规则验证服务类
        MAILER_SERVICE = 'mailer', // 邮件服务类
        VERSION_SERVICE = 'version', // 版本验证服务类
        CACHE_SERVICE = 'cache', // 系统缓存服务类
        ERROR_SERVICE = 'error'; // 系统错误信息管理服务类
    
    /**
     * {@inheritdoc}
     */
    public function coreServices()
    {
        return [
            self::SETTING_SERVICE    => ['class' => 'EngineCore\services\system\Setting'],
            self::VALIDATION_SERVICE => ['class' => 'EngineCore\services\system\Validation'],
            self::MAILER_SERVICE     => ['class' => 'EngineCore\services\system\Mailer'],
            self::VERSION_SERVICE    => ['class' => 'EngineCore\services\system\Version'],
            self::CACHE_SERVICE      => ['class' => 'EngineCore\services\system\Cache'],
            self::ERROR_SERVICE      => ['class' => 'EngineCore\services\system\Error'],
        ];
    }
    
    /**
     * 系统设置服务类
     *
     * @return Setting|Service
     */
    public function getSetting()
    {
        return $this->getService(self::SETTING_SERVICE);
    }
    
    /**
     * 规则验证服务类
     *
     * @return Validation|Service
     */
    public function getValidation()
    {
        return $this->getService(self::VALIDATION_SERVICE);
    }
    
    /**
     * 发送邮件服务类
     *
     * @return Mailer|Service
     */
    public function getMailer()
    {
        return $this->getService(self::MAILER_SERVICE);
    }
    
    /**
     * 版本验证服务类
     *
     * @return Version|Service
     */
    public function getVersion()
    {
        return $this->getService(self::VERSION_SERVICE);
    }
    
    /**
     * 系统缓存服务类
     *
     * @return Cache|Service
     */
    public function getCache()
    {
        return $this->getService(self::CACHE_SERVICE);
    }
    
    /**
     * 系统错误信息管理服务类
     *
     * @return Error|Service
     */
    public function getError()
    {
        return $this->getService(self::ERROR_SERVICE);
    }
    
}
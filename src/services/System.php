<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services;

use EngineCore\services\system\CacheService;
use EngineCore\base\Service;
use EngineCore\services\system\ConfigService;
use EngineCore\services\system\MailerService;
use EngineCore\services\system\ValidationService;
use EngineCore\services\system\VersionService;

/**
 * 系统管理服务类
 *
 * @property ConfigService|Service     $config
 * @property ValidationService|Service $validation
 * @property MailerService|Service     $mailer
 * @property VersionService|Service    $version
 * @property CacheService|Service      $cache
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class System extends Service
{
    
    const
        CONFIG_SERVICE = 'config', // 系统配置服务类
        VALIDATION_SERVICE = 'validation', // 规则验证服务类
        MAILER_SERVICE = 'mailer', // 邮件服务类
        VERSION_SERVICE = 'version', // 扩展版本验证服务类
        CACHE_SERVICE = 'cache'; // 系统缓存服务类
    
    /**
     * @inheritdoc
     */
    public function coreServices()
    {
        return [
            self::CONFIG_SERVICE     => ['class' => '\EngineCore\services\system\ConfigService'],
            self::VALIDATION_SERVICE => ['class' => '\EngineCore\services\system\ValidationService'],
            self::MAILER_SERVICE     => ['class' => '\EngineCore\services\system\MailerService'],
            self::VERSION_SERVICE    => ['class' => '\EngineCore\services\system\VersionService'],
            self::CACHE_SERVICE      => ['class' => '\EngineCore\services\system\CacheService'],
        ];
    }
    
    /**
     * 系统配置服务类
     *
     * @return ConfigService|Service
     */
    public function getConfig()
    {
        return $this->get(self::CONFIG_SERVICE);
    }
    
    /**
     * 规则验证服务类
     *
     * @return ValidationService|Service
     */
    public function getValidation()
    {
        return $this->get(self::VALIDATION_SERVICE);
    }
    
    /**
     * 发送邮件服务类
     *
     * @return MailerService|Service
     */
    public function getMailer()
    {
        return $this->get(self::MAILER_SERVICE);
    }
    
    /**
     * 版本验证服务类
     *
     * @return VersionService|Service
     */
    public function getVersion()
    {
        return $this->get(self::VERSION_SERVICE);
    }
    
    /**
     * 系统缓存服务类
     *
     * @return CacheService|Service
     */
    public function getCache()
    {
        return $this->get(self::CACHE_SERVICE);
    }
    
}
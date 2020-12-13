<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services;

use EngineCore\services\menu\components\ConfigServiceInterface;
use EngineCore\services\menu\components\PageServiceInterface;
use EngineCore\base\Service;

/**
 * 菜单管理服务类
 *
 * @property ConfigServiceInterface|Service $config
 * @property PageServiceInterface|Service   $page
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Menu extends Service
{
    
    const
        CONFIG_SERVICE = 'config', // 配置服务类
        PAGE_SERVICE = 'page'; // 视图页面服务类
    
    /**
     * {@inheritdoc}
     */
    public function coreServices()
    {
        return [
            self::CONFIG_SERVICE => ['class' => 'EngineCore\services\menu\Config'],
            self::PAGE_SERVICE   => ['class' => 'EngineCore\services\menu\Page'],
        ];
    }
    
    /**
     * 配置服务类
     *
     * @return ConfigServiceInterface|Service
     */
    public function getConfig()
    {
        return $this->getService(self::CONFIG_SERVICE);
    }
    
    /**
     * 视图页面服务类
     *
     * @return PageServiceInterface|Service
     */
    public function getPage()
    {
        return $this->getService(self::PAGE_SERVICE);
    }
    
}
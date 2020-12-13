<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services;

use EngineCore\base\Service;

/**
 * @property Extension $extension 扩展服务类
 * @property System    $system 系统服务类
 * @property Menu      $menu 菜单服务类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ServiceLocator extends \EngineCore\base\ServiceLocator
{
    
    const
        EXTENSION_SERVICE = 'extension',
        SYSTEM_SERVICE = 'system',
        MENU_SERVICE = 'menu';
    
    /**
     * {@inheritdoc}
     */
    public function coreLocators()
    {
        return [
            self::EXTENSION_SERVICE => ['class' => 'EngineCore\services\Extension'],
            self::SYSTEM_SERVICE    => ['class' => 'EngineCore\services\System'],
            self::MENU_SERVICE      => ['class' => 'EngineCore\services\Menu'],
        ];
    }
    
    /**
     * 扩展服务类
     *
     * @return Extension|Service
     */
    public function getExtension()
    {
        return $this->get(self::EXTENSION_SERVICE);
    }
    
    /**
     * 系统服务类
     *
     * @return System|Service
     */
    public function getSystem()
    {
        return $this->get(self::SYSTEM_SERVICE);
    }
    
    /**
     * 菜单服务类
     *
     * @return Menu|Service
     */
    public function getMenu()
    {
        return $this->get(self::MENU_SERVICE);
    }
    
}
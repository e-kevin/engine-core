<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\services;

/**
 * @property Extension $extension   扩展服务类
 * @property System    $system      系统服务类
 * @property Menu      $menu        菜单服务类
 * @property Migration $migration   数据库迁移服务类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ServiceLocator extends \EngineCore\base\ServiceLocator
{
    
    const
        EXTENSION_SERVICE = 'extension',
        SYSTEM_SERVICE = 'system',
        MENU_SERVICE = 'menu',
        MIGRATION_SERVICE = 'migration';
    
    /**
     * {@inheritdoc}
     */
    public function coreLocators()
    {
        return [
            self::EXTENSION_SERVICE => ['class' => 'EngineCore\services\Extension'],
            self::SYSTEM_SERVICE    => ['class' => 'EngineCore\services\System'],
            self::MENU_SERVICE      => ['class' => 'EngineCore\services\Menu'],
            self::MIGRATION_SERVICE => ['class' => 'EngineCore\services\Migration'],
        ];
    }
    
    /**
     * 扩展服务类
     *
     * @return Extension
     */
    public function getExtension()
    {
        return $this->get(self::EXTENSION_SERVICE);
    }
    
    /**
     * 系统服务类
     *
     * @return System
     */
    public function getSystem()
    {
        return $this->get(self::SYSTEM_SERVICE);
    }
    
    /**
     * 菜单服务类
     *
     * @return Menu
     */
    public function getMenu()
    {
        return $this->get(self::MENU_SERVICE);
    }
    
    /**
     * 数据库迁移服务类
     *
     * @return Migration
     */
    public function getMigration()
    {
        return $this->get(self::MIGRATION_SERVICE);
    }
    
}
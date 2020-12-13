<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository\info;

/**
 * 模块扩展信息类
 *
 * @property array $migrationPath 数据库迁移路径，只读属性
 * @property array $menus 扩展菜单信息，只读属性
 * @property bool  $isBootstrap 是否启用bootstrap，只读属性
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ModularityInfo extends ExtensionInfo
{
    
    use ExtensionTrait;
    
    /**
     * @var string 模块ID
     */
    protected $id;
    
    /**
     * @var boolean 是否启用bootstrap
     */
    protected $bootstrap = false;
    
    /**
     * {@inheritdoc}
     */
    final public function getType(): string
    {
        return self::TYPE_MODULE;
    }
    
    /**
     * 获取模块菜单信息
     *
     * @see \EngineCore\extension\menu\MenuProvider::defaultMenuConfig()
     *
     * @return array
     */
    public function getMenus(): array
    {
        return [];
    }
    
    /**
     * 获取数据库迁移路径
     *
     * @return array
     */
    public function getMigrationPath(): array
    {
        return [$this->getAutoloadPsr4()['path'] . DIRECTORY_SEPARATOR . 'migrations'];
    }
    
    /**
     * 获取是否启用bootstrap
     *
     * @return bool
     */
    public function getIsBootstrap(): bool
    {
        return (bool)($this->extraConfig['bootstrap'] ?? $this->bootstrap);
    }
    
    /**
     * @inheritdoc
     */
    public function install(): bool
    {
        if (parent::install() == false) {
            return false;
        }
        $this->runMigrate('up');
        
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function uninstall(): bool
    {
        if (parent::uninstall() == false) {
            return false;
        }
        $this->runMigrate('down');
        
        return true;
    }
    
}
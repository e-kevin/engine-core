<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository\info;

/**
 * 控制器扩展信息类
 *
 * @property string $moduleId 扩展所属模块ID，读写属性
 * @property array  $migrationPath 数据库迁移路径，只读属性
 * @property array  $menus 扩展菜单信息，只读属性
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ControllerInfo extends ExtensionInfo
{
    
    use ExtensionTrait;
    
    /**
     * @var string 控制器ID
     */
    protected $id;
    
    /**
     * @var string 扩展所属模块ID，默认空值为当前应用扩展，在Yii中，应用本身就是一个顶级模块
     */
    protected $moduleId = '';
    
    /**
     * {@inheritdoc}
     */
    final public function getType(): string
    {
        return self::TYPE_CONTROLLER;
    }
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        $this->name = $this->getModuleId() ? "/{$this->getModuleId()}/{$this->id}" : "{$this->id}";
    }
    
    /**
     * 获取扩展菜单信息
     *
     * 配置格式参见：
     * @see \EngineCore\extension\menu\MenuProvider::defaultMenuConfig()
     *
     * @return array
     */
    public function getMenus(): array
    {
        return [];
    }
    
    /**
     * 获取扩展所属模块ID
     *
     * @return string
     */
    public function getModuleId(): string
    {
        return $this->moduleId;
    }
    
    /**
     * 获取扩展所属模块ID
     *
     * @param string $id
     */
    public function setModuleId(string $id)
    {
        $this->moduleId = $id;
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
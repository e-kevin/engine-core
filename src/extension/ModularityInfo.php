<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension;

/**
 * 模块扩展信息类
 *
 * @property string $migrationPath 数据库迁移路径，只读属性
 * @property array  $menus 扩展菜单信息，只读属性
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ModularityInfo extends ExtensionInfo
{
    
    use ExtensionTrait;
    
    /**
     * @var string 模块ID
     */
    public $id;
    
    /**
     * @var boolean 是否启用bootstrap
     */
    public $bootstrap = false;
    
    /**
     * @var string 数据库迁移路径
     */
    private $_migrationPath;
    
    /**
     * ControllerInfo constructor.
     *
     * @param string $uniqueId
     * @param string $uniqueName
     * @param string $version
     * @param string $migrationPath
     * @param array  $config
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct(
        string $uniqueId, string $uniqueName, string $version, string $migrationPath, array $config = []
    ) {
        $this->_migrationPath = $migrationPath;
        parent::__construct($uniqueId, $uniqueName, $version, $config);
    }
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->_name = $this->_name ?: $this->id;
    }
    
    /**
     * 获取模块菜单信息
     *
     * @see \EngineCore\extension\menu\MenuProvider::defaultMenuConfig()
     *
     * @return array
     */
    public function getMenus()
    {
        return [];
    }
    
    /**
     * 获取数据库迁移路径
     *
     * @return string
     */
    final public function getMigrationPath()
    {
        return $this->_migrationPath;
    }
    
    /**
     * @inheritdoc
     */
    public function getConfigKey(): array
    {
        return ['components', 'params', 'modules'];
    }
    
}
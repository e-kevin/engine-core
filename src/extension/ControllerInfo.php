<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension;

/**
 * 控制器扩展信息类
 *
 * @property string $moduleId 扩展所属模块ID，读写属性
 * @property string $migrationPath 数据库迁移路径，只读属性
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
    public $id;
    
    /**
     * @var string 扩展所属模块ID
     */
    protected $_moduleId;
    
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
        // 如果没有指定扩展所属模块，则默认为扩展所属的应用
        if (empty($this->getModuleId())) {
            $this->setModuleId($this->app);
        }
        $this->_name = $this->_name ?: "/{$this->getModuleId()}/{$this->id}";
    }
    
    /**
     * 获取扩展菜单信息
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
     * 获取扩展所属模块ID
     *
     * @return string
     */
    public function getModuleId()
    {
        return $this->_moduleId;
    }
    
    /**
     * 设置扩展所属模块ID
     *
     * @param string $moduleId 模块ID
     */
    public function setModuleId($moduleId)
    {
        $this->_moduleId = $moduleId;
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
        return ['components', 'params'];
    }
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\info;

use EngineCore\Ec;
use EngineCore\extension\setting\SettingProviderInterface;
use EngineCore\extension\repository\configuration\Configuration;
use EngineCore\helpers\SecurityHelper;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * 扩展信息基础类
 *
 * @property array         $config               扩展配置信息，只读属性
 * @property Configuration $configuration        扩展配置文件的配置信息
 * @property string        $type                 扩展所属类型，只读属性
 * @property int           $category             扩展所属分类，只读属性
 * @property string        $remark               扩展备注，只读属性
 * @property string        $name                 扩展名，只读属性
 * @property string        $uniqueName           扩展唯一名，只读属性
 * @property string        $uniqueId             扩展唯一ID
 * @property string        $id                   扩展ID，读写属性
 * @property string        $app                  应用ID，只读属性
 * @property array|null    $autoloadPsr4         PSR-4命名空间，只读属性
 * @property array|null    $autoloadPsr0         PSR-0命名空间，只读属性
 * @property array         $menus                扩展菜单信息，只读属性
 * @property array         $settings             扩展设置信息，只读属性
 * @property string        $migrationTable       扩展迁移历史数据库表名，只读属性
 * @property array         $migrationPath        数据库迁移路径，只读属性
 * @property array         $migrationNamespaces  数据库迁移命名空间，只读属性
 * @property bool          $isSystem             是否为系统扩展，只读属性
 * @property bool          $isEnable             扩展是否激活，只读属性
 * @property bool          $canInstall           是否可以安装，只读属性
 * @property bool          $canUninstall         是否可以卸载，只读属性
 * @property int           $runMode              扩展运行模式，只读属性
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ExtensionInfo extends BaseObject
{
    
    const
        /**
         * @var string 扩展随机码，可用于扩展数据库表前缀，主要作用是避免不同扩展所使用到的数据库表可能存在同名的情况，
         * 添加表前缀随机码可明确想要操作的数据库表。
         *
         * 建议每个扩展都自定义一个专属的随机码。默认的随机码预留给EngineCore使用
         * 随机码生成方式参见：@see \EngineCore\helpers\StringHelper::randString()
         */
        EXT_RAND_CODE = 'viMJHk_',
        // 运行模式
        RUN_MODULE_EXTENSION = 0, // 运行系统扩展
        RUN_MODULE_DEVELOPER = 1, // 运行开发者扩展
        // 扩展类型
        TYPE_MODULE = 'module', // 模块扩展
        TYPE_CONTROLLER = 'controller', // 控制器扩展
        TYPE_THEME = 'theme', // 主题扩展
        TYPE_CONFIG = 'config', // 系统配置扩展
        // 扩展分类
        CATEGORY_NONE = 0, // 默认扩展不属于任何分类
        CATEGORY_SYSTEM = 1, // 系统分类
        CATEGORY_EXTENSION = 2, // 扩展管理分类
        CATEGORY_INSTALLATION = 3, // 安装向导分类
        CATEGORY_MENU = 4, // 菜单分类
        CATEGORY_ACCOUNT = 5, // 用户分类
        CATEGORY_PASSPORT = 6, // 通行证分类
        CATEGORY_SECURITY = 7, // 安全分类
        CATEGORY_BACKEND_HOME = 8; // 后台主页分类
    
    /**
     * @var array 必须设置的属性值
     */
    protected $mustBeSetProps;
    
    /**
     * ExtensionInfo constructor.
     *
     * @param string $app
     * @param string $uniqueName
     * @param array  $config
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct(string $app, string $uniqueName, array $config = [])
    {
        $this->_app = $app;
        $this->_uniqueName = $uniqueName;
        parent::__construct($config);
    }
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        $this->mustBeSetProps = array_merge(['id'], (array)$this->mustBeSetProps);
        foreach ($this->mustBeSetProps as $prop) {
            if (empty($this->{$prop})) {
                throw new InvalidConfigException(get_called_class() . ' - The `' . $prop . '` property must be set.');
            }
        }
        $this->name = $this->name ?: $this->id;
    }
    
    /**
     * @var string 扩展ID
     */
    protected $id;
    
    /**
     * 获取扩展ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
    
    /**
     * 设置扩展ID
     *
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }
    
    /**
     * @var string 应用ID
     */
    private $_app;
    
    /**
     * 获取应用ID
     *
     * @return string
     */
    final public function getApp(): string
    {
        return $this->_app;
    }
    
    /**
     * @var string 扩展名称
     */
    protected $name;
    
    /**
     * 获取扩展名，一般为中文，如：系统管理模块、用户模块
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    protected $remark;
    
    /**
     * 获取扩展备注信息
     *
     * @return string
     */
    public function getRemark(): string
    {
        return $this->remark;
    }
    
    /**
     * 获取扩展所属类型
     *
     * @return string
     */
    public function getType(): string
    {
        return '';
    }
    
    /**
     * @var int 扩展所属分类
     */
    protected $category = self::CATEGORY_NONE;
    
    /**
     * 获取扩展所分类
     *
     * @return int
     */
    final public function getCategory(): int
    {
        return $this->category;
    }
    
    private $_configuration;
    
    /**
     * 获取扩展配置文件的配置信息
     *
     * @return Configuration
     */
    final public function getConfiguration(): Configuration
    {
        if (null === $this->_configuration) {
            $this->_configuration = Ec::$service->getExtension()->getRepository()->getFinder()->getConfiguration()[$this->getUniqueName()];
        }
        
        return $this->_configuration;
    }
    
    /**
     * 设置扩展配置文件的配置信息
     *
     * @param Configuration $configuration
     */
    final public function setConfiguration(Configuration $configuration)
    {
        $this->_configuration = $configuration;
    }
    
    private $_uniqueName;
    
    /**
     * 获取扩展唯一名，包括扩展名和开发者名
     *
     * @return string
     */
    final public function getUniqueName(): string
    {
        return $this->_uniqueName;
    }
    
    /**
     * 获取扩展唯一ID
     *
     * @return string
     */
    final public function getUniqueId(): string
    {
        return SecurityHelper::hash($this->getUniqueName());
    }
    
    /**
     * 获取PSR-4命名空间
     *
     * @return array|null
     * ```php
     * [
     * 'namespace' => {namespace},
     * 'path' => {path}
     * ]
     */
    public function getAutoloadPsr4()
    {
        return $this->getConfiguration()->autoloadPsr4[0] ?? null;
    }
    
    /**
     * 获取PSR-0命名空间
     *
     * @return array|null
     * ```php
     * [
     * 'namespace' => {namespace},
     * 'path' => {path}
     * ]
     * ```
     */
    public function getAutoloadPsr0()
    {
        return $this->getConfiguration()->autoloadPsr0[0] ?? null;
    }
    
    /**
     * 安装
     *
     * @return boolean
     */
    public function install(): bool
    {
        // 加载翻译文件配置
        Ec::$service->getExtension()->getEnvironment()->loadTranslationConfig($this);
        
        return true;
    }
    
    /**
     * 卸载
     *
     * @return boolean
     */
    public function uninstall(): bool
    {
        return true;
    }
    
    /**
     * 升级
     *
     * @return boolean
     */
    public function upgrade(): bool
    {
        return true;
    }
    
    /**
     * 获取扩展配置信息
     *
     * 可用配置键名和'main.php'等配置文件一样，如：
     * - `components`
     * - `params`: 如果是系统设置数据，必须放在`'system-setting'`键名的数组里，参见：
     * @see SettingProviderInterface::SETTING_KEY
     * @see SettingProviderInterface::getDefaultConfig()
     * - `modules`
     * - `controllerMap`
     *
     * 配置方式有以下两种：
     * 1、多应用配置
     * 2、当前应用配置
     *
     * 1、多应用配置：如果当前扩展属于公共扩展或多应用扩展，需要为不同应用设置针对性配置，可使用该方式，
     * 格式为：应用名 => 配置数组，如：
     * ```php
     * [
     * // 后台应用
     *  'backend' => [
     *      'components' => [],
     *      'params' => [],
     *      'modules' => [],
     *      'controllerMap' => [],
     *  ],
     * // 前台应用
     *  'frontend' => [
     *      'components' => [],
     *      'params' => [],
     *      'modules' => [],
     *      'controllerMap' => [],
     *  ],
     * // 公共应用
     * 'common' => [
     *      'components' => [],
     *      'params' => [],
     *      'modules' => [],
     *      'controllerMap' => [],
     *  ]
     * ]
     * ```
     *
     * 2、当前应用配置：每个应用均使用该配置，这是默认的配置方式，格式如下：
     * ```php
     * [
     *  'components' => [],
     *  'params' => [],
     *  'modules' => [],
     *  'controllerMap' => [],
     * ]
     * ```
     *
     * 注意：
     * 1、控制器扩展无需明确配置，系统会自动添加相应的`'controllerMap'`配置。
     * 2、支持链式键名配置方式。
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [];
    }
    
    /**
     * 获取扩展菜单信息
     *
     * 配置格式参见：
     * @see \EngineCore\extension\menu\MenuProviderTrait::getDefaultConfig()
     *
     * @return array
     */
    public function getMenus(): array
    {
        return [];
    }
    
    /**
     * 获取扩展设置信息
     *
     * 配置格式参见：
     * @see \EngineCore\extension\setting\SettingProviderTrait::getDefaultConfig()
     *
     * @return array
     */
    public function getSettings(): array
    {
        return [];
    }
    
    /**
     * 获取扩展迁移历史数据库表名
     *
     * @return string
     */
    public function getMigrationTable(): string
    {
        return '{{%' . static::EXT_RAND_CODE . 'migration}}';
    }
    
    /**
     * 获取扩展数据库迁移路径
     *
     * @return array
     */
    public function getMigrationPath(): array
    {
        return [$this->getAutoloadPsr4()['path'] . DIRECTORY_SEPARATOR . 'migrations'];
    }
    
    /**
     * 获取扩展数据库迁移命名空间
     *
     * @return array
     */
    public function getMigrationNamespaces(): array
    {
        return [];
    }
    
    protected $isSystem = false;
    
    /**
     * 获取是否为系统扩展
     *
     * @return bool
     */
    public function getIsSystem(): bool
    {
        $isSystem = Ec::$service->getExtension()
                                ->getRepository()
                                ->getDbConfiguration()[$this->getApp()][$this->getUniqueName()][0]['is_system'] ?? $this->isSystem;
        
        return boolval($isSystem);
    }
    
    protected $isEnable = false;
    
    /**
     * 获取扩展是否激活
     *
     * @return bool
     */
    public function getIsEnable(): bool
    {
        $isEnable = Ec::$service->getExtension()
                                ->getRepository()
                                ->getDbConfiguration()[$this->getApp()][$this->getUniqueName()][0]['status'] ?? $this->isEnable;
        
        return boolval($isEnable);
    }
    
    /**
     * 获取扩展是否可以安装
     *
     * @return bool
     */
    public function getCanInstall(): bool
    {
        return !isset(Ec::$service->getExtension()->getRepository()->getDbConfiguration()[$this->getApp()][$this->getUniqueName()]);
    }
    
    /**
     * 获取扩展是否可以卸载
     *
     * @return bool
     */
    public function getCanUninstall(): bool
    {
        return !$this->getIsSystem() && !$this->getCanInstall();
    }
    
    protected $runMode = self::RUN_MODULE_EXTENSION;
    
    /**
     * 获取扩展运行模式
     *
     * @return int
     */
    public function getRunMode()
    {
        return Ec::$service->getExtension()->getRepository()
                           ->getDbConfiguration()[$this->getApp()][$this->getUniqueName()][0]['run'] ?? $this->runMode;
    }
    
}
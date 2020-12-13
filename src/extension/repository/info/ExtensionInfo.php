<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository\info;

use EngineCore\Ec;
use EngineCore\extension\repository\configuration\Configuration;
use EngineCore\helpers\SecurityHelper;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * 扩展信息基础类
 *
 * @property array         $commonConfig     扩展公共配置信息，只读属性
 * @property array         $config           扩展配置信息，只读属性
 * @property Configuration $configuration    扩展配置文件的配置信息
 * @property string        $type             扩展所属类型，只读属性，【模块、控制器、主题】类型
 * @property string        $category         扩展所属分类，只读属性
 * @property string        $remark           扩展备注，只读属性
 * @property string        $name             扩展名，只读属性
 * @property string        $uniqueName       扩展唯一名，只读属性
 * @property string        $uniqueId         扩展唯一ID
 * @property string        $id               扩展ID，读写属性
 * @property string        $app              应用ID，只读属性
 * @property array|null    $autoloadPsr4     PSR-4命名空间，只读属性
 * @property array|null    $autoloadPsr0     PSR-0命名空间，只读属性
 * @property array         $extraConfig      扩展额外配置数据
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
         *
         * @see \EngineCore\helpers\StringHelper::randString()
         */
        EXT_RAND_CODE = 'viMJHk_',
        // 运行模式
        RUN_MODULE_EXTENSION = 0, // 运行系统扩展
        RUN_MODULE_DEVELOPER = 1, // 运行开发者扩展
        // 扩展类型
        TYPE_MODULE = 'module', // 模块扩展
        TYPE_CONTROLLER = 'controller', // 控制器扩展
        TYPE_THEME = 'theme', // 主题扩展
        // 扩展分类
        CATEGORY_NONE = 'category_none', // 默认扩展不属于任何分类
        CATEGORY_SYSTEM = 'category_system', // 系统分类
        CATEGORY_EXTENSION = 'category_extension', // 扩展模块分类
        CATEGORY_MENU = 'category_menu', // 菜单分类
        CATEGORY_ACCOUNT = 'category_account', // 用户分类
        CATEGORY_PASSPORT = 'category_passport', // 通行证分类
        CATEGORY_SECURITY = 'category_security'; // 安全分类
    
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
    public function getId()
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
    final public function getApp()
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
    public function getName()
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
     * 获取扩展所属类型，【模块、控制器、主题】类型
     *
     * @return string
     */
    public function getType(): string
    {
        return null;
    }
    
    /**
     * @var string 扩展所属分类
     */
    protected $category = self::CATEGORY_NONE;
    
    /**
     * 获取扩展所分类
     *
     * @return string
     */
    final public function getCategory(): string
    {
        return $this->category;
    }
    
    private $_configuration;
    
    /**
     * 获取扩展配置文件的配置信息
     *
     * @return Configuration
     */
    final public function getConfiguration()
    {
        if (null === $this->_configuration) {
            $this->_configuration = Ec::$service->getExtension()->getRepository()->getFinder()->getConfiguration()[$this->getUniqueName()];
        }
        
        return $this->_configuration;
    }
    
    private $_uniqueName;
    
    /**
     * 获取扩展唯一名，包括扩展名和开发者名
     *
     * @return string
     */
    final public function getUniqueName()
    {
        return $this->_uniqueName;
    }
    
    /**
     * 获取扩展唯一ID
     *
     * @return string
     */
    final public function getUniqueId()
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
     * - `params`
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
     * 2、当前应用配置：这是默认的配置方式，格式如下：
     * ```php
     * [
     *  'components' => [],
     *  'params' => [],
     *  'modules' => [],
     *  'controllerMap' => [],
     * ]
     * ```
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [];
    }
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension;

use Yii;
use yii\base\{
    InvalidConfigException, BaseObject
};

/**
 * 扩展信息基础类
 *
 * @property array  $authors 扩展作者，只读属性
 * @property string $category 获取扩展所属类型，只读属性
 * @property array  $commonConfig 扩展公共配置信息，只读属性
 * @property array  $commonConfigKey 扩展公共配置信息允许的键名，只读属性
 * @property array  $config 扩展配置信息，只读属性
 * @property array  $configKey 扩展配置信息允许的键名，只读属性
 * @property array  $depends 扩展所需依赖，只读属性
 * @property string $description 扩展详细描述，只读属性
 * @property array  $issueUrl 扩展Issues地址，只读属性
 * @property string $name 扩展名称，只读属性
 * @property string $note 扩展简介，只读属性
 * @property string $remark 扩展备注，只读属性
 * @property array  $repositoryUrl 扩展仓库地址，只读属性
 * @property array  $requirePackages 扩展所需的composer包，只读属性
 * @property string $uniqueId 扩展唯一ID，只读属性
 * @property string $uniqueName 扩展唯一名称，只读属性
 * @property string $url 扩展网址，只读属性
 * @property string $version 版本，只读属性
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ExtensionInfo extends BaseObject
{
    
    // todo 扩展分类是否需要删除部分没必要的？或采用类似menu的方式，用接口替换即可？
    
    const
        RUN_MODULE_EXTENSION = 0, // 运行系统扩展
        RUN_MODULE_DEVELOPER = 1, // 运行开发者扩展
        CATEGORY_NONE = 'category_none', // 默认扩展不属于任何分类
        CATEGORY_SITE = 'category_site', // 首页控制器分类
        CATEGORY_SYSTEM = 'category_system', // 系统分类
        CATEGORY_EXTENSION = 'category_extension', // 扩展模块分类
        CATEGORY_MENU = 'category_menu', // 菜单分类
        CATEGORY_ACCOUNT = 'category_account', // 用户分类
        CATEGORY_PASSPORT = 'category_passport', // 通行证分类
        CATEGORY_SECURITY = 'category_security'; // 安全分类
    
    /**
     * @var string 扩展唯一ID，不可重复
     */
    private $_uniqueId;
    
    /**
     * @var string 扩展唯一名称，不可重复
     */
    private $_uniqueName;
    
    /**
     * @var string 扩展版本
     */
    private $_version;
    
    /**
     * @var string 所属应用
     */
    public $app;
    
    /**
     * @var string 扩展ID
     */
    public $id;
    
    /**
     * @var boolean 是否系统扩展
     */
    public $isSystem = false;
    
    /**
     * @var boolean 是否可安装
     */
    public $canInstall = false;
    
    /**
     * @var boolean 是否可卸载
     */
    public $canUninstall = false;
    
    /**
     * ExtensionInfo constructor.
     *
     * @param string $uniqueId
     * @param string $uniqueName
     * @param string $version
     * @param array  $config
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct(string $uniqueId, string $uniqueName, string $version, array $config = [])
    {
        $this->_uniqueId = $uniqueId;
        $this->_uniqueName = $uniqueName;
        $this->_version = $version;
        parent::__construct($config);
    }
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        foreach ($this->_mustBeSetProps as $prop) {
            if ($this->{$prop} === null) {
                throw new InvalidConfigException(get_called_class() . ': The `$' . $prop . '` property must be set.');
            }
        }
    }
    
    /**
     * 获取扩展唯一ID，不可重复
     *
     * @return string
     */
    final public function getUniqueId(): string
    {
        return $this->_uniqueId;
    }
    
    /**
     * 获取扩展唯一名称，不可重复
     *
     * @return string
     */
    final public function getUniqueName(): string
    {
        return $this->_uniqueName;
    }
    
    /**
     * 获取扩展当前版本
     *
     * @return string
     */
    final public function getVersion(): string
    {
        return $this->_version;
    }
    
    /**
     * @var string 扩展名称
     */
    protected $_name;
    
    /**
     * 获取扩展名称
     *
     * @return string
     */
    final public function getName(): string
    {
        return $this->_name;
    }
    
    /**
     * @var string 扩展详细描述
     */
    protected $_description;
    
    /**
     * 获取扩展详细描述
     *
     * @return string
     */
    final public function getDescription(): string
    {
        return $this->_description;
    }
    
    /**
     * @var string 扩展简介
     */
    protected $_note;
    
    /**
     * 获取扩展简介
     *
     * @return string
     */
    final public function getNote(): string
    {
        return $this->_note;
    }
    
    /**
     * @var string 扩展备注信息
     */
    protected $_remark;
    
    /**
     * 获取扩展备注信息
     *
     * @return string
     */
    final public function getRemark(): string
    {
        return $this->_remark;
    }
    
    /**
     * @var string 扩展网址
     */
    protected $_url;
    
    /**
     * 获取扩展网址
     *
     * @return string
     */
    final public function getUrl(): string
    {
        return $this->_url;
    }
    
    /**
     * @var array 扩展的仓库网址
     */
    protected $_repositoryUrl;
    
    /**
     * 获取扩展的仓库网址，例如：
     * ```php
     * [
     *  'github' => 'https://github.com/EngineCore/yii2-controller-site'
     * ]
     * ```
     *
     * @return array
     */
    final public function getRepositoryUrl(): array
    {
        return $this->_repositoryUrl;
    }
    
    /**
     * @var array
     */
    protected $_issueUrl;
    
    /**
     * 获取扩展Issues地址，例如：
     * ```php
     * [
     *  'github' => 'https://github.com/EngineCore/yii2-controller-site/issues'
     * ]
     *
     * @return array
     */
    final public function getIssueUrl(): array
    {
        foreach ($this->getRepositoryUrl() as $type => $url) {
            switch ($type) {
                case 'github':
                    $this->_issueUrl[$type] = $url . '/issue';
                    break;
                case 'gitee':
                    break;
                default:
            }
        }
        
        return $this->_issueUrl;
    }
    
    /**
     * @var array 必须设置的属性值
     */
    protected $_mustBeSetProps = ['app', 'id'];
    
    /**
     * @var array 扩展所需依赖
     * ```php
     * [
     *      扩展唯一名 => 所需版本
     *      '{author}/yii2-module-system' => 'dev-master',
     * ]
     * ```
     */
    protected $_depends = [];
    
    /**
     * 获取扩展所需依赖
     *
     * @return array
     */
    final public function getDepends(): array
    {
        return $this->_depends;
    }
    
    /**
     * @var array 扩展所需的composer包
     * ```php
     * [
     *      composer包名 => 所需版本
     * ]
     * ```
     */
    protected $_requirePackages = [];
    
    /**
     * 获取扩展所需的composer包
     *
     * @return array
     */
    final public function getRequirePackages(): array
    {
        return $this->_requirePackages;
    }
    
    /**
     * @var string|null 扩展所属类型
     */
    protected $_category;
    
    /**
     * 获取扩展所属类型
     *
     * @return string|null
     */
    final public function getCategory(): string
    {
        return $this->_category;
    }
    
    /**
     * @var array 扩展作者
     *
     * ```php
     * [
     *  [
     *      'name' => 'authorName',
     *      'email' => 'authorEmail',
     *      'url' => 'authorWebUrl',
     *  ],
     * ]
     * ```
     */
    protected $_authors;
    
    
    /**
     * 获取扩展作者
     *
     * @return array
     */
    final public function getAuthors(): array
    {
        return $this->_authors;
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
     * 获取扩展配置信息允许的键名，用于过滤非法的配置数据
     *
     * @return array
     */
    public function getConfigKey(): array
    {
        return ['components', 'params'];
    }
    
    /**
     * 获取扩展配置信息
     * 可能包含的键名如下：
     * - `components`
     * - `params`
     * - `modules`
     * - `controllerMap`
     *
     * @see getConfigKey() 详情请查看
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [];
    }
    
    /**
     * 获取扩展公共配置信息允许的键名，用于过滤非法的配置数据
     *
     * @return array
     */
    public function getCommonConfigKey(): array
    {
        return ['components', 'params'];
    }
    
    /**
     * 获取扩展公共配置信息
     * 可能包含的键名如下：
     * - `components`
     * - `params`
     * - `modules`
     * - `controllerMap`
     *
     * @see getCommonConfigKey() 详情请查看
     *
     * @return array
     */
    public function getCommonConfig(): array
    {
        return [];
    }
    
}
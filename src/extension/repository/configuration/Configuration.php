<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository\configuration;

use yii\base\BaseObject;

/**
 * 扩展配置格式类
 *
 * @property string             $vendorDir
 * @property string             $name
 * @property string             $type
 * @property string             $description
 * @property string             $version
 * @property array              $keywords
 * @property string             $homepage
 * @property Author[]           $authors
 * @property SupportInformation $support
 * @property array              $composerDependencies
 * @property array              $composerDevDependencies
 * @property array              $suggest
 * @property array              $autoloadPsr0
 * @property array              $autoloadPsr4
 * @property array              $repositories
 * @property array              $extra
 * @property array              $extraConfig
 * @property array              $app
 * @property array              $extensionDependencies
 *
 * @author            E-Kevin <e-kevin@qq.com>
 */
class Configuration extends BaseObject
{
    
    const NAME_SEPARATOR = '/';
    
    /**
     * Configuration constructor.
     *
     * @param string                  $vendorDir
     * @param string                  $name
     * @param string|null             $type
     * @param string|null             $description
     * @param string|null             $version
     * @param array|null              $keywords
     * @param string                  $homepage
     * @param Author[]|null           $authors
     * @param SupportInformation|null $support
     * @param array|null              $dependencies
     * @param array|null              $devDependencies
     * @param array|null              $suggest
     * @param array|null              $autoloadPsr0
     * @param array|null              $autoloadPsr4
     * @param array|null              $repositories
     * @param mixed                   $extra
     * @param array                   $config
     *
     * @throws UndefinedPropertyException
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct(
        $vendorDir,
        $name,
        $type = null,
        $description = null,
        $version = null,
        array $keywords = null,
        $homepage = null,
        array $authors = null,
        SupportInformation $support = null,
        array $dependencies = null,
        array $devDependencies = null,
        array $suggest = null,
        array $autoloadPsr0 = null,
        array $autoloadPsr4 = null,
        array $repositories = null,
        $extra = null,
        array $config = []
    ) {
        if (null === $vendorDir || empty($vendorDir)) {
            throw new UndefinedPropertyException('vendorDir');
        }
        if (null === $name || empty($name)) {
            throw new UndefinedPropertyException('name');
        }
        if (null === $type) {
            $type = 'library';
        }
        if (null === $keywords) {
            $keywords = [];
        }
        if (null === $authors) {
            $authors = [];
        }
        if (null === $support) {
            $support = new SupportInformation;
        }
        if (null === $dependencies) {
            $dependencies = [];
        }
        if (null === $devDependencies) {
            $devDependencies = [];
        }
        if (null === $suggest) {
            $suggest = [];
        }
        if (null === $autoloadPsr4) {
            $autoloadPsr4 = [];
        }
        if (null === $autoloadPsr0) {
            $autoloadPsr0 = [];
        }
        if (null === $repositories) {
            $repositories = [];
        }
        
        $this->_vendorDir = $vendorDir;
        $this->_name = $name;
        $this->_description = $description;
        $this->_version = $version;
        $this->_type = $type;
        $this->_keywords = $keywords;
        $this->_homepage = $homepage;
        $this->_authors = $authors;
        $this->_support = $support;
        $this->_dependencies = $dependencies;
        $this->_devDependencies = $devDependencies;
        $this->_suggest = $suggest;
        $this->_autoloadPsr0 = $autoloadPsr0;
        $this->_autoloadPsr4 = $autoloadPsr4;
        $this->_repositories = $repositories;
        $this->_extra = $extra;
        
        parent::__construct($config);
    }
    
    /**
     * 获取扩展开发目录
     *
     * @return string
     */
    public function getVendorDir()
    {
        return $this->_vendorDir;
    }
    
    /**
     * 获取完整扩展名，包括开发者名和扩展名
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * 获取扩展名
     *
     * @return string
     */
    public function getProjectName()
    {
        $name = $this->getName();
        if (null === $name) {
            return null;
        }
        
        $atoms = explode(static::NAME_SEPARATOR, $name);
        
        return array_pop($atoms);
    }
    
    /**
     * 获取开发者名
     *
     * @return string
     */
    public function getVendorName()
    {
        $name = $this->getName();
        if (null === $name) {
            return null;
        }
        
        $atoms = explode(static::NAME_SEPARATOR, $name);
        array_pop($atoms);
        if (count($atoms) < 1) {
            return null;
        }
        
        return implode(static::NAME_SEPARATOR, $atoms);
    }
    
    /**
     * 获取描述
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }
    
    /**
     * 获取版本
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }
    
    /**
     * 获取扩展类型
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }
    
    /**
     * 获取关键词
     *
     * @return array
     */
    public function getKeywords()
    {
        return $this->_keywords;
    }
    
    /**
     * 获取主页
     *
     * @return string
     */
    public function getHomepage()
    {
        return $this->_homepage;
    }
    
    /**
     * 获取作者
     *
     * @return Author[]
     */
    public function getAuthors()
    {
        return $this->_authors;
    }
    
    /**
     * 获取PSR-0
     *
     * @return array
     */
    public function getAutoloadPsr0()
    {
        return $this->_autoloadPsr0;
    }
    
    /**
     * 获取PSR-4
     *
     * @return array
     */
    public function getAutoloadPsr4()
    {
        return $this->_autoloadPsr4;
    }
    
    /**
     * 获取支持相关数据
     *
     * @return SupportInformation
     */
    public function getSupport()
    {
        return $this->_support;
    }
    
    /**
     * 获取composer依赖包
     *
     * @return array
     */
    public function getComposerDependencies()
    {
        return $this->_dependencies;
    }
    
    /**
     * 获取composer开发依赖包
     *
     * @return array
     */
    public function getComposerDevDependencies()
    {
        return $this->_devDependencies;
    }
    
    /**
     * 获取建议信息
     *
     * @return array
     */
    public function getSuggest()
    {
        return $this->_suggest;
    }
    
    /**
     * 获取资源库
     *
     * @return array
     */
    public function getRepositories()
    {
        return $this->_repositories;
    }
    
    /**
     * 获取额外数据
     *
     * @return mixed
     */
    public function getExtra()
    {
        return $this->_extra;
    }
    
    /**
     * 获取扩展额外配置数据
     *
     * @return array
     */
    public function getExtraConfig()
    {
        return $this->getExtra()[ConfigurationFinderInterface::EXTENSION_CONFIGURATION_KEY] ?? [];
    }
    
    /**
     * 获取所属应用，属于多个应用时，代表可以安装到这几个应用当中。
     *
     * @return array
     */
    public function getApp()
    {
        if (null === $this->_app) {
            $this->_app = (array)($this->getExtraConfig()['app'] ?? 'common');
            // 如果扩展所属应用包含公共应用，则该扩展直接被视为公共扩展
            if (in_array('common', $this->_app)) {
                $this->_app = ['common'];
            }
        }
        
        return $this->_app;
    }
    
    /**
     * 获取扩展依赖数据
     *
     * @return array
     */
    public function getExtensionDependencies()
    {
        $dependencies = [];
        $composerRequire = $this->getComposerDependencies();
        $extensionRequire = $this->getExtraConfig()['require'] ?? [];
        foreach ($extensionRequire as $app => $value) {
            // 只获取在可安装的应用列表中的依赖数据
            if (in_array($app, $this->getApp())) {
                foreach ($value as $uniqueName => $row) {
                    // "engine-core/theme-bootstrap-v3": "*"
                    if (is_string($row)) {
                        if (isset($composerRequire[$uniqueName])) {
                            $row = $composerRequire[$uniqueName]; // 以composer依赖的版本为准
                        }
                        $dependencies[$app][$uniqueName] = [
                            'app'     => $app,
                            'version' => $row,
                        ];
                    } else {
                        if (isset($composerRequire[$uniqueName])) {
                            $row['version'] = $composerRequire[$uniqueName]; // 以composer依赖的版本为准
                        }
                        /*
                         * "engine-core/theme-bootstrap-v3": {
                         *  "app": "backend",
                         * }
                         */
                        if (!isset($row['version'])) {
                            $row['version'] = '*'; // 没有指定版本，即任意版本均可
                        }
                        /*
                         * "engine-core/theme-bootstrap-v3": {
                         *  "version": "*",
                         * }
                         */
                        if (!isset($row['app'])) {
                            $row['app'] = $app;
                        }
                        $dependencies[$app][$uniqueName] = $row;
                    }
                }
            }
        }
        
        return $dependencies;
    }
    
    private
        $_vendorDir,
        $_name,
        $_description,
        $_version,
        $_type,
        $_keywords,
        $_homepage,
        $_authors,
        $_support,
        $_dependencies,
        $_devDependencies,
        $_suggest,
        $_autoloadPsr0,
        $_autoloadPsr4,
        $_repositories,
        $_extra,
        $_app;
    
}
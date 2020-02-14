<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\Ec;
use EngineCore\extension\ExtensionInfo;
use EngineCore\services\extension\repository\configuration\ConfigurationFinderInterface;
use EngineCore\services\Extension;
use EngineCore\helpers\StringHelper;
use EngineCore\base\Service;
use yii\base\InvalidConfigException;

/**
 * 扩展仓库管理服务类，主要管理所有扩展分类的本地和数据库数据
 *
 * @property ConfigurationFinderInterface $finder
 * @property array                        $aliases
 * @property array                        $localConfiguration
 * @property array                        $dbConfiguration
 * @property array                        $installed
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Repository extends Service implements RepositoryInterface
{
    
    /**
     * @var Extension 父级服务类
     */
    public $service;
    
    /**
     * 删除所有【扩展仓库、扩展配置】的缓存数据
     */
    public function clearCache()
    {
        $this->service->getControllerRepository()->clearCache();
        $this->service->getThemeRepository()->clearCache();
        $this->service->getModularityRepository()->clearCache();
    }
    
    /**
     * 生成所有扩展【系统扩展、开发者扩展】的别名配置信息
     *
     * @return array
     */
    public function getAliases(): array
    {
        $aliases = [];
        foreach ($this->getFinder()->getConfigFiles() as $uniqueName => $config) {
            $namespace = '@' . str_replace('\\', '/', rtrim($config['autoload']['psr-4']['namespace'], '\\'));
            $aliases[$namespace] = $config['autoload']['psr-4']['path'];
        }
        // 添加开发者目录别名
        foreach ($aliases as $alias => $path) {
            $aliases[StringHelper::replace($alias, 'extensions', 'developer')] = StringHelper::replace(
                $path, 'extensions', 'developer'
            );
        }
        
        return $aliases;
    }
    
    /**
     * 获取【所有】扩展的配置数据，以数据库信息为准
     *
     * @return array
     * ```php
     * [
     *  {uniqueName} => [
     *      'class' => {class}, // 主题扩展不存在该项
     *      'infoInstance' => {infoInstance},
     *      'data' => [], // 数据库配置数据
     *  ],
     * ]
     * ```
     */
    public function getLocalConfiguration(): array
    {
        return array_merge(
        // 已经安装的控制器扩展
            $this->service->getControllerRepository()->getLocalConfiguration(),
            // 已经安装的模块扩展
            $this->service->getModularityRepository()->getLocalConfiguration(),
            // 已经安装的主题扩展
            $this->service->getThemeRepository()->getLocalConfiguration()
        );
    }
    
    private $_finder;
    
    /**
     * 获取扩展配置文件搜索器
     *
     * @return ConfigurationFinderInterface
     * @throws InvalidConfigException 当搜索器未配置时抛出异常
     */
    public function getFinder()
    {
        if (null === $this->_finder) {
            throw new InvalidConfigException('The `finder` property must be set.');
        }
        $this->_finder;
    }
    
    /**
     * 设置扩展配置文件搜索器
     *
     * @param string|array|callable $finder
     *
     * @return self
     */
    public function setFinder($finder)
    {
        $this->_finder = Ec::createObject($finder, [], ConfigurationFinderInterface::class);
        
        return $this;
    }
    
    /**
     * 获取所有【已安装】的扩展的数据库配置数据，包括控制器、模块和主题扩展
     *
     * @return array
     * [
     *  {uniqueName} => [],
     * ]
     */
    public function getInstalled(): array
    {
        return array_merge(
        // 已经安装的控制器扩展
            $this->service->getControllerRepository()->getInstalled(),
            // 已经安装的模块扩展
            $this->service->getModularityRepository()->getInstalled(),
            // 已经安装的主题扩展
            $this->service->getThemeRepository()->getInstalled()
        );
    }
    
    /**
     * 获取所有【已安装】的扩展的配置数据
     *
     * @return array
     * ```php
     * [
     *  {uniqueName} => [
     *      'class' => {class}, // 主题扩展不存在该项
     *      'infoInstance' => {infoInstance},
     *      'data' => [], // 数据库配置数据
     *  ],
     * ]
     * ```
     */
    public function getDbConfiguration(): array
    {
        return array_merge(
        // 已经安装的控制器扩展
            $this->service->getControllerRepository()->getDbConfiguration(),
            // 已经安装的模块扩展
            $this->service->getModularityRepository()->getDbConfiguration(),
            // 已经安装的主题扩展
            $this->service->getThemeRepository()->getDbConfiguration()
        );
    }
    
    /**
     * 获取指定应用【所有|已安装】扩展的配置数据
     *
     * @param bool   $installed
     * @param string $app
     *
     * @return array
     * [
     *  {uniqueName} => [
     *      'class' => {class}, // 主题扩展不存在该项
     *      'infoInstance' => {infoInstance},
     *      'data' => [], // 数据库配置数据
     *  ],
     * ]
     */
    public function getConfigurationByApp($installed = false, $app = null): array
    {
        return array_merge(
        // 已经安装的控制器扩展
            $this->service->getControllerRepository()->getConfigurationByApp($installed, $app),
            // 已经安装的模块扩展
            $this->service->getModularityRepository()->getConfigurationByApp($installed, $app),
            // 已经安装的主题扩展
            $this->service->getThemeRepository()->getConfigurationByApp($installed, $app)
        );
    }
    
    /**
     * 获取【所有|已安装】扩展不同分类的列表数据
     *
     * @param bool $installed 默认获取【已安装】的扩展分类数据
     *
     * @return array
     * ```php
     * [
     *  {category} => [
     *      {extensionUniqueName},
     *      ...,
     *  ]
     * ]
     * ```
     */
    public function getListGroupByCategory($installed = true): array
    {
        $arr = [];
        $configuration = $installed ?
            $this->getDbConfiguration() :
            $this->getLocalConfiguration();
        foreach ($configuration as $uniqueName => $config) {
            /** @var ExtensionInfo $infoInstance */
            $infoInstance = $config['infoInstance'];
            $arr[$infoInstance->getCategory() ?: ExtensionInfo::CATEGORY_NONE][] = $uniqueName;
        }
        
        return $arr;
    }
    
    /**
     * 判断指定扩展分类是否存在【未安装|已安装】的扩展
     *
     * @param string $category 扩展分类
     * @param bool   $installed 默认获取【已安装】的扩展分类下是否存在指定分类的扩展
     *
     * @return bool
     */
    public function hasExtensionByCategory(string $category, $installed = true): bool
    {
        return isset($this->getListGroupByCategory($installed)[$category]);
    }
    
}
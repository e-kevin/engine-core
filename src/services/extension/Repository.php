<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\Ec;
use EngineCore\extension\repository\configuration\Configuration;
use EngineCore\extension\repository\info\ControllerInfo;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\extension\repository\configuration\ConfigurationFinderInterface;
use EngineCore\extension\repository\info\ModularityInfo;
use EngineCore\extension\repository\info\ThemeInfo;
use EngineCore\helpers\ArrayHelper;
use EngineCore\services\Extension;
use EngineCore\base\Service;
use yii\base\InvalidConfigException;

/**
 * 扩展仓库管理服务类，主要管理所有扩展分类的本地和数据库数据
 *
 * @property ConfigurationFinderInterface $finder
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
        Ec::$service->getSystem()->getCache()->getComponent()->delete([
            ConfigurationFinderInterface::CACHE_LOCAL_EXTENSION_CONFIGURATION,
            'all',
        ]);
        $this->_localConfiguration = $this->_dbConfiguration = $this->_configurationByApp = null;
        $this->service->getControllerRepository()->clearCache();
        $this->service->getThemeRepository()->clearCache();
        $this->service->getModularityRepository()->clearCache();
    }
    
    private $_localConfiguration;
    
    /**
     * {@inheritdoc}
     */
    public function getLocalConfiguration(): array
    {
        if (null === $this->_localConfiguration) {
            $this->_localConfiguration = Ec::$service->getSystem()->getCache()->getOrSet(
                [
                    ConfigurationFinderInterface::CACHE_LOCAL_EXTENSION_CONFIGURATION,
                    'all',
                ],
                function () {
                    $arr = [];
                    foreach ($this->service->getRepository()->getFinder()->getConfiguration() as $uniqueName =>
                             $configuration) {
                        foreach ($configuration->getApp() as $app) {
                            if (isset($this->getDbConfiguration()[$app][$uniqueName])) {
                                $config = $this->getDbConfiguration()[$app][$uniqueName][0];
                                $infoInstance = $this->getInfoInstance($uniqueName, $app);
                                switch (true) {
                                    case is_subclass_of($infoInstance, ControllerInfo::class):
                                        $this->service->getControllerRepository()->configureInfo($infoInstance, $config);
                                        break;
                                    case is_subclass_of($infoInstance, ModularityInfo::class):
                                        $this->service->getModularityRepository()->configureInfo($infoInstance, $config);
                                        break;
                                    case is_subclass_of($infoInstance, ThemeInfo::class):
                                        $this->service->getThemeRepository()->configureInfo($infoInstance, $config);
                                        break;
                                }
                                $arr[$app][$uniqueName] = $infoInstance;
                            } else {
                                $arr[$app][$uniqueName] = $this->getInfoInstance($uniqueName, $app);
                            }
                        }
                    }
                    
                    return $arr;
                }, $this->getCacheDuration());
        }
        
        return $this->_localConfiguration;
    }
    
    private $_dbConfiguration;
    
    /**
     * {@inheritdoc}
     */
    public function getDbConfiguration(): array
    {
        if (null == $this->_dbConfiguration) {
            $this->_dbConfiguration = ArrayHelper::merge(
            // 已经安装的控制器扩展
                $this->service->getControllerRepository()->getDbConfiguration(),
                // 已经安装的模块扩展
                $this->service->getModularityRepository()->getDbConfiguration(),
                // 已经安装的主题扩展
                $this->service->getThemeRepository()->getDbConfiguration()
            );
        }
        
        return $this->_dbConfiguration;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getInstalledConfiguration(): array
    {
        return ArrayHelper::merge(
        // 已经安装的控制器扩展
            $this->service->getControllerRepository()->getInstalledConfiguration(),
            // 已经安装的模块扩展
            $this->service->getModularityRepository()->getInstalledConfiguration(),
            // 已经安装的主题扩展
            $this->service->getThemeRepository()->getInstalledConfiguration()
        );
    }
    
    private $_configurationByApp;
    
    /**
     * {@inheritdoc}
     */
    public function getConfigurationByApp($installed = false, $app = null)
    {
        if (!isset($this->_configurationByApp[$installed][$app])) {
            $this->_configurationByApp[$installed][$app] = array_merge(
            // 已经安装的控制器扩展
                $this->service->getControllerRepository()->getConfigurationByApp($installed, $app),
                // 已经安装的模块扩展
                $this->service->getModularityRepository()->getConfigurationByApp($installed, $app),
                // 已经安装的主题扩展
                $this->service->getThemeRepository()->getConfigurationByApp($installed, $app)
            );
        }
        
        return $this->_configurationByApp[$installed][$app];
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
        
        return $this->_finder;
    }
    
    /**
     * 设置扩展配置文件搜索器
     *
     * @param string|array|callable $finder
     */
    public function setFinder($finder)
    {
        $this->_finder = Ec::createObject($finder, [], ConfigurationFinderInterface::class);
    }
    
    /**
     * 获取指定的扩展信息类
     *
     * @param string $uniqueName
     * @param string $app
     *
     * @return ExtensionInfo|null|object
     */
    public function getInfoInstance($uniqueName, $app)
    {
        $configuration = $this->getConfiguration($uniqueName, false);
        if (null === $configuration) {
            return null;
        }
        try {
            // 多个命名空间，默认把第一个视为主命名空间
            $autoload = $configuration->autoloadPsr4[0] ?? [];
            $infoInstance = Ec::createObject([
                'class' => $autoload['namespace'] . 'Info', // 扩展信息类
            ], [
                $app,
                $configuration->getName(),
            ], ExtensionInfo::class);
        } catch (\Exception $e) {
            $infoInstance = null;
        }
        
        return $infoInstance;
    }
    
    /**
     * 获取指定扩展的配置文件的配置数据
     *
     * @param string $uniqueName
     * @param bool   $throwException
     *
     * @return Configuration|null
     * @throws InvalidConfigException
     */
    public function getConfiguration($uniqueName, $throwException = true)
    {
        if (!isset($this->getFinder()->getConfiguration()[$uniqueName])) {
            if ($throwException) {
                throw new InvalidConfigException('The repository `' . $uniqueName . '` is not found.');
            } else {
                return null;
            }
        }
        
        return $this->getFinder()->getConfiguration()[$uniqueName];
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
     *      {uniqueName},
     *      ...,
     *  ]
     * ]
     * ```
     */
    public function getListGroupByCategory($installed = true): array
    {
        $arr = [];
        $configuration = $installed ? $this->getInstalledConfiguration() : $this->getLocalConfiguration();
        foreach ($configuration as $app => $row) {
            /** @var ExtensionInfo $infoInstance */
            foreach ($row as $uniqueName => $infoInstance) {
                $arr[$infoInstance->getCategory()][] = $uniqueName;
            }
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
    public function existsByCategory(string $category, $installed = true): bool
    {
        return isset($this->getListGroupByCategory($installed)[$category]);
    }
    
}
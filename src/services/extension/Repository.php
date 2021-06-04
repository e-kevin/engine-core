<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\Ec;
use EngineCore\enums\StatusEnum;
use EngineCore\extension\repository\configuration\Configuration;
use EngineCore\extension\repository\info\ConfigInfo;
use EngineCore\extension\repository\info\ControllerInfo;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\extension\repository\configuration\ConfigurationFinderInterface;
use EngineCore\extension\repository\info\ModularityInfo;
use EngineCore\extension\repository\info\ThemeInfo;
use EngineCore\helpers\ArrayHelper;
use EngineCore\services\Extension;
use EngineCore\base\Service;
use Yii;
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
        $this->service->getModularityRepository()->clearCache();
        $this->service->getControllerRepository()->clearCache();
        $this->service->getThemeRepository()->clearCache();
        $this->service->getConfigRepository()->clearCache();
        $this->_localConfiguration = $this->_dbConfiguration = $this->_configurationByApp = $this->_listGroupByCategory = null;
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
                            $infoInstance = $this->getInfoInstance($uniqueName, $app);
                            if (isset($this->getDbConfiguration()[$app][$uniqueName])) {
                                // 目前系统只允许同一个扩展在同一个应用里安装一次，故取第一条数据即可
                                $config = $this->getDbConfiguration()[$app][$uniqueName][0];
                                $data = [];
                                // 配置扩展信息类的属性，用于同步数据库里的数据到信息类里
                                switch (true) {
                                    case is_subclass_of($infoInstance, ControllerInfo::class):
                                        $data = [
                                            'id'       => $config['controller_id'],
                                            'moduleId' => $config['module_id'],
                                        ];
                                        break;
                                    case is_subclass_of($infoInstance, ModularityInfo::class):
                                        $data = [
                                            'id'        => $config['module_id'],
                                            'bootstrap' => $config['bootstrap'],
                                        ];
                                        break;
                                    case is_subclass_of($infoInstance, ThemeInfo::class):
                                        $data = [
                                            'id' => $config['theme_id'],
                                        ];
                                        break;
                                    case is_subclass_of($infoInstance, ConfigInfo::class):
                                        break;
                                }
                                Yii::configure($infoInstance, $data);
                            }
                            $arr[$app][$uniqueName] = $infoInstance;
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
                $this->service->getThemeRepository()->getDbConfiguration(),
                // 已经安装的系统配置扩展
                $this->service->getConfigRepository()->getDbConfiguration()
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
            $this->service->getThemeRepository()->getInstalledConfiguration(),
            // 已经安装的系统配置扩展
            $this->service->getConfigRepository()->getInstalledConfiguration()
        );
    }
    
    /**
     * 获取已安装扩展的信息类数据，并按底层依赖关系排序
     *
     * @return array
     * ```php
     * [
     *      {uniqueName} => ['backend', 'frontend', ...]
     * ]
     * ```
     */
    public function getInstalledExtension(): array
    {
        // 已安装扩展的数据库数据
        $data = [];
        if ($this->service->getRepository()->hasModel()) {
            $data = array_merge(
                $this->service->getThemeRepository()->getModel()->getAll(),
                $this->service->getModularityRepository()->getModel()->getAll(),
                $this->service->getControllerRepository()->getModel()->getAll(),
                $this->service->getConfigRepository()->getModel()->getAll()
            );
        }
        if (empty($data)) {
            return [];
        }
        
        $localConfiguration = $this->getLocalConfiguration(); // 本地所有扩展的配置数据
        $arr = [];
        $index = ArrayHelper::index($data, 'unique_name', 'app');
        foreach ($data as $row) {
            $app = $row['app'];
            $uniqueName = $row['unique_name'];
            // 只获取已经安装且本地存在配置信息的扩展
            if (isset($localConfiguration[$app][$uniqueName])) {
                /** @var ExtensionInfo $infoInstance */
                $infoInstance = $localConfiguration[$app][$uniqueName];
                // 只获取激活的主题扩展
                if ($infoInstance->getType() === $infoInstance::TYPE_THEME
                    && $index[$app][$uniqueName]['status'] == StatusEnum::STATUS_OFF
                ) {
                    continue;
                }
                $arr[$uniqueName]['app'][] = $app;
            }
        }
        $arr = $arr ? $this->service->getDependent()->sort($arr) : [];
        $hasTheme = false;
        foreach ($arr as $uniqueName => $row) {
            unset($arr[$uniqueName]);
            foreach ($row['app'] as $app) {
                /** @var ExtensionInfo $infoInstance */
                $infoInstance = $localConfiguration[$app][$uniqueName];
                // 只获取一个主题扩展
                if ($infoInstance->getType() === $infoInstance::TYPE_THEME) {
                    if ($hasTheme) {
                        continue;
                    }
                    $hasTheme = true;
                }
                $arr[$uniqueName][] = $app;
            }
        }
        
        return $arr;
    }
    
    private $_configurationByApp;
    
    /**
     * {@inheritdoc}
     * todo 改用ArrayHelper::filter()?
     */
    public function getConfigurationByApp($installed = false, $app = null)
    {
        $app = $app ?: Yii::$app->id;
        if (!isset($this->_configurationByApp[$installed][$app])) {
            $this->_configurationByApp[$installed][$app] = array_merge(
            // 已经安装的控制器扩展
                $this->service->getControllerRepository()->getConfigurationByApp($installed, $app),
                // 已经安装的模块扩展
                $this->service->getModularityRepository()->getConfigurationByApp($installed, $app),
                // 已经安装的主题扩展
                $this->service->getThemeRepository()->getConfigurationByApp($installed, $app),
                // 已经安装的系统配置扩展
                $this->service->getConfigRepository()->getConfigurationByApp($installed, $app)
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
     * 获取指定扩展的信息类
     *
     * @param string $uniqueName
     * @param string $app
     *
     * @return ExtensionInfo|null|object
     */
    public function getInfoInstance(string $uniqueName, string $app)
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
    
    private $_listGroupByCategory;
    
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
        if (!isset($this->_listGroupByCategory[$installed])) {
            $this->_listGroupByCategory[$installed] = [];
            $configuration = $installed ? $this->getInstalledConfiguration() : $this->getLocalConfiguration();
            foreach ($configuration as $app => $row) {
                /** @var ExtensionInfo $infoInstance */
                foreach ($row as $uniqueName => $infoInstance) {
                    if (!isset($this->_listGroupByCategory[$installed][$infoInstance->getCategory()])) {
                        $this->_listGroupByCategory[$installed][$infoInstance->getCategory()][] = $uniqueName;
                    } elseif (!in_array($uniqueName, $this->_listGroupByCategory[$installed][$infoInstance->getCategory()])) {
                        $this->_listGroupByCategory[$installed][$infoInstance->getCategory()][] = $uniqueName;
                    }
                }
            }
        }
        
        return $this->_listGroupByCategory[$installed];
    }
    
    /**
     * 判断指定扩展分类是否存在【未安装|已安装】的扩展
     *
     * @param string $category  扩展分类
     * @param bool   $installed 默认获取【已安装】的扩展分类下是否存在指定分类的扩展
     *
     * @return bool
     */
    public function existsByCategory(string $category, $installed = true): bool
    {
        return isset($this->getListGroupByCategory($installed)[$category]);
    }
    
    /**
     * 判断是否已经设置了扩展模型类
     *
     * @return bool
     */
    public function hasModel(): bool
    {
        return $this->service->getModularityRepository()->hasModel()
            && $this->service->getThemeRepository()->hasModel()
            && $this->service->getControllerRepository()->hasModel()
            && $this->service->getConfigRepository()->hasModel();
    }
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\base\Service;
use EngineCore\extension\ControllerInfo;
use EngineCore\extension\ExtensionInfo;
use EngineCore\extension\ModularityInfo;
use EngineCore\extension\repository\CategoryRepositoryInterface;
use EngineCore\extension\ThemeInfo;
use EngineCore\helpers\ArrayHelper;
use EngineCore\services\Extension;
use Yii;

/**
 * 分类扩展仓库抽象类
 *
 * @property CategoryRepositoryInterface $repository
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
abstract class BaseCategoryRepository extends Service implements RepositoryInterface
{
    
    /**
     * @var Extension 父级服务类
     */
    public $service;
    
    /**
     * @var string 扩展信息类
     */
    protected $extensionInfo;
    
    /**
     * 获取本地所有【未安装、已安装】的配置数据，以数据库信息为准
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
        return $this->service->getCache()->getOrSet(
            RepositoryInterface::CACHE_LOCAL_EXTENSION_CONFIGURATION_PREFIX . '_' . $this->extensionInfo,
            function () {
                $config = [];
                foreach ($this->service->getRepository()->getFinder()->getConfigFiles() as $uniqueName => $row) {
                    $namespace = $row['autoload']['psr-4']['namespace'];
                    $realPath = $row['autoload']['psr-4']['path'];
                    $uniqueId = ArrayHelper::remove($row, 'id');
                    $version = ArrayHelper::remove($row, 'version');
                    unset($row['autoload']);
                    // 扩展信息类
                    $infoClass = $namespace . 'Info';
                    // 扩展信息类配置
                    $arr[$uniqueName]['infoConfig'] = ['class' => $infoClass];
                    // 扩展信息类构造函数配置
                    $arr[$uniqueName]['infoParams'] = [
                        $uniqueId,
                        $uniqueName,
                        $version,
                    ];
                    // 扩展命名空间
                    $arr[$uniqueName]['namespace'] = $namespace;
                    // 初始化信息类
                    if (is_subclass_of($infoClass, ControllerInfo::class)) {
                        $class = '\\' . $namespace . 'Controller';
                        if (!class_exists($class)) {
                            continue;
                        }
                        $arr[$uniqueName]['infoParams'][] = $realPath . DIRECTORY_SEPARATOR . 'migrations';
                        $config[$uniqueName] = $this->createControllerInfo($uniqueName, $arr, $class);
                    } elseif (is_subclass_of($infoClass, ModularityInfo::class)) {
                        $class = '\\' . $namespace . 'Module';
                        if (!class_exists($class)) {
                            continue;
                        }
                        $arr[$uniqueName]['infoParams'][] = $realPath . DIRECTORY_SEPARATOR . 'migrations';
                        $config[$uniqueName] = $this->createModularityInfo($uniqueName, $arr, $class);
                    } elseif (is_subclass_of($infoClass, ThemeInfo::class)) {
                        $arr[$uniqueName]['infoParams'][] = str_replace('\\', '/', '@' . $namespace . 'views');
                        $config[$uniqueName] = $this->createThemeInfo($uniqueName, $arr);
                    } else {
                        continue;
                    }
                }
                
                return $config;
            }, $this->getCacheDuration());
    }
    
    /**
     * 创建控制器扩展信息类
     *
     * @param string $uniqueName
     * @param array  $config
     * @param string $class
     *
     * @return array
     */
    protected function createControllerInfo($uniqueName, $config, $class)
    {
        // 初始化扩展详情类
        /** @var ControllerInfo $infoInstance */
        $infoInstance = Yii::createObject($config['infoConfig'], $config['infoParams']);
        // 以数据库信息为准
        if ($data = $this->getInstalled()[$uniqueName] ?? []) {
            // 根据数据库数据自定义参数赋值
            $infoInstance->id = $data['controller_id'];
            $infoInstance->setModuleId($data['module_id']);
            $infoInstance->canUninstall = !$infoInstance->isSystem && !$data['is_system'];
            $infoInstance->canInstall = false;
        } else {
            $infoInstance->canInstall = true;
            $infoInstance->canUninstall = false;
        }
        
        return [
            'class'        => $class,
            'infoInstance' => $infoInstance,
            'data'         => $data,
        ];
    }
    
    /**
     * 创建模块扩展信息类
     *
     * @param string $uniqueName
     * @param array  $config
     * @param string $class
     *
     * @return array
     */
    protected function createModularityInfo($uniqueName, $config, $class)
    {
        // 初始化扩展详情类
        /** @var ModularityInfo $infoInstance */
        $infoInstance = Yii::createObject($config['infoConfig'], $config['infoParams']);
        // 以数据库信息为准
        if ($data = $this->getInstalled()[$uniqueName] ?? []) {
            // 根据数据库数据自定义参数赋值
            $infoInstance->id = $data['module_id'];
            $infoInstance->canUninstall = !$infoInstance->isSystem && !$data['is_system'];
            $infoInstance->canInstall = false;
        } else {
            $infoInstance->canInstall = true;
            $infoInstance->canUninstall = false;
        }
        
        return [
            'class'        => $class,
            'infoInstance' => $infoInstance,
            'data'         => $data,
        ];
    }
    
    /**
     * 创建主题扩展信息类
     *
     * @param string $uniqueName
     * @param array  $config
     *
     * @return array
     */
    protected function createThemeInfo($uniqueName, $config)
    {
        // 初始化扩展详情类
        /** @var ThemeInfo $infoInstance */
        $infoInstance = Yii::createObject($config['infoConfig'], $config['infoParams']);
        // 以数据库信息为准
        if ($data = $this->getInstalled()[$uniqueName] ?? []) {
            // 根据数据库数据自定义参数赋值
            $infoInstance->canUninstall = !$infoInstance->isSystem && !$data['is_system'];
            $infoInstance->canInstall = false;
        } else {
            $infoInstance->canInstall = true;
            $infoInstance->canUninstall = false;
        }
        
        return [
            'infoInstance' => $infoInstance,
            'data'         => $data,
        ];
    }
    
    private $_dbConfiguration;
    
    /**
     * 获取【已安装】的扩展的配置数据
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
        if (null === $this->_dbConfiguration) {
            foreach ($this->getLocalConfiguration() as $uniqueName => $config) {
                if (isset($this->getInstalled()[$uniqueName])) {
                    $this->_dbConfiguration[$uniqueName] = $config;
                }
            }
        }
        
        return $this->_dbConfiguration ?: [];
    }
    
    /**
     * 删除缓存
     */
    public function clearCache()
    {
        $this->service->getCache()->delete(RepositoryInterface::CACHE_LOCAL_EXTENSION_CONFIGURATION_PREFIX
            . '_' . $this->extensionInfo);
        $this->_dbConfiguration = null;
    }
    
    /**
     * 获取指定应用【所有|已安装】扩展的配置数据
     *
     * @param bool   $installed 默认获取【所有】数据
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
        $arr = [];
        $configuration = $installed ?
            $this->getDbConfiguration() :
            $this->getLocalConfiguration();
        $app = $app ?: Yii::$app->id;
        foreach ($configuration as $uniqueName => $config) {
            /** @var ExtensionInfo $infoInstance */
            $infoInstance = $config['infoInstance'];
            if (is_subclass_of($infoInstance, $this->extensionInfo) && $app == $infoInstance->app) {
                $arr[$uniqueName] = $config;
            }
        }
        
        return $arr;
    }
    
    /**
     * 获取【已安装】的扩展的数据库配置数据
     *
     * @return array
     * [
     *  {uniqueName} => [],
     * ]
     */
    public function getInstalled(): array
    {
        return $this->getRepository() ? $this->getRepository()->getAll() : [];
    }
    
    /**
     * 获取扩展仓库，主要由该仓库处理一些和数据库结构相关的业务逻辑
     *
     * @return CategoryRepositoryInterface
     */
    abstract public function getRepository(): CategoryRepositoryInterface;
    
    /**
     * 设置扩展仓库
     *
     * @param string|array|callable $config
     */
    abstract public function setRepository($config);
    
}
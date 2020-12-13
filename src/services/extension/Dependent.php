<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\Ec;
use EngineCore\services\Extension;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\base\Service;
use PackageVersions\Versions;
use Yii;

/**
 * 扩展依赖服务类
 *
 * @property array $definitions 扩展依赖定义
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Dependent extends Service
{
    
    /**
     * @var Extension 父级服务类
     */
    public $service;
    
    /**
     * {@inheritdoc}
     */
    public function clearCache()
    {
        $this->_definitions = null;
    }
    
    /**
     * @var array 本地所有扩展依赖定义
     */
    private $_definitions;
    
    /**
     * 获取本地所有扩展依赖定义
     *
     * @return array
     */
    public function getDefinitions(): array
    {
        if (null === $this->_definitions) {
            $this->_definitions = [];
            $composerList = $this->service->getRepository()->getFinder()->readInstalledFile(Yii::getAlias('@vendor/composer/installed.json'));
            $configuration = $this->service->getRepository()->getFinder()->getConfiguration();
            foreach ($configuration as $config) {
                $definition = [
                    'name'                  => $config->getName(),
                    'version'               => $config->getVersion(),
                    'description'           => $config->getDescription(),
                    'app'                   => $config->getApp(),
                    'extensionDependencies' => [],
                    'composerDependencies'  => [],
                ];
                // 扩展依赖关系
                foreach ($config->getExtensionDependencies() as $app => $v) {
                    foreach ($v as $uniqueName => $row) {
                        $arr = [
                            'name'           => $uniqueName,
                            'description'    => 'N/A',
                            'localVersion'   => 'N/A',
                            'requireVersion' => $row['version'],
                            'requireApp'     => $row['app'], // 为数组时，则要求所依赖的扩展需要同时被多个应用所安装
                            'downloaded'     => false,
                            'installed'      => false,
                        ];
                        // 本地存在扩展
                        if (isset($configuration[$uniqueName])) {
                            $currentConfig = $configuration[$uniqueName];
                            $arr['downloaded'] = true;
                            $arr['localVersion'] = $currentConfig->getVersion();
                            $arr['description'] = $currentConfig->getDescription();
                            foreach ((array)$row['app'] as $requireApp) {
                                // 验证所请求的应用是否有效，无效则过滤该依赖
                                if (in_array($requireApp, $currentConfig->getApp())) {
                                    $arr['installed'] = isset($this->service->getRepository()->getDbConfiguration()[$requireApp][$uniqueName]);
                                    $arr['requireApp'] = $requireApp;
                                    $definition['extensionDependencies'][$app][] = $arr;
                                }
                            }
                        } else {
                            $definition['extensionDependencies'][$app][] = $arr;
                        }
                    }
                }
                // composer依赖关系
                foreach ($config->getComposerDependencies() as $uniqueName => $version) {
                    $arr = [
                        'name'           => $uniqueName,
                        'description'    => 'N/A',
                        'localVersion'   => 'N/A',
                        'requireVersion' => $version,
                        'installed'      => false,
                    ];
                    if (isset($composerList[$uniqueName])) {
                        $arr['localVersion'] = $composerList[$uniqueName]['version'];
                        $arr['description'] = $composerList[$uniqueName]['description'];
                        $arr['installed'] = true;
                    }
                    $definition['composerDependencies'][$uniqueName] = $arr;
                }
                
                $this->_definitions[$config->getName()] = $definition;
            }
        }
        
        return $this->_definitions;
    }
    
    /**
     * 标准化扩展数据
     *
     * @param array $extensions
     *
     * @return array
     * ```php
     * [
     *  {$uniqueName} => [
     *          'version' => '~1.0',
     *          'app' => ['backend', 'frontend']
     *      ]
     * ]
     * ```
     */
    public function normalize(array $extensions): array
    {
        foreach ($extensions as $uniqueName => &$row) {
            if (is_string($row)) {
                $row = [
                    'version' => $row,
                ];
            } else {
                if (!isset($row['version'])) {
                    $row['version'] = '*';
                }
                if (isset($row['app'])) {
                    if (is_string($row['app'])) {
                        $row['app'] = [$row['app']];
                    }
                    if (in_array('common', $row['app'])) {
                        $row['app'] = ['common'];
                    }
                }
            }
        }
        
        return $extensions;
    }
    
    /**
     * 检测指定应用下指定扩展是否满足所需的依赖关系
     *
     * @param string $uniqueName 扩展名称
     * @param string $app 应用ID
     * @param bool   $verifyInstalled 是否验证扩展是否已经安装，默认验证安装状态
     *
     * @return bool
     */
    public function checkDependencies(string $uniqueName, $app = null, bool $verifyInstalled = true): bool
    {
        if (!isset($this->service->getRepository()->getFinder()->getConfiguration()[$uniqueName])) {
            $this->_info = "不存在扩展：`{$uniqueName}`";
            
            return $this->_status = false;
        }
        
        $dependencies = $this->service->getRepository()->getFinder()->getConfiguration()[$uniqueName]->getExtensionDependencies();
        $this->getDependenciesStatus($dependencies[$app ?: Yii::$app->id] ?? [], $uniqueName, $verifyInstalled);
        
        return $this->_status;
    }
    
    /**
     *  获取指定扩展的依赖状态
     *
     * @param array  $extensions 扩展数据
     * ```php
     * [
     *  'engine-core/theme-bootstrap-v3' => [
     *      'version' => '*',
     *      'app' => 'backend', // ['backend', 'frontend']
     *  ]
     * ]
     * ```
     * @param string $parent 上级扩展名称
     * @param bool   $verifyInstalled 是否验证扩展是否已经安装，默认验证安装状态
     *
     * @return array
     * ```php
     * [
     * 'download'  => [], // 提示下载扩展
     * 'conflict'  => [], // 提示扩展版本冲突
     * 'uninstall' => [], // 提示需要安装的扩展
     * 'circular'  => [], // 无限循环依赖的扩展
     * 'passed'    => [], // 通过依赖检测的扩展
     * ]
     * ```
     */
    public function getDependenciesStatus(array $extensions, string $parent, bool $verifyInstalled = true): array
    {
        $this->_data = [
            'download'  => [], // 提示下载扩展
            'conflict'  => [], // 提示扩展版本冲突
            'uninstall' => [], // 提示需要安装的扩展
            'circular'  => [], // 无限循环依赖的扩展
            'passed'    => [], // 通过依赖检测的扩展
            'parent'    => [], // 上级扩展名称
        ];
        
        $this->detectDependenciesStatus($extensions, $parent, $verifyInstalled);
        
        if (
            !empty($this->_data['download'])
            || !empty($this->_data['conflict'])
            || !empty($this->_data['circular'])
            || ($verifyInstalled && !empty($this->_data['uninstall']))
        ) {
            $this->_info = '请先满足扩展依赖关系再执行当前操作。';
            $this->_status = false;
        } else {
            $this->_status = true;
        }
        unset($this->_data['parent']);
        
        return $this->_data;
    }
    
    /**
     *  检测扩展依赖状态
     *
     * @param array  $extensions 扩展数据
     * @param string $parent 上级扩展名称
     * @param bool   $verifyInstalled 是否验证扩展是否已经安装
     */
    protected function detectDependenciesStatus(array $extensions, string $parent, bool $verifyInstalled)
    {
        $extensions = $this->normalize($extensions);
        $configuration = $this->service->getRepository()->getFinder()->getConfiguration();
        $localConfiguration = $this->service->getRepository()->getLocalConfiguration();
//        if (!in_array($parent, $this->_data['parent'])) {
//            $this->_data['parent'][] = $parent;
//        }
        
        foreach ($extensions as $uniqueName => $row) {
            $requireVersion = $row['version'];
            $extension = $uniqueName . ':' . $requireVersion;
            // 需要下载的扩展不检测依赖关系
            if (in_array($extension, $this->_data['download'])) {
                continue;
            }
            // 本地存在扩展
            if (isset($configuration[$uniqueName])) {
                $exists = $configuration[$uniqueName];
                
                // 指定在哪个app里安装扩展
                if (isset($row['app'])) {
                    $row['app'] = array_intersect($exists->getApp(), $row['app']);
                    // 没有有效app，则默认在所有有效的app里安装扩展
                    if (empty($row['app'])) {
                        $row['app'] = $exists->getApp();
                    }
                } // 没有指定app，则默认在所有有效的app里安装扩展
                else {
                    $row['app'] = $exists->getApp();
                }
                
                // 检查扩展依赖关系
                foreach ($row['app'] as $app) {
                    // 检测扩展是否需要安装
                    if ($verifyInstalled &&
                        !isset($this->service->getRepository()->getDbConfiguration()[$app][$uniqueName])
                        && !in_array($uniqueName, $this->_data['uninstall'][$app] ?? [])
                    ) {
                        $this->_data['uninstall'][$app][] = $uniqueName;
                    }
                    
                    // 扩展没有通过检测
                    if (!isset($this->_data['passed'][$app][$uniqueName])) {
                        // 版本不符合则提示需要解决版本冲突
                        if (!Ec::$service->getSystem()->getVersion()->compare($exists->getVersion(), $requireVersion)) {
                            $this->_data['conflict'][$uniqueName]['localVersion'] = $exists->getVersion();
                            $this->_data['conflict'][$uniqueName]['requireVersion'][$parent] = $requireVersion;
                        } // 版本一致
                        else {
                            $this->_data['passed'][$app][$uniqueName] = false;
                            if (isset($exists->getExtensionDependencies()[$app])) {
                                $this->detectDependenciesStatus($exists->getExtensionDependencies()[$app], $uniqueName, $verifyInstalled);
                            }
                            unset($this->_data['passed'][$app][$uniqueName]); // 确保被依赖的扩展在前
                            if ($verifyInstalled && !in_array($uniqueName, $this->_data['uninstall'][$app] ?? [])) {
                                /** @var ExtensionInfo $infoInstance */
                                $infoInstance = $localConfiguration[$app][$uniqueName];
                                $this->_data['passed'][$app][$uniqueName] = $infoInstance;
                            }
                        }
                    } // 通过检测的扩展是否存在循环依赖
                    elseif (false === $this->_data['passed'][$app][$uniqueName]) {
                        $this->_data['circular'][$uniqueName] = $this->composeCircularDependencyTrace($uniqueName, $app);
                    } else {
                        // 版本不符合则提示需要解决版本冲突
                        if (!Ec::$service->getSystem()->getVersion()->compare($exists->getVersion(), $requireVersion)) {
                            $this->_data['conflict'][$uniqueName]['localVersion'] = $exists->getVersion();
                            $this->_data['conflict'][$uniqueName]['requireVersion'][$parent] = $requireVersion;
                        }
                    }
                }
            } // 本地不存在扩展
            else {
                if (!isset(Versions::VERSIONS[$uniqueName])) {
                    $this->_data['download'][] = $extension;
                }
            }
        }
    }
    
    /**
     * 组成扩展依赖关系内循环跟踪信息
     *
     * @param string  $circularDependencyName 内循环扩展名称
     * @param  string $app
     *
     * @return string
     */
    protected function composeCircularDependencyTrace($circularDependencyName, $app): string
    {
        $dependencyTrace = [];
        $startFound = false;
        foreach ($this->_data['passed'][$app] as $uniqueName => $value) {
            if ($uniqueName === $circularDependencyName) {
                $startFound = true;
            }
            if ($startFound && $value === false) {
                $dependencyTrace[] = $uniqueName;
            }
        }
        $dependencyTrace[] = $circularDependencyName;
        
        return implode(' -> ', $dependencyTrace);
    }
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\Ec;
use EngineCore\services\Extension;
use EngineCore\extension\ExtensionInfo;
use EngineCore\base\Service;
use OutOfBoundsException;
use PackageVersions\Versions;
use yii\base\InvalidConfigException;

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
     * @inheritdoc
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
            $configuration = $this->service->getLocal()->getConfiguration();
            foreach ($configuration as $uniqueName => $config) {
                /** @var ExtensionInfo $infoInstance */
                $infoInstance = $config['infoInstance'];
                $this->_definitions[$uniqueName] = [
                    'id'          => $infoInstance->getUniqueId(),
                    'name'        => $infoInstance->getName(),
                    'version'     => $infoInstance->getVersion(),
                    'description' => $infoInstance->getDescription(),
                    'depends'     => $infoInstance->getDepends(),
                    'composer'    => $infoInstance->getRequirePackages(),
                ];
                // 系统扩展
                foreach ($this->_definitions[$uniqueName]['depends'] as $name => $version) {
                    $this->_definitions[$uniqueName]['depends'][$name] = [
                        'downloaded'     => isset($configuration[$name]),
                        'requireVersion' => $version,
                    ];
                    if (isset($configuration[$name])) {
                        /** @var ExtensionInfo $infoInstance */
                        $infoInstance = $configuration[$name]['infoInstance'];
                        $this->_definitions[$uniqueName]['depends'][$name]['localVersion'] = $infoInstance->getVersion();
                        $this->_definitions[$uniqueName]['depends'][$name]['description'] = $infoInstance->getDescription();
                        $this->_definitions[$uniqueName]['depends'][$name]['name'] = $infoInstance->getName();
                    } else {
                        $this->_definitions[$uniqueName]['depends'][$name]['localVersion'] = 'N/A';
                        $this->_definitions[$uniqueName]['depends'][$name]['description'] = 'N/A';
                        $this->_definitions[$uniqueName]['depends'][$name]['name'] = 'N/A';
                    }
                    $this->_definitions[$uniqueName]['depends'][$name]['installed']
                        = isset($this->service->getDb()->getInstalled()[$name]);
                }
                // composer扩展
                foreach ($this->_definitions[$uniqueName]['composer'] as $name => $version) {
                    try {
                        $localVersion = Versions::getVersion($name);
                    } catch (OutOfBoundsException $e) {
                        $localVersion = [];
                    }
                    if (!empty($localVersion)) {
                        list($localVersion, $branch) = explode('@', $localVersion);
                        if (preg_match('{^[vV]}', $localVersion)) {
                            $localVersion = substr($localVersion, 1);
                        }
                        $this->_definitions[$uniqueName]['composer'][$name] = [
                            'requireVersion' => $version,
                            'localVersion'   => $localVersion,
                            'installed'      => true,
                        ];
                    } else {
                        $this->_definitions[$uniqueName]['composer'][$name] = [
                            'requireVersion' => $version,
                            'localVersion'   => 'N/A',
                            'installed'      => false,
                        ];
                    }
                }
            }
        }
        
        return $this->_definitions;
    }
    
    /**
     * 获取指定扩展的依赖关系列表
     *
     * @param string $extension 扩展名称
     *
     * @return array
     */
    public function getDependList(string $extension): array
    {
        return $this->getDefinitions()[$extension]['depends'] ?? [];
    }
    
    /**
     * 获取指定扩展的composer依赖关系列表
     *
     * @param string $extension 扩展名称
     *
     * @return array
     */
    public function getComposerList(string $extension): array
    {
        return $this->getDefinitions()[$extension]['composer'] ?? [];
    }
    
    /**
     * 检测指定扩展是否满足所需的依赖关系
     *
     * @param string $extension 待检测的扩展名称
     *
     * @return bool
     * @throws InvalidConfigException
     */
    public function checkDependencies($extension): bool
    {
        $configuration = $this->service->getLocal()->getConfiguration();
        if (!isset($configuration[$extension])) {
            throw new InvalidConfigException("扩展 `{$extension}` 不存在");
        }
        /** @var ExtensionInfo $infoInstance */
        $infoInstance = $configuration[$extension]['infoInstance'];
        
        return $this->getDependenciesStatus($infoInstance->getDepends(), $configuration, $extension);
    }
    
    /**
     *  获取指定扩展的依赖状态
     *
     * @param array  $depends 扩展的依赖关系数据
     * @param array  $list 本地所有已经下载的扩展列表数据
     * @param string $parent 上级扩展名称
     * @param bool   $verifyInstalled 是否验证扩展是否已经安装，默认验证安装状态
     *
     * @return bool
     */
    public function getDependenciesStatus(array $depends, array $list, string $parent, bool $verifyInstalled = true
    ): bool {
        $this->_data = [
            'download'  => [], // 提示下载扩展
            'conflict'  => [], // 提示扩展版本冲突
            'uninstall' => [], // 提示需要安装的扩展
            'passed'    => [], // 通过依赖检测的扩展
            'circular'  => [], // 循环依赖
        ];
        $this->detectDependenciesStatus($depends, $list, $parent, $verifyInstalled);
        
        if (!empty($this->_data['download']) || !empty($this->_data['conflict']) || !empty($this->_data['uninstall'])
            || !empty($this->_data['circular'])) {
            $this->_info = '请先满足扩展依赖关系再执行当前操作。';
            
            return $this->_status = false;
        } else {
            return $this->_status = true;
        }
    }
    
    /**
     *  检测扩展依赖状态
     *
     * @param array  $depends 扩展的依赖关系数据
     * @param array  $list 本地所有已经下载的扩展列表数据
     * @param string $parent 上级扩展名称
     * @param bool   $verifyInstalled 是否验证扩展是否已经安装，默认验证安装状态
     */
    protected function detectDependenciesStatus(
        array $depends, array $list, string $parent, bool $verifyInstalled = true
    ) {
        foreach ($depends as $uniqueName => $version) {
            $extension = $uniqueName . ':' . $version;
            // 需要下载的扩展不检测依赖关系
            if (in_array($extension, $this->_data['download'])) {
                continue;
            }
            // 存在扩展则检测扩展是否通过依赖
            if (isset($list[$uniqueName])) {
                // 检测扩展是否需要安装
                if ($verifyInstalled &&
                    !isset($this->_data['uninstall'][$uniqueName]) &&
                    !isset($this->service->getDb()->getInstalled()[$uniqueName])
                ) {
                    $this->_data['uninstall'][$uniqueName] = false;
                }
                // 扩展没有通过检测
                if (!isset($this->_data['passed'][$uniqueName])) {
                    /** @var ExtensionInfo $infoInstance */
                    $infoInstance = $list[$uniqueName]['infoInstance'];
                    // 版本不符合则提示需要解决版本冲突
                    if (!Ec::$service->getSystem()->getVersion()->compare($infoInstance->getVersion(), $version)) {
                        $this->_data['conflict'][$uniqueName]['localVersion'] = $infoInstance->getVersion();
                        $this->_data['conflict'][$uniqueName][$parent] = $version;
                    } // 版本一致
                    else {
                        $this->_data['passed'][$uniqueName] = false;
                        $this->detectDependenciesStatus($infoInstance->getDepends(), $list, $uniqueName, $verifyInstalled);
                        unset($this->_data['passed'][$uniqueName]); // 确保被依赖的扩展在前
                        $this->_data['passed'][$uniqueName] = $list[$uniqueName];
                        if (isset($this->_data['uninstall'][$uniqueName])) {
                            unset($this->_data['passed'][$uniqueName]);
                        }
                    }
                } elseif ($this->_data['passed'][$uniqueName] === false) {
                    $this->_data['circular'][$uniqueName] = $this->composeCircularDependencyTrace($uniqueName);
                } else {
                    /** @var ExtensionInfo $infoInstance */
                    $infoInstance = $list[$uniqueName]['infoInstance'];
                    // 版本不符合则提示需要解决版本冲突
                    if (!Ec::$service->getSystem()->getVersion()->compare($infoInstance->getVersion(), $version)) {
                        $this->_data['conflict'][$uniqueName]['localVersion'] = $infoInstance->getVersion();
                        $this->_data['conflict'][$uniqueName][$parent] = $version;
                    }
                }
            } // 不存在扩展则提示需要下载该扩展
            else {
                $this->_data['download'][] = $extension;
            }
        }
    }
    
    /**
     * 组成扩展依赖关系内循环跟踪信息
     *
     * @param string $circularDependencyName 内循环扩展名称
     *
     * @return string
     */
    protected function composeCircularDependencyTrace($circularDependencyName): string
    {
        $dependencyTrace = [];
        $startFound = false;
        foreach ($this->_data['passed'] as $uniqueName => $value) {
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
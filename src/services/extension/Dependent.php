<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\Ec;
use EngineCore\extension\repository\configuration\Configuration;
use EngineCore\services\Extension;
use EngineCore\base\Service;
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
                            'requireApp'     => $row['app'],
                            'downloaded'     => false,
                            'installed'      => false,
                        ];
                        // 本地存在扩展
                        if (isset($configuration[$uniqueName])) {
                            $currentConfig = $configuration[$uniqueName];
                            $arr['downloaded'] = true;
                            $arr['localVersion'] = $currentConfig->getVersion();
                            $arr['description'] = $currentConfig->getDescription();
                            /*
                             * 为数组时，则要求在多个应用里必须安装所依赖的扩展，如：
                             * [
                             *  "engine-core/theme-bootstrap-v3" => [
                             *      'app' => ['backend', 'frontend']
                             *  ]
                             * ]
                             * 表示在'backend'和'frontend'应用里必须安装"engine-core/theme-bootstrap-v3"扩展
                             */
                            foreach ((array)$row['app'] as $requireApp) {
                                // 验证所请求的应用是否有效，无效则过滤该依赖
                                if (in_array($requireApp, $currentConfig->getApp())) {
                                    $arr['installed'] = isset($this->service->getRepository()->getDbConfiguration()[$requireApp][$uniqueName]);
                                    $arr['requireApp'] = $requireApp;
                                    $definition['extensionDependencies'][$app][$uniqueName] = $arr;
                                }
                            }
                        } else {
                            $definition['extensionDependencies'][$app][$uniqueName] = $arr;
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
                    'app'     => [],
                ];
            } else {
                $row['version'] = $row['version'] ?? '*';
                $row['app'] = (array)($row['app'] ?? []);
                if (in_array('common', $row['app'])) {
                    $row['app'] = ['common'];
                }
            }
        }
        
        return $extensions;
    }
    
    /**
     * 检测指定应用下指定扩展是否满足所需的依赖关系
     *
     * @param string $uniqueName 扩展名称
     * @param string $app        应用ID
     *
     * @return bool
     */
    public function checkDependencies(string $uniqueName, $app = null): bool
    {
        if (!isset($this->service->getRepository()->getFinder()->getConfiguration()[$uniqueName])) {
            $this->_info = "不存在扩展：`{$uniqueName}`";
            
            return $this->_status = false;
        }
        
        $app = $app ?: Yii::$app->id;
        $configuration = $this->service->getRepository()->getFinder()->getConfiguration()[$uniqueName];
        if (!in_array($app, $configuration->getApp())) {
            $this->_info = $uniqueName . '扩展无法在`' . $app . '`应用里安装。';
            
            return $this->_status = false;
        }
        if (false === $this->validate($configuration->getExtensionDependencies()[$app] ?? [])) {
            $this->_info = '请先满足扩展依赖关系再执行当前操作。';
            $this->_data['download'] = $this->getDownload();
            $this->_data['circular'] = $this->getCircular();
            $this->_data['conflict'] = $this->getConflict();
            $this->_status = false;
        }
        
        return $this->_status;
    }
    
    /**
     * 验证扩展数据是否通过依赖检测
     *
     * @param array $extensions 扩展数据
     *
     * @see normalize()
     * ```php
     * [
     *  'engine-core/theme-bootstrap-v3' => [
     *      'version' => '*',
     *      'app' => 'backend', // ['backend', 'frontend']
     *  ]
     * ]
     * ```
     *
     * @param bool  $debug      是否开启调试，显示调试数据， 默认不开启
     * @param array $parent     父级扩展名称
     *
     * @return bool
     */
    public function validate(array $extensions, $debug = false, array $parent = []): bool
    {
        $this->_data['checked'] = [];
        $extensions = $this->checkParent($this->normalize($extensions), $parent);
        $configuration = $this->service->getRepository()->getFinder()->getConfiguration();
        /**
         * 生成已经通过检测的扩展数据
         *
         * @param Configuration $config
         * @param array         $data
         * @param string        $app
         */
        $debugFunc = function (Configuration $config, array $data, string $app) {
            $uniqueName = $config->getName();
            $this->_data['checked'][$uniqueName]['version'] = $config->getVersion();
            $this->_data['checked'][$uniqueName]['app'][$app] = $app;
            $this->_data['checked'][$uniqueName]['items'][] = [
                'app'            => $app,
                'requireVersion' => $data['version'],
                'extensions'     => $data['parent'],
            ];
        };
        
        while (!empty($extensions)) {
            $ext = [];
            $break = false; // 扩展依赖关系未满足，则终止后续检测
            foreach ($extensions as $uniqueName => $row) {
                $config = $configuration[$uniqueName];
                foreach ($row['app'] as $app) {
                    // 存在次级依赖扩展
                    if (isset($config->getExtensionDependencies()[$app])) {
                        $arr = $this->checkParent($config->getExtensionDependencies()[$app], array_merge($row['parent'], [$uniqueName]));
                        $break = empty($arr);
                        if (!$break) {
                            $ext = array_merge($ext, $arr);
                        }
                    }
                    // 次级扩展依赖关系不满足，则忽略该扩展所属的父级扩展
                    if ($break) {
                        foreach ($row['parent'] as $uniqueName) {
                            unset($this->_data['checked'][$uniqueName]);
                        }
                    } else {
                        $debugFunc($config, $row, $app);
                    }
                }
            }
            $extensions = $ext;
        }
        
        $this->_passed = $this->sort($this->_data['checked']);
        
        if (!$debug) {
            unset($this->_data['circular_tmp'], $this->_data['checked']);
        }
        
        if (
            !empty($this->getDownload())
            || !empty($this->getConflict())
            || !empty($this->getCircular())
        ) {
            $this->_status = false;
        } else {
            $this->_status = true;
        }
        
        return $this->_status;
    }
    
    /**
     * 检测父级扩展是否满足依赖关系，即是否存在【未下载、版本冲突或无限循环】等问题
     *
     * @param array $extensions
     * @param array $parent
     *
     * @return array 返回满足依赖关系的扩展数组
     */
    private function checkParent(array $extensions, array $parent = []): array
    {
        $configuration = $this->service->getRepository()->getFinder()->getConfiguration();
        $total = count($extensions);
        $rootLevel = empty($parent); // 根级别扩展
        /**
         * 检测扩展合法性
         *
         * @param Configuration $config
         * @param array         $data
         * @param array         $extensions
         *
         * @internal param string $requireVersion
         * @internal param array $parent
         */
        $checker = function (Configuration $config, array $data, array &$extensions) {
            $parent = $data['parent'];
            $requireVersion = $data['version'];
            $uniqueName = $config->getName();
            // 版本不符合则提示需要解决版本冲突
            if (!Ec::$service->getSystem()->getVersion()->compare($config->getVersion(), $requireVersion)) {
                $this->_conflict[$uniqueName]['localVersion'] = $config->getVersion();
                $this->_conflict[$uniqueName]['items'][] = [
                    'extensions'     => $parent,
                    'requireVersion' => $requireVersion,
                ];
                unset($extensions[$uniqueName]);
            } // 通过版本验证
            else {
                // 检测扩展是否存在无限循环依赖
                // 扩展没有被检测
                if (!isset($this->_data['circular_tmp'][$uniqueName])) {
                    $this->_data['circular_tmp'][$uniqueName] = false;
                } // 已被检测的扩展是否存在无限循环依赖
                elseif (false === $this->_data['circular_tmp'][$uniqueName]) {
                    if (in_array($uniqueName, $parent)) {
                        $this->_circular[$uniqueName] = array_merge($parent, [$uniqueName]);
                        unset($extensions[$uniqueName]);
                    }
                }
            }
        };
        
        foreach ($extensions as $uniqueName => &$row) {
            $row['parent'] = $parent;
            // 本地存在扩展
            if (isset($configuration[$uniqueName])) {
                $config = $configuration[$uniqueName];
                $row['app'] = array_intersect($config->getApp(), $row['app']);
                // 不存在有效app，则忽略该扩展
                if (empty($row['app'])) {
                    unset($extensions[$uniqueName]);
                    continue;
                }
                $checker($config, $row, $extensions);
            } // 本地不存在扩展
            else {
                $this->_download[$uniqueName][] = [
                    'extensions'     => $parent,
                    'requireVersion' => $row['version'],
                ];
                unset($extensions[$uniqueName]);
            }
        }
        
        return !$rootLevel && ($total != count($extensions))
            // 非顶级的父级扩展所依赖的子级扩展没有解决依赖关系（冲突、不存在或无限循环），则中断该扩展的后续检测
            ? []
            : $extensions;
    }
    
    /**
     * 按照底层依赖关系排序扩展，确保被依赖的扩展在前
     *
     * @param array $extensions
     *                   ```php
     *                   [
     *                   'engine-core/theme-bootstrap-v3' => [
     *                   'app' => ['backend']
     *                   ]
     *                   ]
     *                   ```
     * @param array $arr 循环中转参数
     *
     * @return array
     * ```php
     * [
     *      'engine-core/config-system' => [
     *          'app' => ['common']
     *      ],
     *      'engine-core/theme-bootstrap-v3' => [
     *          'app' => ['backend']
     *      ]
     * ]
     * ```
     */
    public function sort(array $extensions, $arr = []): array
    {
        $data = [];
        $configuration = $this->service->getRepository()->getFinder()->getConfiguration();
        
        foreach ($extensions as $uniqueName => $row) {
            $config = $configuration[$uniqueName];
            foreach ($row['app'] as $app) {
                // 扩展没有被检测
                if (!isset($arr[$app][$uniqueName])) {
                    $arr[$app][$uniqueName] = false;
                    // 递归检测依赖关系
                    if (isset($config->getExtensionDependencies()[$app])) {
                        $data = array_merge($data, $this->sort($config->getExtensionDependencies()[$app], $arr));
                    }
                    if (!isset($data[$uniqueName]['app'])) {
                        $data[$uniqueName]['app'][] = $app;
                    } elseif (!in_array($app, $data[$uniqueName]['app'])) {
                        array_push($data[$uniqueName]['app'], $app);
                    }
                }
            }
        }
        
        return $data;
    }
    
    /**
     * @var array 需要下载的扩展
     */
    private $_download = [];
    
    /**
     * 获取需要下载的扩展
     *
     * @return array
     */
    public function getDownload(): array
    {
        return $this->_download;
    }
    
    /**
     * @var array 存在版本冲突的扩展
     */
    private $_conflict = [];
    
    /**
     * 获取存在版本冲突的扩展
     *
     * @return array
     */
    public function getConflict(): array
    {
        return $this->_conflict;
    }
    
    /**
     * @var array 无限循环依赖的扩展
     */
    private $_circular = [];
    
    /**
     * 获取无限循环依赖的扩展
     *
     * @return array
     */
    public function getCircular(): array
    {
        return $this->_circular;
    }
    
    /**
     * @var array 通过依赖检测的扩展
     */
    private $_passed = [];
    
    /**
     * 获取通过依赖检测的扩展
     *
     * @see validate()
     *
     * @return array
     */
    public function getPassed(): array
    {
        return $this->_passed;
    }
    
}
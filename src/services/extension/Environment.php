<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\enums\AppEnum;
use EngineCore\services\Extension;
use EngineCore\Ec;
use EngineCore\extension\ControllerInfo;
use EngineCore\extension\ExtensionInfo;
use EngineCore\extension\ModularityInfo;
use EngineCore\extension\ThemeInfo;
use EngineCore\helpers\FileHelper;
use EngineCore\base\Service;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * 扩展环境服务类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Environment extends Service
{
    
    /**
     * @var Extension 父级服务类
     */
    public $service;
    
    /**
     * 在指定路径下生成系统配置文件
     *
     * @param array  $config 待生成的配置数据
     * @param string $path 待生成文件的路径，支持路径别名
     *
     * @return bool
     */
    public function generateConfigFile($config, $path): bool
    {
        $content = <<<php
<?php
// 注意：该文件由系统自动生成，请勿更改！
return
php;
        $content .= ' ' . VarDumper::export($config) . ';';
        
        return FileHelper::createFile(Yii::getAlias($path), $content, 0744);
    }
    
    /**
     * 刷新系统配置文件
     *
     * 系统配置文件如下：
     * '@app/config/extension.php'，系统各应用的扩展配置文件，如'backend','frontend'
     * '@common/config/extension.php'，公共扩展配置文件
     * '@extensions/aliases.php'，扩展别名配置文件
     *
     * @param bool $all 是否刷新所有应用的配置文件，默认为`true`，刷新所有应用的配置文件。
     * 该值为`false`时，将会刷新以下配置文件：
     * '@app/config/extension.php'，当前应用的扩展配置文件
     * '@common/config/extension.php'，公共扩展配置文件
     *
     * @return array 成功生成的配置文件路径数组
     */
    public function flushConfigFiles($all = true)
    {
        if ($all) {
            $this->service->getCache()->clearCache();
        } else {
            $this->service->getRepository()->clearCache();
        }
        $configuration = $this->generateConfig();
        $list = $this->getConfigFileList();
        if ($all) {
            // 扩展别名配置数据
            $configuration['extensions'] = [
                'aliases' => $this->service->getRepository()->getAliases(),
            ];
        } else {
            $list = [
                Yii::$app->id => $list[Yii::$app->id],
                'common'      => $list['common'],
            ];
        }
        
        $files = [];
        foreach ($list as $category => $file) {
            if (isset($configuration[$category]) && $this->generateConfigFile($configuration[$category], $file)) {
                $files[] = $file;
            }
        }
        
        return $files;
    }
    
    /**
     * 移除系统配置文件
     *
     * 系统配置文件如下：
     * '@app/config/extension.php'
     * '@common/config/extension.php;
     * '@extensions/aliases.php'
     *
     * @return array 成功移除的配置文件路径数组
     */
    public function removeConfigFiles()
    {
        $list = [];
        foreach ($this->getConfigFileList() as $file) {
            if (FileHelper::removeFile($file)) {
                $list[] = $file;
            }
        }
        
        return $list;
    }
    
    /**
     * 获取配置文件路径列表数据
     *
     * @return array
     */
    protected function getConfigFileList(): array
    {
        $files = [];
        // 应用扩展配置文件
        foreach (AppEnum::list() as $app => $name) {
            $files[$app] = Yii::getAlias("@{$app}/config/extension.php");
        }
        
        return array_merge($files, [
            'common'     => Yii::getAlias("@common/config/extension.php"), // 公共配置文件
            'extensions' => Yii::getAlias("@extensions/aliases.php"), // 扩展别名配置文件
        ]);
    }
    
    /**
     * 生成已安装的扩展配置信息
     *
     * @return array
     * ```php
     *  [
     *      {app} => [
     *          'bootstrap' => [],
     *          'components' => [],
     *          'modules' => [],
     *          'controllerMap' => [],
     *          'params' => [],
     *      ],
     *      'common' => [],
     *  ]
     * ```
     */
    protected function generateConfig(): array
    {
        $configuration = $this->service->getRepository()->getDbConfiguration();
        
        // 获取所有激活的主题
        $activeTheme = Ec::$service->getExtension()->getThemeRepository()->getAllActiveTheme();
        foreach ($configuration as $uniqueName => $row) {
            /** @var ThemeInfo $infoInstance */
            $infoInstance = $row['infoInstance'];
            if (is_subclass_of($infoInstance, ThemeInfo::class) && !isset($activeTheme[$uniqueName])) {
                unset($configuration[$uniqueName]);
            }
        }
        if (empty($configuration)) {
            return [];
        }
        $data = ArrayHelper::index(
            $configuration,
            function ($element) {
                /** @var ExtensionInfo $infoInstance */
                $infoInstance = $element['infoInstance'];
                
                return $infoInstance->getUniqueName();
            },
            function ($element) {
                /** @var ExtensionInfo $infoInstance */
                $infoInstance = $element['infoInstance'];
                
                return $infoInstance->app;
            }
        );
        
        $configuration = [];
        foreach ($data as $app => $config) {
            $configuration = ArrayHelper::merge($configuration, $this->_generate($config, $app));
        }
        
        return $configuration;
    }
    
    /**
     * 生成扩展系统配置数据
     *
     * @param array  $config
     * @param string $app
     *
     * @return array
     * ```php
     *  [
     *      {app} => [
     *          'bootstrap' => [],
     *          'components' => [],
     *          'modules' => [],
     *          'controllerMap' => [],
     *          'params' => [],
     *      ],
     *      'common' => [],
     *  ]
     * ```
     */
    private function _generate($config, $app)
    {
        if (empty($config)) {
            return [];
        }
        // 生成扩展公共配置
        $configuration['common'] = [];
        // 启动模块配置信息
        $configuration[$app]['bootstrap'] = [];
        // 创建模块配置
        foreach ($config as $uniqueName => $row) {
            /** @var ModularityInfo $infoInstance */
            $infoInstance = $row['infoInstance'];
            if (is_subclass_of($infoInstance, ModularityInfo::class)) {
                // 生成扩展配置数据
                $infoConfig = $infoInstance->getConfig();
                // 剔除扩展配置无效键名
                foreach ($infoConfig as $k => $v) {
                    if (!in_array($k, $infoInstance->getConfigKey())) {
                        unset($infoConfig[$k]);
                    }
                }
                $configuration[$app] = ArrayHelper::merge($configuration[$app], $infoConfig);
                $configuration[$app]['modules'][$infoInstance->id]['class'] = $row['class'];
                // 生成公共配置数据
                $infoConfig = $infoInstance->getCommonConfig();
                // 剔除扩展配置无效键名
                foreach ($infoConfig as $k => $v) {
                    if (!in_array($k, $infoInstance->getCommonConfigKey())) {
                        unset($infoConfig[$k]);
                    }
                }
                $configuration['common'] = ArrayHelper::merge($configuration['common'], $infoConfig);
                // 加载启动模块
                if ($infoInstance->bootstrap) {
                    array_push($configuration[$app]['bootstrap'], $infoInstance->id);
                }
                unset($config[$uniqueName]);
            }
        }
        // 创建控制器配置
        foreach ($config as $uniqueName => $row) {
            /** @var ControllerInfo $infoInstance */
            $infoInstance = $row['infoInstance'];
            if (is_subclass_of($infoInstance, ControllerInfo::class)) {
                // 生成扩展配置数据
                $infoConfig = $infoInstance->getConfig();
                // 剔除扩展配置无效键名
                foreach ($infoConfig as $k => $v) {
                    if (!in_array($k, $infoInstance->getConfigKey())) {
                        unset($infoConfig[$k]);
                    }
                }
                $configuration[$app] = ArrayHelper::merge($configuration[$app], $infoConfig);
                // 模块控制器扩展
                if (isset($configuration[$app]['modules'][$infoInstance->getModuleId()])) {
                    $configuration[$app]['modules'][$infoInstance->getModuleId()]['controllerMap'][$infoInstance->id]['class'] = $row['class'];
                } // 应用控制器扩展
                elseif ($app == $infoInstance->getModuleId()) {
                    $configuration[$app]['controllerMap'][$infoInstance->id]['class'] = $row['class'];
                }
                // 生成公共配置数据
                $infoConfig = $infoInstance->getCommonConfig();
                // 剔除扩展配置无效键名
                foreach ($infoConfig as $k => $v) {
                    if (!in_array($k, $infoInstance->getCommonConfigKey())) {
                        unset($infoConfig[$k]);
                    }
                }
                $configuration['common'] = ArrayHelper::merge($configuration['common'], $infoConfig);
                unset($config[$uniqueName]);
            }
        }
        // 加载主题配置
        foreach ($config as $uniqueName => $row) {
            /** @var ThemeInfo $infoInstance */
            $infoInstance = $row['infoInstance'];
            if (is_subclass_of($infoInstance, ThemeInfo::class)) {
                // 生成扩展配置数据
                $infoConfig = $infoInstance->getConfig();
                // 剔除扩展配置无效键名
                foreach ($infoConfig as $k => $v) {
                    if (!in_array($k, $infoInstance->getConfigKey())) {
                        unset($infoConfig[$k]);
                    }
                }
                $configuration[$app] = ArrayHelper::merge($configuration[$app], $infoConfig);
                // 生成公共配置数据
                $infoConfig = $infoInstance->getCommonConfig();
                // 剔除扩展配置无效键名
                foreach ($infoConfig as $k => $v) {
                    if (!in_array($k, $infoInstance->getCommonConfigKey())) {
                        unset($infoConfig[$k]);
                    }
                }
                $configuration['common'] = ArrayHelper::merge($configuration['common'], $infoConfig);
                unset($config[$uniqueName]);
            }
        }
        
        return $configuration;
    }
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\enums\AppEnum;
use EngineCore\enums\StatusEnum;
use EngineCore\services\Extension;
use EngineCore\extension\repository\info\ControllerInfo;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\extension\repository\info\ModularityInfo;
use EngineCore\extension\repository\info\ThemeInfo;
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
    protected function generateConfigFile($config, $path): bool
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
     * 刷新扩展配置文件
     *
     * 扩展配置文件如下：
     * '@app/config/extension.php'，系统各应用的扩展配置文件，如'backend','frontend'
     * '@common/config/extension.php'，公共扩展配置文件
     * '@extensions/config.php'，系统扩展配置文件
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
            $this->service->clearCache();
        }
        $configuration = $this->generateConfig();
        $list = $this->getConfigFileList();
        if ($all) {
            // 扩展别名配置数据
            $configuration['extensions'] = [
                'aliases' => $this->service->getRepository()->getFinder()->getAliases(),
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
     * 移除扩展配置文件
     *
     * 扩展配置文件如下：
     * '@app/config/extension.php'
     * '@common/config/extension.php;
     * '@extensions/config.php'
     *
     * @see getConfigFileList()
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
     * 获取扩展配置文件路径列表数据
     *
     * @return array
     */
    public function getConfigFileList(): array
    {
        $files = [];
        // 应用扩展配置文件
        foreach (AppEnum::list() as $app => $name) {
            $files[$app] = Yii::getAlias("@{$app}/config/extension.php");
        }
        
        return array_merge($files, [
            'extensions' => Yii::getAlias("@extensions/config.php"), // 扩展配置文件
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
     *  ]
     * ```
     */
    public function generateConfig(): array
    {
        $dbConfiguration = $this->service->getRepository()->getDbConfiguration(); // 已安装扩展的数据库数据
        if (empty($dbConfiguration)) {
            return [];
        }
        $localConfiguration = $this->service->getRepository()->getLocalConfiguration(); // 本地所有扩展的配置数据
        $extensions = [];
        foreach ($dbConfiguration as $app => $row) {
            foreach ($row as $uniqueName => $cfg) {
                // 只获取已经安装且本地存在配置信息的扩展
                if (isset($localConfiguration[$app][$uniqueName])) {
                    /** @var ExtensionInfo $infoInstance */
                    $infoInstance = $localConfiguration[$app][$uniqueName];
                    // 目前仅支持同一个扩展只能安装在同一个应用中一次，故取第一个数据库数据即可
                    $data = $cfg[0];
                    // 只获取激活的主题扩展
                    if ($infoInstance->getType() === $infoInstance::TYPE_THEME && $data['status'] == StatusEnum::STATUS_OFF) {
                        continue;
                    }
                    $extensions[$app][$uniqueName] = $infoInstance;
                }
            }
        }
        if (empty($extensions)) {
            return [];
        }
        
        $configuration = [];
        foreach ($extensions as $app => $row) {
            $configuration = ArrayHelper::merge($configuration, $this->_generate($row));
        }
        
        return $configuration;
    }
    
    
    /**
     * 获取扩展配置数据
     *
     * @param ExtensionInfo $infoInstance
     *
     * @return array
     */
    public function getConfig(ExtensionInfo $infoInstance)
    {
        $arr = [];
        $config = $infoInstance->getConfig();
        $app = $infoInstance->getApp();
        $isGroup = false; // 是否采用应用分组配置方式
        $isCommonExtension = 'common' === $app; // 是否为公共扩展
        
        if (!empty($config)) {
            foreach ($config as $k => $v) {
                if (in_array($k, AppEnum::list())) {
                    $isGroup = true;
                    break;
                }
            }
            if ($isGroup) {
                foreach ($config as $k => $v) {
                    // 如果为公共扩展，则获取其他应用下的配置数据
                    if ($isCommonExtension) {
                        if (in_array($k, AppEnum::list())) {
                            $arr[$k] = $v;
                        }
                    } // 如果不是公共扩展，则只获取当前应用下的配置数据
                    else {
                        if ($k === $app) {
                            $arr[$k] = $v;
                        }
                    }
                }
            } else {
                $arr[$app] = $config;
            }
        }
        
        return $arr;
    }
    
    /**
     * 生成扩展系统配置数据
     *
     * @param array  $config
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
     *  ]
     * ```
     */
    private function _generate($config)
    {
        if (empty($config)) {
            return [];
        }
        $configuration = [];
        // 加载模块配置
        $this->_loadModuleConfig($configuration, $config);
        // 加载控制器配置
        $this->_loadControllerConfig($configuration, $config);
        // 加载主题配置
        $this->_loadThemeConfig($configuration, $config);
        
        return $configuration;
    }
    
    /**
     * 加载模块配置
     *
     * @param $configuration
     * @param $config
     */
    private function _loadModuleConfig(&$configuration, &$config)
    {
        /** @var ModularityInfo $infoInstance */
        foreach ($config as $uniqueName => $infoInstance) {
            $app = $infoInstance->getApp();
            if ($infoInstance->getType() == $infoInstance::TYPE_MODULE) {
                // 应用配置数据
                $configuration = ArrayHelper::merge($configuration, $this->getConfig($infoInstance));
                //  添加模块配置
                $class = $infoInstance->getAutoloadPsr4()['namespace'] . 'Module';
                $configuration[$app]['modules'][$infoInstance->getId()]['class'] = $class;
                // 加载启动模块
                if ($infoInstance->getIsBootstrap()) {
                    $configuration[$app]['bootstrap'][] = $infoInstance->getId();
                }
                unset($config[$app][$uniqueName]);
            }
        }
    }
    
    /**
     * 加载控制器配置
     *
     * @param $configuration
     * @param $config
     */
    private function _loadControllerConfig(&$configuration, &$config)
    {
        /** @var ControllerInfo $infoInstance */
        foreach ($config as $uniqueName => $infoInstance) {
            $app = $infoInstance->getApp();
            if ($infoInstance->getType() == $infoInstance::TYPE_CONTROLLER) {
                // 应用配置数据
                $configuration = ArrayHelper::merge($configuration, $this->getConfig($infoInstance));
                // 添加控制器配置
                // 模块控制器配置
                $class = $infoInstance->getAutoloadPsr4()['namespace'] . 'Controller';
                if (isset($configuration[$app]['modules'][$infoInstance->getModuleId()])) {
                    $configuration[$app]['modules'][$infoInstance->getModuleId()]['controllerMap'][$infoInstance->getId()]['class'] = $class;
                } // 应用控制器配置
                elseif ('' == $infoInstance->getModuleId()) {
                    $configuration[$app]['controllerMap'][$infoInstance->getId()]['class'] = $class;
                }
                unset($config[$app][$uniqueName]);
            }
        }
    }
    
    /**
     * 加载主题配置
     *
     * @param $configuration
     * @param $config
     */
    private function _loadThemeConfig(&$configuration, &$config)
    {
        /** @var ThemeInfo $infoInstance */
        foreach ($config as $uniqueName => $infoInstance) {
            $app = $infoInstance->getApp();
            if ($infoInstance->getType() == $infoInstance::TYPE_THEME) {
                // 应用配置数据
                $configuration = ArrayHelper::merge($configuration, $this->getConfig($infoInstance));
                unset($config[$app][$uniqueName]);
            }
        }
    }
    
}
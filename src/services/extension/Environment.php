<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\services\extension;

use EngineCore\Ec;
use EngineCore\enums\AppEnum;
use EngineCore\helpers\ArrayHelper;
use EngineCore\services\Extension;
use EngineCore\extension\repository\info\ControllerInfo;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\extension\repository\info\ModularityInfo;
use EngineCore\helpers\FileHelper;
use EngineCore\base\Service;
use Yii;
use yii\console\Application;
use yii\helpers\VarDumper;

/**
 * 扩展环境服务类
 *
 * 注意：使用前必须确保系统已经正确配置扩展仓库模型类，否则会因没有已经安装的扩展数据而导致数据异常。
 * @see    \EngineCore\services\extension\Repository::hasModel()
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
     * @var string 系统设置文件，文件方式存储系统设置数据或不同存储方式转换时使用，这通常是储存系统扩展配置里的数据
     * @see \EngineCore\extension\setting\FileProvider
     */
    public $settingFile = '@extensions/setting.php';
    
    /**
     * @var string 菜单配置文件，文件方式存储菜单数据或不同存储方式转换时使用，这通常是储存系统扩展配置里的数据
     * @see \EngineCore\extension\menu\FileProvider
     */
    public $menuFile = '@extensions/menu.php';
    
    /**
     * @var string 用户设置文件，文件方式存储系统设置数据，一般用户自定义的数据应在该文件里设置
     * @see \EngineCore\extension\setting\FileProvider
     */
    public $userSettingFile = '@common/config/setting.php';
    
    /**
     * @var string 用户菜单文件，文件方式存储系统菜单数据，一般用户自定义的数据应在该文件里设置
     * @see \EngineCore\extension\menu\FileProvider
     */
    public $userMenuFile = '@common/config/menu.php';
    
    /**
     * @var string 数据库配置文件
     */
    public $dbConfigFile = '@common/config/db-local.php';
    
    /**
     * 刷新扩展配置文件
     *
     * @see getConfigFileList()
     *
     * @return array ['success', 'fail'] 成功生成或失败的配置文件路径数组
     */
    public function flushConfigFiles(): array
    {
        // 配置数据
        $configuration = array_merge(array_fill_keys(array_keys(AppEnum::list()), []), $this->generateConfig());
        
        $files = [];
        $hasModel = $this->service->getRepository()->hasModel(); // 是否已经配置扩展仓库模型类
        foreach ($this->getConfigFileList() as $category => $file) {
            if (isset($configuration[$category])) {
                $config = $configuration[$category];
                $res = false;
                switch ($category) {
                    case 'menu':
                        $res = $this->flushMenuFile($config);
                        break;
                    case 'setting':
                        $res = $this->flushSettingFile($config);
                        break;
                    case 'extension':
                        $res = $this->generateConfigFile($config, $file);
                        break;
                    default:
                        if ($hasModel) {
                            $res = $this->generateConfigFile($config, $file);
                        }
                }
                $files[$res ? 'success' : 'fail'][] = $file;
            }
        }
        
        return $files;
    }
    
    /**
     * 移除扩展配置文件
     *
     * @see getConfigFileList()
     *
     * @return array ['success', 'fail'] 成功移除或失败的配置文件路径数组
     */
    public function removeConfigFiles(): array
    {
        $files = [];
        foreach ($this->getConfigFileList() as $file) {
            $res = FileHelper::removeFile($file);
            $files[$res ? 'success' : 'fail'][] = $file;
        }
        
        return $files;
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
            'extension' => Yii::getAlias("@extensions/config.php"), // 扩展配置文件
            'setting'   => Yii::getAlias($this->settingFile), // 系统设置文件
            'menu'      => Yii::getAlias($this->menuFile), // 菜单配置文件
        ]);
    }
    
    /**
     * 获取扩展实际的配置数据
     *
     * 扩展信息类里的`\EngineCore\extension\repository\info\ExtensionInfo::getConfig()`方法返回的是扩展所有应用
     * 环境下的配置数据。当前方法只获取和`$infoInstance`扩展实例有关的配置数据。
     * @see \EngineCore\extension\repository\info\ExtensionInfo::getConfig()
     *
     * @param ExtensionInfo $infoInstance
     *
     * @return array
     * ```php
     * [
     *      'common' => [
     *          'components' => [],
     *          'params' => [],
     *          'modules' => [],
     *          'controllerMap' => [],
     *      ],
     *      {app} => [
     *          'components' => [],
     *          'params' => [],
     *          'modules' => [],
     *          'controllerMap' => [],
     *      ],
     * ]
     * ```
     */
    public function getConfig(ExtensionInfo $infoInstance): array
    {
        $config = $infoInstance->getConfig();
        if (empty($config)) {
            return [];
        }
        
        $arr = [];
        $app = $infoInstance->getApp();
        $isGroup = (bool)array_intersect_key(AppEnum::list(), $config); // 是否采用应用分组配置方式
        
        if ($isGroup) {
            // 如果为公共扩展，则同时获取其他应用下的配置数据
            if (AppEnum::COMMON === $app) {
                /**
                 * 排序数组，让'common'公共配置在前
                 *
                 * @param array $config
                 *
                 * @return array
                 */
                $sortFunc = function (array $config): array {
                    $arr[AppEnum::COMMON] = ArrayHelper::remove($config, AppEnum::COMMON, []);
                    
                    return ArrayHelper::merge($arr, $config);
                };
                $arr = array_intersect_key($sortFunc($config), AppEnum::list());
            } // 如果不是公共扩展，则只获取当前应用下的配置数据
            else {
                $arr[$app] = $config[$app] ?? [];
            }
        } else {
            $arr[$app] = $config;
        }
        
        return $arr;
    }
    
    /**
     * 加载扩展实例的翻译文件配置
     *
     * @param ExtensionInfo $infoInstance
     */
    public function loadTranslationConfig(ExtensionInfo $infoInstance)
    {
        $config = $this->getConfig($infoInstance);
        // 公共翻译文件配置
        $translations[AppEnum::COMMON] = ArrayHelper::getValue($config, AppEnum::COMMON . '.components.i18n.translations', []);
        // 应用翻译文件配置
        $translations[$infoInstance->getApp()] = ArrayHelper::getValue($config, $infoInstance->getApp() . '.components.i18n.translations', []);
        Yii::$app->getI18n()->translations = ArrayHelper::merge(
            $translations[AppEnum::COMMON],
            $translations[$infoInstance->getApp()],
            Yii::$app->getI18n()->translations
        );
    }
    
    /**
     * 生成扩展配置数据
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
     *      'menu' => [],
     *      'setting' => [],
     *      'extension' => [],
     *  ]
     * ```
     */
    public function generateConfig(): array
    {
        $configuration = [];
        $localConfiguration = $this->service->getRepository()->getLocalConfiguration(); // 本地所有扩展的配置数据
        $installedExtension = $this->service->getRepository()->getInstalledExtension(); // 已安装扩展的数据库数据
        if (!empty($installedExtension)) {
            $isConsoleApp = Yii::$app instanceof Application;
            foreach ($installedExtension as $uniqueName => $apps) {
                $hasOne = false;
                foreach ($apps as $app) {
                    /** @var ExtensionInfo $infoInstance */
                    $infoInstance = $localConfiguration[$app][$uniqueName];
                    // 只获取激活状态的扩展
                    if ($infoInstance->getIsEnable()) {
                        // 扩展配置数据
                        $config = $this->_getConfig($infoInstance);
                        // 确保只获取一次
                        if (false === $hasOne) {
                            $hasOne = true;
                            // 加载翻译文件配置
                            if ($isConsoleApp) {
                                $this->loadTranslationConfig($infoInstance);
                            }
                            // 菜单数据
                            $config['menu'] = $infoInstance->getMenus();
                            // 系统设置数据
                            $config['setting'] = $infoInstance->getSettings();
                        }
                        $configuration = ArrayHelper::superMerge($configuration, $config);
                    }
                }
            }
            // 菜单数据
            $configuration['menu'] = ArrayHelper::merge(
                Ec::$service->getMenu()->getConfig()->getProvider()->getDefaultConfig(),
                $configuration['menu'] ?? []
            );
            // 系统设置数据
            $configuration['setting'] = ArrayHelper::merge(
                Ec::$service->getSystem()->getSetting()->getProvider()->getDefaultConfig(),
                $configuration['setting'] ?? []
            );
            // 扩展配置文件所需的配置数据
            $configuration['extension'] = [
                'aliases' => $this->service->getRepository()->getFinder()->getAliases(),
            ];
        }
        
        return $configuration;
    }
    
    /**
     * 获取和指定扩展实例有关的配置数据
     *
     * @param ExtensionInfo $infoInstance
     *
     * @return array
     * ```php
     * [
     *      {app} => [
     *          'components' => [],
     *          'params' => [],
     *          'modules' => [],
     *          'controllerMap' => [],
     *      ],
     * ]
     * ```
     */
    private function _getConfig(ExtensionInfo $infoInstance): array
    {
        switch (true) {
            // 加载模块配置
            case $infoInstance->getType() == $infoInstance::TYPE_MODULE:
                /* @var ModularityInfo $infoInstance */
                // 应用配置数据
                $configuration = $this->getConfig($infoInstance);
                if (!$infoInstance->getBootstrap()) {
                    unset($configuration['bootstrap']);
                }
                break;
            // 加载控制器配置
            case $infoInstance->getType() == $infoInstance::TYPE_CONTROLLER:
                /* @var ControllerInfo $infoInstance */
                $app = $infoInstance->getApp();
                // 应用配置数据
                $configuration = $this->getConfig($infoInstance);
                // 添加控制器配置
                // 模块控制器配置
                $class = $infoInstance->getAutoloadPsr4()['namespace'] . 'Controller';
                if (!empty($infoInstance->getModuleId())) {
                    $configuration[$app]['modules'][$infoInstance->getModuleId()]['controllerMap'][$infoInstance->getId()]['class'] = $class;
                } // 应用控制器配置
                else {
                    $configuration[$app]['controllerMap'][$infoInstance->getId()]['class'] = $class;
                }
                break;
            // 加载其他配置
            default:
                // 应用配置数据
                $configuration = $this->getConfig($infoInstance);
        }
        
        return $configuration;
    }
    
    /**
     * 刷新系统设置文件
     *
     * @param array $config 待生成的配置数据
     *
     * @return bool
     */
    public function flushSettingFile(array $config): bool
    {
        $header = <<<header
/**
 * 注意：该文件由系统自动生成，请勿更改！
 *
 * 文件方式存储系统设置数据
 *
 * 文件默认存储的是系统已经安装的扩展和系统默认的设置数据，这些数据由系统自动生成，所以一切对文件的更改都会被覆写。
 * 如果需要更改设置，建议在`params-local.php`文件里对系统设置进行更改。
 *
 * 对系统设置进行更改，是通过`Yii::\$app->params['system-setting']`参数实现对系统数据的覆写。
 * 具体实现可查看： @see \\EngineCore\\extension\\setting\\FileProvider::getAll() 方法。
 *
 * ### 更改设置
 * 假如现在需要更改网站标题的值，对应的键名是`SITE_TITLE`，更改设置的大体操作如下：
 * 在`params-local.php`文件里添加以下代码：
 * ```php
 * return [
 *      // 对系统设置的更改需要储存在该键名的数组下
 *      // SettingProviderInterface::SETTING_KEY => [
 *      'system-setting' => [
 *          'SITE_TITLE' => [
 *              'value' => '这是新的网站标题',
 *          ],
 *      ],
 * ];
 * ```
 * 或通过链式键名的方式进行更改，这样的配置方式更简洁美观：
 * ```php
 * return [
 *      ':system-setting.SITE_TITLE.value' => '这是新的网站标题',
 * ];
 * ```
 * 用`:`开头并以`.`连接每个键名，该格式即可调用`链式键名配置`，至此系统会自动定位并更改最后`value`键名的值。
 *
 * ### 新增设置
 * ```php
 * return [
 *      // 对系统设置的更改需要储存在该键名的数组下
 *      // SettingProviderInterface::SETTING_KEY => [
 *      'system-setting' => [
 *          'NEW_KEY' => [
 *              'name' => 'NEW_KEY',
 *              'title' => '新的设置标题',
 *              'value' => '新的设置值',
 *              'description' => '新的设置描述',
 *              'extra' => '新的设置额外值',
 *          ],
 *      ],
 * ];
 * ```
 * 或通过链式键名的方式进行新增：
 * ```php
 * return [
 *      ':system-setting.NEW_KEY' => [
 *          'name' => 'NEW_KEY',
 *          'title' => '新的设置标题',
 *          'value' => '新的设置值',
 *          'description' => '新的设置描述',
 *          'extra' => '新的设置额外值',
 *      ],
 * ];
 * ```
 *
 * 注意：设置数组的键名可用值请参照 @see \\EngineCore\\extension\\setting\\SettingFieldTrait::\$_mapField 数组的键名。
 *
 * 默认的设置数据可查看设置数据提供器的默认配置。
 * @see \\EngineCore\\extension\\setting\\SettingProviderTrait::getDefaultConfig()
 *
 * 更多详情请查看 @see \\EngineCore\\extension\\setting\\SettingProviderInterface
 */
 
header;
        
        return $this->generateConfigFile($config, $this->settingFile, $header);
    }
    
    /**
     * 刷新用户设置文件
     *
     * @param array $config 待生成的配置数据
     *
     * @return bool
     */
    public function flushUserSettingFile(array $config): bool
    {
        $header = <<<header
/**
 * 文件方式存储系统设置数据
 *
 * 默认的设置数据可查看设置数据提供器的默认配置。
 * @see \\EngineCore\\extension\\setting\\SettingProviderTrait::getDefaultConfig()
 *
 * 设置方式可参考：
 * @see \\EngineCore\\services\\extension\\Environment::flushSettingFile()
 *
 * 或只更改下列相应配置里的`'value'`值即可。
 *
 * 更多详情请查看 @see \\EngineCore\\extension\\setting\\SettingProviderInterface
 */
 
header;
        
        return $this->generateConfigFile($config, $this->userSettingFile, $header);
    }
    
    /**
     * 刷新数据库配置文件
     *
     * @param array $config 待生成的配置数据
     *
     * @return bool
     */
    public function flushDbFile(array $config): bool
    {
        return $this->generateConfigFile($config, $this->dbConfigFile);
    }
    
    /**
     * 刷新菜单配置文件
     *
     * @param array $config 待生成的配置数据
     *
     * @return bool
     */
    public function flushMenuFile(array $config): bool
    {
        $header = <<<header
/**
 * 注意：该文件由系统自动生成，请勿更改！
 *
 * 文件方式存储系统菜单数据
 *
 * 文件默认存储的是系统已经安装的扩展和系统默认的菜单数据，这些数据由系统自动生成，所以一切对文件的更改都会被覆写。
 * 如果需要更改菜单数据，建议在`params-local.php`文件里对菜单数据进行更改。
 *
 * 对菜单数据进行更改，是通过`Yii::\$app->params['system-menu']`参数实现对菜单数据的覆写。
 * 具体实现可查看： @see \\EngineCore\\extension\\menu\\FileProvider::getAll() 方法。
 *
 * ### 更改菜单
 * 假如现在需要更改系统`backend`后台默认的`EngineCore`菜单数据，菜单的具体配置请查看：
 * @see \\EngineCore\\extension\menu\\MenuProviderTrait::getDefaultConfig() 方法，更改菜单的大体操作如下：
 * 在`params-local.php`文件里添加以下代码：
 * ```php
 * return [
 *      // 对菜单数据的更改需要储存在该键名的数组下，该键名是系统预留的配置键名
 *      // MenuProviderInterface::MENU_KEY => [
 *      'system-menu' => [
 *          'backend' => [
 *              'engine-core' => [
 *                  ':items.0' => [ // 更改`items`数组里索引为`0`的数据，这里使用了链式键名配置方式
 *                      'alias' => 'EngineCore的最新动态',
 *                  ],
 *              ],
 *          ],
 *      ],
 * ];
 * ```
 * 以上的方式同样可以通过使用\\EngineCore\\helpers\\MergeArrayValue() 对象进行合并替换：
 * ```php
 * return [
 *      // 对菜单数据的更改需要储存在该键名的数组下，该键名是系统预留的配置键名
 *      // MenuProviderInterface::MENU_KEY => [
 *      'system-menu' => [
 *          'backend' => [
 *              'engine-core' => [
 *                  'items' => [
 *                      0 => new \\EngineCore\\helpers\\MergeArrayValue([ // 更改索引为`0`的数据
 *                          'alias' => 'EngineCore的最新动态',
 *                      ]),
 *                  ],
 *              ],
 *          ],
 *      ],
 * ];
 * ```
 * 或通过链式键名的方式进行更改，这样的配置方式更简洁美观：
 * ```php
 * return [
 *      'system-menu' => [
 *          ':backend.engine-core.items.0.alias' => 'EngineCore的最新动态',
 *      ],
 * ];
 * ```
 * 用`:`开头并以`.`连接每个键名，该格式即可调用`链式键名配置`，至此系统会自动定位并更改最后`alias`键名的值。
 *
 * ### 新增菜单
 * ```php
 * return [
 *      // 对菜单数据的更改需要储存在该键名的数组下
 *      // MenuProviderInterface::MENU_KEY => [
 *      'system-menu' => [
 *          'backend' => [
 *              'engine-core' => [
 *                  'items' => [
 *                      [
 *                          'label' => '新的菜单',
 *                          'alias' => '新的菜单',
 *                          'show' => true,
 *                      ],
 *                  ],
 *              ],
 *          ],
 *      ],
 * ];
 * ```
 * 或通过链式键名的方式进行新增，这样的配置方式更简洁美观
 * ```php
 * return [
 *      ':system-menu.backend.engine-core.items' => [
 *          [
 *              'label' => '新的菜单',
 *              'alias' => '新的菜单',
 *              'show' => true,
 *          ],
 *      ],
 * ];
 * ```
 *
 * 注意：菜单数组的键名可用值请参照 @see \\EngineCore\\extension\\menu\\MenuFieldTrait::\$_mapField 接口里提供的属性方法。
 *
 * 默认的设置数据可查看设置数据提供器的默认配置。
 * @see \\EngineCore\\extension\\menu\\MenuProviderTrait::getDefaultConfig()
 *
 * 更多详情请查看 @see \\EngineCore\\extension\\menu\\MenuProviderInterface
 */
 
header;
        
        return $this->generateConfigFile($config, $this->menuFile, $header);
    }
    
    /**
     * 在指定路径下生成系统配置文件
     *
     * @param array  $config 待生成的配置数据
     * @param string $path   待生成文件的路径，支持路径别名
     * @param string $header 头部信息
     *
     * @return bool
     */
    public function generateConfigFile(array $config, string $path, string $header = '// 注意：该文件由系统自动生成，请勿更改！'): bool
    {
        $content = <<<php
<?php
%s
return
php;
        $content = sprintf($content, $header ?: '') . ' ';
        $content .= VarDumper::export($config) . ';';
        
        return FileHelper::createFile(Yii::getAlias($path), $content, 0744);
    }
    
}
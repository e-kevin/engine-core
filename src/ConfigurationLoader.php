<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore;

use EngineCore\extension\menu\MenuProviderInterface;
use EngineCore\extension\setting\SettingProviderInterface;
use EngineCore\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * 配置加载器
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ConfigurationLoader
{

    /**
     * @var array 配置文件
     */
    private $_config = [];

    /**
     * @var array 参数配置文件
     */
    private $_param = [];

    /**
     * @var string 单配置文件
     */
    private $_singleConfigFile;

    /**
     * @var bool 是否总是加载最新的配置数据
     */
    private $alwaysLoad;

    /**
     * ConfigurationLoader constructor.
     *
     * @param string $singleConfigFile 单配置文件
     * @param array $bootstrap 启动配置文件
     * @param array $config 一般配置文件
     * @param array $param 参数配置文件
     * @param mixed $alwaysLoad 是否总是加载最新的配置数据
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct(
        string $singleConfigFile, array $bootstrap, array $config, array $param = [], $alwaysLoad = null
    )
    {
        foreach ($bootstrap as $file) {
            require("$file");
        }
        $this->_singleConfigFile = $singleConfigFile;
        $this->_config = $config;
        $this->_param = $param;
        if (is_bool($alwaysLoad)) {
            $this->alwaysLoad = $alwaysLoad;
        } elseif ($alwaysLoad instanceof \Closure) {
            $this->alwaysLoad = $alwaysLoad($singleConfigFile);
        } else {
            // 默认开发环境下，总是加载最新的配置数据
            $this->alwaysLoad = YII_ENV_DEV;
        }
    }

    /**
     * 获取配置数据
     *
     * @return array
     */
    public function getConfig()
    {
        if ($this->alwaysLoad) {
            $this->_config = $this->loadConfig();
        } elseif (!is_file($this->_singleConfigFile)) {
            $this->_config = $this->loadConfig();
            $this->generateConfigFile($this->_config);
        } else {
            $this->_config = require("$this->_singleConfigFile");
        }

        return $this->_config;
    }

    /**
     * 加载配置数据，包括主配置(`main`)数据和参数(`params`)配置数据
     *
     * @return array
     */
    protected function loadConfig(): array
    {
        $config = [];
        foreach ($this->_config as $file) {
            $config = ArrayHelper::superMerge(
                $config,
                require("$file")
            );
        }
        foreach ($this->_param as $file) {
            $params = require("$file");
            /**
             * 取出系统预留的配置键名数据，使之保持原样，确保实际使用时的最优先级。
             * 而类似':system-setting.SITE_TITLE.value'这样的以系统预留的配置键名来定义的链式键名配置方式，
             * 会优先被处理成最终的数据，如':system-setting.SITE_TITLE.value'会优先被处理成：
             * [
             *      'system-setting' => [
             *          'SITE_TITLE' => [
             *              'value' => '',
             *          ]
             *      ]
             * ]
             * 所以，希望在需要被调用时配置才生效，必须用系统预留的配置键名来定义配置数组，如：
             * [
             *      'system-menu' => [
             *          // 更改数值
             *          ':backend.engine-core.items.0.alias' => 'EngineCore的最新动态',
             *      ]
             * ]
             * 以上配置仅在该配置被调用时才会被处理，而不是事先处理。
             */
            $settings = ArrayHelper::remove($params, SettingProviderInterface::SETTING_KEY, []);
            $menus = ArrayHelper::remove($params, MenuProviderInterface::MENU_KEY, []);
            $config['params'][SettingProviderInterface::SETTING_KEY] = ArrayHelper::merge($config['params'][SettingProviderInterface::SETTING_KEY] ?? [], $settings);
            $config['params'][MenuProviderInterface::MENU_KEY] = ArrayHelper::merge($config['params'][MenuProviderInterface::MENU_KEY] ?? [], $menus);
            $config['params'] = ArrayHelper::superMerge($config['params'], $params);
        }

        return $config;
    }

    /**
     * 在指定路径下生成系统配置文件
     *
     * @param array $config 待生成的配置数据
     *
     * @return bool
     */
    protected function generateConfigFile($config): bool
    {
        $content = <<<php
<?php
// 注意：该文件由系统自动生成，请勿更改！
return
php;
        // todo 启用数据压缩
        $content .= ' ' . VarDumper::export($config) . ';';

        return $this->createFile($this->_singleConfigFile, $content, 0744);
    }

    /**
     * 创建文件
     *
     * @param string $filePath 文件路径
     * @param mixed $content 内容
     * @param int $mode 文件权限
     *
     * @return bool
     */
    private function createFile(string $filePath, $content, int $mode = 0777): bool
    {
        if (@file_put_contents($filePath, $content, LOCK_EX) !== false) {
            if ($mode !== null) {
                @chmod($filePath, $mode);
            }

            return @touch($filePath);
        } else {
            return false;
        }
    }

}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore;

use EngineCore\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * 配置加载器
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ConfigurationLoader
{
    
    private $_config = [];
    
    private $_configFile;
    
    /**
     * @var bool 是否强制总是加载最新的配置数据
     */
    public $force;
    
    /**
     * ConfigurationLoader constructor.
     *
     * @param string $configFile
     * @param array  $bootstrap
     * @param array  $config
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct(string $configFile, array $bootstrap, array $config)
    {
        foreach ($bootstrap as $file) {
            require("$file");
        }
        $this->_configFile = $configFile;
        $this->_config = $config;
        // 默认开发环境下，总是加载最新的配置数据
        (null === $this->force) && $this->force = YII_ENV_DEV;
    }
    
    /**
     * 获取配置数据
     *
     * @return array
     */
    public function getConfig()
    {
        if ($this->force || !is_file($this->_configFile)) {
            $config = [];
            foreach ($this->_config as $file) {
                $config = ArrayHelper::merge(
                    $config,
                    require("$file")
                );
            }
            $this->generateConfigFile($config);
            $this->_config = $config;
        } else {
            $this->_config = require("$this->_configFile");
        }
        
        return $this->_config;
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
        $content .= ' ' . VarDumper::export($config) . ';';
        
        return FileHelper::createFile($this->_configFile, $content, 0744);
    }
    
}
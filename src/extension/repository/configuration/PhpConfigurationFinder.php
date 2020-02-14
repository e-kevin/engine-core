<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension\repository\configuration;

use yii\helpers\ArrayHelper;

/**
 * 系统默认的PHP格式扩展配置文件搜索器
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class PhpConfigurationFinder extends ConfigurationFinder
{
    
    /**
     * @inheritdoc
     */
    protected function readConfigFiles($files)
    {
        $config = [];
        foreach ($files as $file) {
            $file = require "{$file}";
            $name = ArrayHelper::remove($file, 'name');
            $psr4 = ArrayHelper::remove($file['autoload'], 'psr-4');
            $config[$name] = $file;
            $config[$name]['version'] = $config[$name]['version'] ?? 'dev-master'; // 设置默认版本
            $config[$name]['autoload']['psr-4'] = [
                'namespace' => array_keys($psr4)[0],
                'path'      => array_shift($psr4),
            ];
        }
        
        return $config;
    }
    
    /**
     * @inheritdoc
     */
    protected function createConfiguration($data)
    {
        // TODO: Implement createConfiguration() method.
    }
}
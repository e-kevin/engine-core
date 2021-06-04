<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\configuration;

/**
 * PHP格式扩展配置文件搜索器
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class PhpConfigurationFinder extends ConfigurationFinder
{
    
    public $searchFileName = 'config.php';
    
    /**
     * {@inheritdoc}
     */
    protected function read($file)
    {
        return include "$file";
    }
    
    /**
     * {@inheritdoc}
     */
    public function readInstalledFile($file)
    {
        return [];
    }
    
}
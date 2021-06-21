<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\configuration;

use EngineCore\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * JSON格式扩展配置文件搜索器
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class JsonConfigurationFinder extends ConfigurationFinder
{
    
    public $searchFileName = 'composer.json';
    
    /**
     * {@inheritdoc}
     */
    protected function read($file)
    {
        return Json::decode(file_get_contents($file));
    }
    
    /**
     * {@inheritdoc}
     */
    public function readInstalledFile($file)
    {
        return ArrayHelper::index($this->read($file), 'name');
    }
    
}
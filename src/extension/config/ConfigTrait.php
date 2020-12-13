<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\config;

trait ConfigTrait
{
    
    /**
     * @var string 配置键名
     */
    public $nameField = 'name';
    
    /**
     * @var string 配置值字段
     */
    public $valueField = 'value';
    
    /**
     * @var string 额外数据字段
     */
    public $extraField = 'extra';
    
    /**
     * @var string 配置键名
     */
    protected $configKey = 'config.system';
    
}
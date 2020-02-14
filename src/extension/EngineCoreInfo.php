<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension;

/**
 * EngineCore核心扩展信息类
 */
class EngineCoreInfo extends ExtensionInfo
{
    
    public
        $app = 'EngineCore',
        $id = 'EngineCore';
    
    protected
        $_name = 'EngineCore',
        $_description = 'EngineCore核心构架',
        $_repositoryUrl = ['github' => 'https://github.com/e-kevin/engine-core'],
        $_authors = [
        [
            'name' => 'E-Kevin',
            'email' => 'e-kevin@qq.com',
        ],
    ];
    
}
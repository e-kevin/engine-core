<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\installation;

/**
 * 安装扩展接口类，一般在安装扩展模块的Info类里使用
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface InstallExtensionInterface
{
    
    /**
     * 配置扩展仓库模型
     *
     * @see \EngineCore\services\extension\ControllerRepository::setModel()
     * @see \EngineCore\services\extension\ModularityRepository::setModel()
     * @see \EngineCore\services\extension\ThemeRepository::setModel()
     */
    public function setRepositoryModel();
    
}
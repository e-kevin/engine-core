<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\menu\components;

use EngineCore\extension\menu\MenuProviderInterface;

/**
 * 配置服务接口类
 *
 * @property MenuProviderInterface $provider
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface ConfigServiceInterface
{
    
    /**
     * 获取菜单数据提供者
     *
     * @return MenuProviderInterface
     */
    public function getProvider(): MenuProviderInterface;
    
    /**
     * 设置菜单数据提供者
     *
     * @param array $config
     */
    public function setProvider($config = []);
    
    /**
     * 同步扩展菜单数据
     *
     * @return bool
     */
    public function sync(): bool;
    
}

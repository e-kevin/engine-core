<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\services\system;

use EngineCore\extension\setting\SettingProviderInterface;

/**
 * 系统设置服务接口
 *
 * @property SettingProviderInterface $provider 配置数据提供器
 * @property array                    $all      所有配置项
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface SettingServiceInterface
{
    
    /**
     * 获取所有配置项
     *
     * @return array
     */
    public function getAll();
    
    /**
     * 获取指定标识的配置值
     *
     * @param string $key          标识ID e.g. WEB_SITE_TITLE
     * @param mixed  $defaultValue 默认值
     *
     * @return mixed
     */
    public function get($key, $defaultValue = null);
    
    /**
     * 获取指定标识的额外配置值
     *
     * @param string $key          标识ID .e.g BACKEND_THEME
     * @param mixed  $defaultValue 默认值
     *
     * @return mixed
     */
    public function extra($key, $defaultValue = null);
    
    /**
     * 获取看板配置
     *
     * @param string       $key          标识ID e.g. REGISTER_STEP
     * @param string       $category     看板分类 e.g. enable|disable，默认为`enable`
     * @param string|array $defaultValue 默认值
     *
     * @return array
     */
    public function sortable($key, $category = 'enable', $defaultValue = []);
    
    /**
     * 获取系统设置数据提供器
     *
     * @return SettingProviderInterface
     */
    public function getProvider(): SettingProviderInterface;
    
    /**
     * 设置系统设置数据提供器
     *
     * @param string|array|callable $provider
     */
    public function setProvider($provider);
    
}
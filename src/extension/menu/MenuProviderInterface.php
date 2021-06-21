<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\menu;

use EngineCore\base\DataCacheInterface;

/**
 * 菜单数据提供器接口
 *
 * 系统提供了以下几种方式的菜单数据提供器实现：
 * 1、文件方式 @see \EngineCore\extension\menu\FileProvider
 * 2、数据库方式 @see \EngineCore\extension\menu\DbProvider
 * 3、任何实现该接口的类 如：菜单数据库的模型类 @see \EngineCore\extension\menu\MenuModel
 *
 * 在使用菜单服务前，必须先指定使用哪个菜单数据提供器来提供菜单数据。
 * 如下：我们在`@common/config/main-local.php`文件里进行设置
 * ```php
 * [
 *  'container' => [
 *      'definitions' => [
 *          'MenuProvider' => [
 *              // 指定具体实现了当前接口的数据提供器
 *              'class' => 'EngineCore\extension\menu\FileProvider',
 *          ]
 *      ]
 *  ]
 * ]
 *
 * @property array  $all                 所有菜单数据
 * @property array  $defaultConfig       默认菜单数据
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface MenuProviderInterface extends DataCacheInterface, MenuFieldInterface
{
    
    const
        CREATED_BY_USER = 0, // 菜单创建者为用户
        CREATED_BY_EXTENSION = 1; // 菜单创建者为扩展
    
    /**
     * @var string 菜单数据的键名
     */
    const MENU_KEY = 'system-menu';
    
    /**
     * 获取所有菜单数据，数组键名以`id`为索引，通常可以考虑使用缓存
     *
     * @return array
     */
    public function getAll(): array;
    
    /**
     * 获取指定菜单层级的所有菜单数据
     *
     * @param int $level
     *
     * @return array
     */
    public function getAllByLevel(int $level): array;
    
    /**
     * 获取默认菜单配置
     *
     * 注意：数组的键名可用值请参照 @see MenuFieldTrait::$_mapField 的数组键名值。
     *
     * @return array
     */
    public function getDefaultConfig(): array;
    
//    /**
//     * 获取菜单模型类
//     *
//     * @return ActiveRecord
//     */
//    public function getModel();
//
//    /**
//     * 设置菜单模型类
//     *
//     * @param array $config
//     *
//     * @throws InvalidConfigException
//     */
//    public function setModel($config = []);

//    /**
//     * 获取菜单配置数据
//     *
//     * @return array
//     * ```php
//     * [
//     *  {app} => [],
//     * ]
//     * ```
//     */
//    public function getMenuConfig();

//    /**
//     * 设置菜单配置数据
//     *
//     * @param array $config 菜单配置数据，该数据为未写入数据库的菜单数组数据，一般为系统默认的菜单数据或扩展配置文件里的菜单数据。
//     *
//     * @see \EngineCore\extension\menu\MenuProvider::defaultMenuConfig()
//     *
//     *```php
//     * [
//     *  {app} => [],
//     * ]
//     * ```
//     */
//    public function setMenuConfig($config = []);
    
}
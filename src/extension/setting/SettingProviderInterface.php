<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\setting;

use EngineCore\base\DataCacheInterface;

/**
 * 系统设置数据提供器接口
 *
 * 系统提供了以下几种方式的设置数据提供器实现：
 * 1、文件方式 @see \EngineCore\extension\setting\FileProvider
 * 2、数据库方式 @see \EngineCore\extension\setting\DbProvider
 * 3、任何实现该接口的类 如：设置数据库的模型类 @see \EngineCore\extension\setting\SettingModel
 *
 * 设置获取方式：
 * 通过使用系统的设置服务类即可成功获取。
 * 如：`\EngineCore\Ec::$service->getSystem()->getSetting()->get('SITE_TITLE')`
 * @see    \EngineCore\services\system\Setting::get()
 *
 * 在使用系统设置服务前，必须先指定使用哪个设置数据提供器来提供设置数据。
 * 如下：我们在`@common/config/main-local.php`文件里进行设置
 * ```php
 * [
 *      'container' => [
 *          'definitions' => [
 *              'SettingProvider' => [
 *                  // 指定具体实现了当前接口的数据提供器
 *                  'class' => 'EngineCore\extension\setting\FileProvider',
 *              ]
 *          ]
 *      ]
 * ]
 *
 * @property array $all 所有设置项
 * @property array $defaultConfig 默认设置数据
 * @property array $defaultFields 默认的设置字段和属性名
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface SettingProviderInterface extends DataCacheInterface
{

    // 默认设置标识
    const
        SITE_TITLE = 'SITE_TITLE', // 网站标题
        SITE_DESCRIPTION = 'SITE_DESCRIPTION', // 网站描述
        SITE_KEYWORD = 'SITE_KEYWORD', // 网站关键词
        SITE_ICP = 'SITE_ICP', // 网站备案号
        CONFIG_TYPE_LIST = 'CONFIG_TYPE_LIST', // 字段类型列表
        CONFIG_GROUP_LIST = 'CONFIG_GROUP_LIST', // 设置分组列表
        DEFAULT_THEME = 'DEFAULT_THEME', // 默认主题
        ENABLE_THEME = 'ENABLE_THEME', // 启用多主题
        STRICT_THEME = 'STRICT_THEME'; // 启用主题严谨模式

    // 设置字段类型
    const
        TYPE_STRING = 1, // 字符串类型
        TYPE_TEXT = 2, // 文本类型
        TYPE_SELECT = 3, // 下拉框类型
        TYPE_CHECKBOX = 4, // 选择框类型
        TYPE_RADIO = 5, // 单选项类型
        TYPE_DATETIME = 6, // 日期时间类型
        TYPE_DATE = 7, // 日期类型
        TYPE_TIME = 8, // 时间类型
        TYPE_KANBAN = 9; // 看板类型

    // 设置分组
    const
        CATEGORY_NONE = 0, // 不分组
        CATEGORY_BASE = 1, // 基础设置
        CATEGORY_CONTENT = 2, // 内容设置
        CATEGORY_REGISTRATION = 3, // 注册设置
        CATEGORY_SYSTEM = 4, // 系统设置
        CATEGORY_SECURITY = 5; // 安全设置

    /**
     * @var string 设置数据的键名
     */
    const SETTING_KEY = 'system-setting';

    /**
     * 获取所有设置项，数组键名以设置标识为索引，通常可以考虑使用缓存
     *
     * @return array 必须返回包含有默认字段键名的数组，如：
     * ```php
     * [
     *      'SITE_TITLE' => [
     *          'name'        => SettingProviderInterface::SITE_TITLE,
     *          'value'       => '',
     *          'extra'       => '',
     *          'title'       => '网站名称',
     *          'description' => '网站名称',
     *          'type'        => SettingProviderInterface::TYPE_STRING,
     *          'category'    => SettingProviderInterface::CATEGORY_BASE,
     *          'rule'        => 'required',
     *      ],
     *      ...,
     * ]
     * ```
     * @see getDefaultFields()
     */
    public function getAll();

    /**
     * 获取默认设置数据
     *
     * @return array 必须返回包含有默认字段键名的数组，如：
     * ```php
     * [
     *      'SITE_TITLE' => [
     *          'name'        => SettingProviderInterface::SITE_TITLE,
     *          'value'       => '',
     *          'extra'       => '',
     *          'title'       => '网站名称',
     *          'description' => '网站名称',
     *          'type'        => SettingProviderInterface::TYPE_STRING,
     *          'category'    => SettingProviderInterface::CATEGORY_BASE,
     *          'rule'        => 'required',
     *      ],
     *      ...,
     * ]
     * ```
     * @see getDefaultFields()
     */
    public function getDefaultConfig(): array;

    /**
     * 获取默认的设置字段和属性名
     *
     * @return array
     */
    public function getDefaultFields(): array;

}
<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\setting;

use EngineCore\base\CacheDurationTrait;
use EngineCore\enums\EnableEnum;

/**
 * Class SettingProviderTrait
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
trait SettingProviderTrait
{
    
    use CacheDurationTrait, SettingFieldTrait;
    
    /**
     * 获取默认设置数据
     *
     * @see SettingProviderInterface::getDefaultConfig()
     *
     * @return array
     *
     * 必须返回包含有 @see SettingFieldTrait::$_mapField 数组键名值的数组，如：
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
     *      ],
     *      ...,
     * ]
     * ```
     */
    public function getDefaultConfig(): array
    {
        $typeList = [
            SettingProviderInterface::TYPE_STRING . ':字符串',
            SettingProviderInterface::TYPE_TEXT . ':文本',
            SettingProviderInterface::TYPE_SELECT . ':下拉框',
            SettingProviderInterface::TYPE_CHECKBOX . ':多选框',
            SettingProviderInterface::TYPE_RADIO . ':单选框',
            SettingProviderInterface::TYPE_DATETIME . ':日期+时间',
            SettingProviderInterface::TYPE_DATE . ':日期',
            SettingProviderInterface::TYPE_TIME . ':时间',
            SettingProviderInterface::TYPE_KANBAN . ':看板',
        ];
        $groupList = [
            SettingProviderInterface::CATEGORY_NONE . ':不分组',
            SettingProviderInterface::CATEGORY_BASE . ':基础',
            SettingProviderInterface::CATEGORY_CONTENT . ':内容',
            SettingProviderInterface::CATEGORY_REGISTRATION . ':注册',
            SettingProviderInterface::CATEGORY_SYSTEM . ':系统',
            SettingProviderInterface::CATEGORY_SECURITY . ':安全',
        ];
        $enableList = [];
        foreach (EnableEnum::list() as $key => $value) {
            $enableList[] = "$key:$value";
        }
        
        return [
            SettingProviderInterface::SITE_TITLE        => [
                'name'        => SettingProviderInterface::SITE_TITLE,
                'value'       => '',
                'extra'       => '',
                'title'       => '网站名称',
                'description' => '网站名称',
                'type'        => SettingProviderInterface::TYPE_STRING,
                'category'    => SettingProviderInterface::CATEGORY_BASE,
            ],
            SettingProviderInterface::SITE_DESCRIPTION  => [
                'name'        => SettingProviderInterface::SITE_DESCRIPTION,
                'value'       => '',
                'extra'       => '',
                'title'       => '网站描述',
                'description' => '网站描述',
                'type'        => SettingProviderInterface::TYPE_STRING,
                'category'    => SettingProviderInterface::CATEGORY_BASE,
            ],
            SettingProviderInterface::SITE_KEYWORD      => [
                'name'        => SettingProviderInterface::SITE_KEYWORD,
                'value'       => '',
                'extra'       => '',
                'title'       => '网站关键词',
                'description' => '搜索引擎关键词',
                'type'        => SettingProviderInterface::TYPE_STRING,
                'category'    => SettingProviderInterface::CATEGORY_BASE,
            ],
            SettingProviderInterface::SITE_ICP          => [
                'name'        => SettingProviderInterface::SITE_ICP,
                'value'       => '',
                'extra'       => '',
                'title'       => '网站备案号',
                'description' => '网站备案号，如：沪ICP备12345678号-9',
                'type'        => SettingProviderInterface::TYPE_STRING,
                'category'    => SettingProviderInterface::CATEGORY_BASE,
            ],
            SettingProviderInterface::CONFIG_TYPE_LIST  => [
                'name'        => SettingProviderInterface::CONFIG_TYPE_LIST,
                'value'       => '',
                'extra'       => implode(',', $typeList),
                'title'       => '字段类型列表',
                'description' => '主要用于数据解析和页面表单项的生成',
                'type'        => SettingProviderInterface::TYPE_SELECT,
                'category'    => SettingProviderInterface::CATEGORY_NONE,
            ],
            SettingProviderInterface::CONFIG_GROUP_LIST => [
                'name'        => SettingProviderInterface::CONFIG_GROUP_LIST,
                'value'       => '',
                'extra'       => implode(',', $groupList),
                'title'       => '设置分组列表',
                'description' => '设置分组',
                'type'        => SettingProviderInterface::TYPE_SELECT,
                'category'    => SettingProviderInterface::CATEGORY_NONE,
            ],
            SettingProviderInterface::DEFAULT_THEME     => [
                'name'        => SettingProviderInterface::DEFAULT_THEME,
                'value'       => '',
                'extra'       => '',
                'title'       => '默认主题',
                'description' => '如果启用主题功能，在获取不到主题相关的视图文件或调度器时，将在默认主题里搜索相关资源',
                'type'        => SettingProviderInterface::TYPE_SELECT,
                'category'    => SettingProviderInterface::CATEGORY_NONE,
            ],
            SettingProviderInterface::ENABLE_THEME      => [
                'name'        => SettingProviderInterface::ENABLE_THEME,
                'value'       => EnableEnum::ENABLE,
                'extra'       => implode(',', $enableList),
                'title'       => '启用多主题',
                'description' => '关闭多主题功能后，系统将会在默认主题里获取相关的视图文件和调度器',
                'type'        => SettingProviderInterface::TYPE_RADIO,
                'category'    => SettingProviderInterface::CATEGORY_NONE,
            ],
            SettingProviderInterface::STRICT_THEME      => [
                'name'        => SettingProviderInterface::STRICT_THEME,
                'value'       => EnableEnum::DISABLE,
                'extra'       => implode(',', $enableList),
                'title'       => '主题严谨模式',
                'description' => '如果开启主题严谨模式，则调度器会优先在主题目录下获取需要的视图文件，否则从`views`目录里获取',
                'type'        => SettingProviderInterface::TYPE_RADIO,
                'category'    => SettingProviderInterface::CATEGORY_NONE,
            ],
        ];
    }
    
}
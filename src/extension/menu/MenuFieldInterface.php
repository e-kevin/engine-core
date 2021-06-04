<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\menu;

/**
 * 菜单数据提供器默认属性和数据库表字段接口，该接口对文件方式的菜单数据提供器无效
 *
 * @property array  $fieldMap            配置键名与方法名之间的映射关系
 * @property string $labelField          菜单名称的数据库字段名
 * @property string $aliasField          菜单别名的数据库字段名
 * @property string $iconField           菜单图标的数据库字段名
 * @property string $urlField            url地址的数据库字段名
 * @property string $paramsField         url地址参数的数据库字段名
 * @property string $configField         菜单配置数据的数据库字段名
 * @property string $visibleField        是否显示菜单的数据库字段名
 * @property string $orderField          菜单排序的数据库字段名
 * @property string $themeField          菜单所属的主题的数据库字段名
 * @property string $descriptionField    菜单描述的数据库字段名
 * @property string $categoryField       菜单分类的数据库字段名
 * @property string $createdByField      菜单创建类型的数据库字段名
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface MenuFieldInterface
{
    
    /**
     * 获取配置键名与方法名之间的映射关系
     *
     * @return array
     */
    public function getFieldMap(): array;
    
    /**
     * 获取菜单名称的数据库字段名
     *
     * 建议简写，如‘列表’、‘更新’、‘删除’等，通常在某些地方需要显示很多菜单项时使用，如系统的权限配置里。
     *
     * @return string
     */
    public function getLabelField(): string;
    
    /**
     * 设置菜单名称的数据库字段名
     *
     * @param string $field
     */
    public function setLabelField(string $field);
    
    /**
     * 获取菜单别名的数据库字段名
     *
     * 建议完整显示，如‘用户列表’、‘更新用户’等，一般用于菜单显示。
     *
     * @return string
     */
    public function getAliasField(): string;
    
    /**
     * 设置菜单别名的数据库字段名
     *
     * @param string $field
     */
    public function setAliasField(string $field);
    
    /**
     * 获取菜单图标的数据库字段名
     *
     * @return string
     */
    public function getIconField(): string;
    
    /**
     * 设置菜单图标的数据库字段名
     *
     * @param string $field
     */
    public function setIconField(string $field);
    
    /**
     * 获取url地址的数据库字段名
     *
     * @return string
     */
    public function getUrlField(): string;
    
    /**
     * 设置url地址的数据库字段名
     *
     * @param string $field
     */
    public function setUrlField(string $field);
    
    /**
     * 获取url地址参数的数据库字段名
     *
     * @return string
     */
    public function getParamsField(): string;
    
    /**
     * 设置url地址参数的数据库字段名
     *
     * @param string $field
     */
    public function setParamsField(string $field);
    
    /**
     * 获取菜单配置数据的数据库字段名
     *
     * 一般用于小部件，为小部件提供菜单本身所需的配置参数
     *
     * @return string
     */
    public function getConfigField(): string;
    
    /**
     * 设置菜单配置数据的数据库字段名
     *
     * @param string $field
     */
    public function setConfigField(string $field);
    
    /**
     * 获取是否显示菜单的数据库字段名
     *
     * @return string
     */
    public function getVisibleField(): string;
    
    /**
     * 设置是否显示菜单的数据库字段名
     *
     * @param string $field
     */
    public function setVisibleField(string $field);
    
    /**
     * 获取菜单排序的数据库字段名
     *
     * @return string
     */
    public function getOrderField(): string;
    
    /**
     * 设置菜单排序的数据库字段名
     *
     * @param string $field
     */
    public function setOrderField(string $field);
    
    /**
     * 获取菜单所属的主题的数据库字段名
     *
     * 默认为'common'，表示所有主题通用，该值为系统保留字段，建议开发者避免使用该值作为主题名
     *
     * @return string
     */
    public function getThemeField(): string;
    
    /**
     * 设置菜单所属的主题的数据库字段名
     *
     * @param string $field
     */
    public function setThemeField(string $field);
    
    /**
     * 获取菜单描述的数据库字段名
     *
     * @return string
     */
    public function getDescriptionField(): string;
    
    /**
     * 设置菜单描述的数据库字段名
     *
     * @param string $field
     */
    public function setDescriptionField(string $field);
    
    /**
     * 获取菜单分类的数据库字段名
     *
     * @return string
     */
    public function getCategoryField(): string;
    
    /**
     * 设置菜单分类的数据库字段名
     *
     * @param string $field
     */
    public function setCategoryField(string $field);
    
    /**
     * 获取菜单创建类型的数据库字段名
     *
     * @return string
     */
    public function getCreatedByField(): string;
    
    /**
     * 设置菜单创建类型的数据库字段名
     *
     * @param string $field
     */
    public function setCreatedByField(string $field);
    
}
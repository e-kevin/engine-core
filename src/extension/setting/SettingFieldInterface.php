<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\setting;

/**
 * 系统设置数据提供器默认属性和数据库表字段接口，该接口对文件方式的设置数据提供器无效
 *
 * @property array  $fieldMap            配置键名与方法名之间的映射关系
 * @property string $nameField           设置键名的数据库字段名
 * @property string $valueField          设置值的数据库字段名
 * @property string $extraField          设置额外数据的数据库字段名
 * @property string $titleField          设置标题的数据库字段名
 * @property string $descriptionField    设置描述的数据库字段名
 * @property string $typeField           设置类型的数据库字段名
 * @property string $categoryField       设置分组的数据库字段名
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface SettingFieldInterface
{
    
    /**
     * 获取配置键名与方法名之间的映射关系
     *
     * @return array
     */
    public function getFieldMap(): array;
    
    /**
     * 获取设置键名的数据库字段名
     *
     * @return string
     */
    public function getNameField(): string;
    
    /**
     * 设置设置键名的数据库字段名
     *
     * @param string $field
     */
    public function setNameField(string $field);
    
    /**
     * 获取设置值的数据库字段名
     *
     * @return string
     */
    public function getValueField(): string;
    
    /**
     * 设置设置值的数据库字段名
     *
     * @param string $field
     */
    public function setValueField(string $field);
    
    /**
     * 获取设置额外数据的数据库字段名
     *
     * @return string
     */
    public function getExtraField(): string;
    
    /**
     * 设置设置额外数据的数据库字段名
     *
     * @param string $field
     */
    public function setExtraField(string $field);
    
    /**
     * 获取设置标题的数据库字段名
     *
     * @return string
     */
    public function getTitleField(): string;
    
    /**
     * 设置设置标题的数据库字段名
     *
     * @param string $field
     */
    public function setTitleField(string $field);
    
    /**
     * 获取设置描述的数据库字段名
     *
     * @return string
     */
    public function getDescriptionField(): string;
    
    /**
     * 设置设置描述的数据库字段名
     *
     * @param string $field
     */
    public function setDescriptionField(string $field);
    
    /**
     * 获取设置类型的数据库字段名
     *
     * @return string
     */
    public function getTypeField(): string;
    
    /**
     * 设置设置类型的数据库字段名
     *
     * @param string $field
     */
    public function setTypeField(string $field);
    
    /**
     * 获取设置分组的数据库字段名
     *
     * @return string
     */
    public function getCategoryField(): string;
    
    /**
     * 设置设置分组的数据库字段名
     *
     * @param string $field
     */
    public function setCategoryField(string $field);
    
}
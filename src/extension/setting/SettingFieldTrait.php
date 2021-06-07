<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\setting;

/**
 * Class SettingFieldTrait
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
trait SettingFieldTrait
{

    /**
     * @var array 配置键名与方法名之间的映射关系
     *            数组的键名值是系统设置数据提供器默认支持的属性，同时也是系统设置数据库表默认需要包含的字段
     */
    private $_mapField = [
        'name' => 'getNameField',
        'value' => 'getValueField',
        'extra' => 'getExtraField',
        'title' => 'getTitleField',
        'description' => 'getDescriptionField',
        'type' => 'getTypeField',
        'category' => 'getCategoryField',
    ];

    private $_map;

    /**
     * 获取配置键名与方法名之间的映射关系
     *
     * @see SettingFieldInterface::getFieldMap()
     *
     * @return array
     */
    public function getFieldMap(): array
    {
        if (null === $this->_map) {
            foreach ($this->_mapField as $field => $method) {
                $this->_map[$field] = $this->$method();
            }
            $this->_map['id'] = 'id';
        }

        return $this->_map;
    }

    /**
     * @var string 设置键名的数据库字段名
     */
    private $_nameField = 'name';

    /**
     * 获取设置键名的数据库字段名
     *
     * @see SettingFieldInterface::getNameField()
     *
     * @return string
     */
    public function getNameField(): string
    {
        return $this->_nameField;
    }

    /**
     * 设置设置键名的数据库字段名
     *
     * @see SettingFieldInterface::setNameField()
     *
     * @param string $field
     */
    public function setNameField(string $field)
    {
        $this->_nameField = $field;
    }

    /**
     * @var string 设置值的数据库字段名
     */
    private $_valueField = 'value';

    /**
     * 获取设置值的数据库字段名
     *
     * @see SettingFieldInterface::getValueField()
     *
     * @return string
     */
    public function getValueField(): string
    {
        return $this->_valueField;
    }

    /**
     * 设置设置值的数据库字段名
     *
     * @see SettingFieldInterface::setValueField()
     *
     * @param string $field
     */
    public function setValueField(string $field)
    {
        $this->_valueField = $field;
    }

    /**
     * @var string 设置额外数据的数据库字段名
     */
    private $_extraField = 'extra';

    /**
     * 获取设置额外数据的数据库字段名
     *
     * @see SettingFieldInterface::getExtraField()
     *
     * @return string
     */
    public function getExtraField(): string
    {
        return $this->_extraField;
    }

    /**
     * 设置设置额外数据的数据库字段名
     *
     * @see SettingFieldInterface::setExtraField()
     *
     * @param string $field
     */
    public function setExtraField(string $field)
    {
        $this->_extraField = $field;
    }

    /**
     * @var string 设置标题的数据库字段名
     */
    private $_titleField = 'title';

    /**
     * 获取设置标题的数据库字段名
     * @see SettingFieldInterface::getTitleField()
     *
     *
     * @return string
     */
    public function getTitleField(): string
    {
        return $this->_titleField;
    }

    /**
     * 设置设置标题的数据库字段名
     *
     * @see SettingFieldInterface::setTitleField()
     *
     * @param string $field
     */
    public function setTitleField(string $field)
    {
        $this->_titleField = $field;
    }

    /**
     * @var string 设置描述的数据库字段名
     */
    private $_descriptionField = 'description';

    /**
     * 获取设置描述的数据库字段名
     *
     * @see SettingFieldInterface::getDescriptionField()
     *
     * @return string
     */
    public function getDescriptionField(): string
    {
        return $this->_descriptionField;
    }

    /**
     * 设置设置描述的数据库字段名
     *
     * @see SettingFieldInterface::setDescriptionField()
     *
     * @param string $field
     */
    public function setDescriptionField(string $field)
    {
        $this->_descriptionField = $field;
    }

    /**
     * @var string 设置类型的数据库字段名
     */
    private $_typeField = 'type';

    /**
     * 获取设置类型的数据库字段名
     *
     * @see SettingFieldInterface::getTypeField()
     *
     * @return string
     */
    public function getTypeField(): string
    {
        return $this->_typeField;
    }

    /**
     * 设置设置类型的数据库字段名
     *
     * @see SettingFieldInterface::setTypeField()
     *
     * @param string $field
     */
    public function setTypeField(string $field)
    {
        $this->_typeField = $field;
    }

    /**
     * @var string 设置分组的数据库字段名
     */
    private $_categoryField = 'category';

    /**
     * 获取设置分组的数据库字段名
     *
     * @see SettingFieldInterface::getCategoryField()
     *
     * @return string
     */
    public function getCategoryField(): string
    {
        return $this->_categoryField;
    }

    /**
     * 设置设置分组的数据库字段名
     *
     * @see SettingFieldInterface::setCategoryField()
     *
     * @param string $field
     */
    public function setCategoryField(string $field)
    {
        $this->_categoryField = $field;
    }

}
<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\menu;

/**
 * Class MenuFieldTrait
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
trait MenuFieldTrait
{
    
    /**
     * @var array 配置键名与方法名之间的映射关系
     *            数组的键名值是菜单数据提供器默认支持的属性，同时也是菜单数据库表默认需要包含的字段
     */
    private $_mapField = [
        'label'       => 'getLabelField',
        'alias'       => 'getAliasField',
        'icon'        => 'getIconField',
        'url'         => 'getUrlField',
        'params'      => 'getParamsField',
        'visible'     => 'getVisibleField',
        'theme'       => 'getThemeField',
        'order'       => 'getOrderField',
        'config'      => 'getConfigField',
        'description' => 'getDescriptionField',
        'category'    => 'getCategoryField',
        'created_by'  => 'getCreatedByField',
    ];
    
    /**
     * 获取配置键名与方法名之间的映射关系
     *
     * @see MenuFieldInterface::getFieldMap()
     *
     * @return array
     */
    public function getFieldMap(): array
    {
        $map = [];
        foreach ($this->_mapField as $field => $method) {
            $map[$field] = $this->$method();
        }
        $map['id'] = 'id';
        $map['parent_id'] = 'parent_id';
        $map['visible_on_dev'] = 'visible_on_dev';
        
        return $map;
    }
    
    /**
     * @var string 菜单名称的数据库字段名
     */
    private $_labelField = 'label';
    
    /**
     * 获取菜单名称的数据库字段名
     *
     * @see MenuFieldInterface::getLabelField()
     *
     * @return string
     */
    public function getLabelField(): string
    {
        return $this->_labelField;
    }
    
    /**
     * 设置菜单名称的数据库字段名
     *
     * @see MenuFieldInterface::setLabelField()
     *
     * @param string $field
     */
    public function setLabelField(string $field)
    {
        $this->_labelField = $field;
    }
    
    /**
     * @var string 菜单别名的数据库字段名
     */
    private $_aliasField = 'alias';
    
    /**
     * 获取菜单别名的数据库字段名
     *
     * @see MenuFieldInterface::getAliasField()
     *
     * @return string
     */
    public function getAliasField(): string
    {
        return $this->_aliasField;
    }
    
    /**
     * 设置菜单别名的数据库字段名
     *
     * @see MenuFieldInterface::setAliasField()
     *
     * @param string $field
     */
    public function setAliasField(string $field)
    {
        $this->_aliasField = $field;
    }
    
    /**
     * @var string 菜单图标的数据库字段名
     */
    private $_iconField = 'icon';
    
    /**
     * 获取菜单图标的数据库字段名
     *
     * @see MenuFieldInterface::getIconField()
     *
     * @return string
     */
    public function getIconField(): string
    {
        return $this->_iconField;
    }
    
    /**
     * 设置菜单图标的数据库字段名
     *
     * @see MenuFieldInterface::setIconField()
     *
     * @param string $field
     */
    public function setIconField(string $field)
    {
        $this->_iconField = $field;
    }
    
    /**
     * @var string url地址的数据库字段名
     */
    private $_urlField = 'url';
    
    /**
     * 获取url地址的数据库字段名
     *
     * @see MenuFieldInterface::getUrlField()
     *
     * @return string
     */
    public function getUrlField(): string
    {
        return $this->_urlField;
    }
    
    /**
     * 设置url地址的数据库字段名
     *
     * @see MenuFieldInterface::setUrlField()
     *
     * @param string $field
     */
    public function setUrlField(string $field)
    {
        $this->_urlField = $field;
    }
    
    /**
     * @var string url地址参数的数据库字段名
     */
    private $_paramsField = 'params';
    
    /**
     * 获取url地址参数的数据库字段名
     *
     * @see MenuFieldInterface::getParamsField()
     *
     * @return string
     */
    public function getParamsField(): string
    {
        return $this->_paramsField;
    }
    
    /**
     * 设置url地址参数的数据库字段名
     *
     * @see MenuFieldInterface::setParamsField()
     *
     * @param string $field
     */
    public function setParamsField(string $field)
    {
        $this->_paramsField = $field;
    }
    
    /**
     * @var string 菜单配置数据的数据库字段名
     */
    private $_configField = 'config';
    
    /**
     * 获取菜单配置数据的数据库字段名
     *
     * @see MenuFieldInterface::getConfigField()
     *
     * @return string
     */
    public function getConfigField(): string
    {
        return $this->_configField;
    }
    
    /**
     * 设置菜单配置数据的数据库字段名
     *
     * @see MenuFieldInterface::setConfigField()
     *
     * @param string $field
     */
    public function setConfigField(string $field)
    {
        $this->_configField = $field;
    }
    
    /**
     * @var string 是否显示菜单的数据库字段名
     */
    private $_visibleField = 'visible';
    
    /**
     * 获取是否显示菜单的数据库字段名
     *
     * @see MenuFieldInterface::getVisibleField()
     *
     * @return string
     */
    public function getVisibleField(): string
    {
        return $this->_visibleField;
    }
    
    /**
     * 设置是否显示菜单的数据库字段名
     *
     * @see MenuFieldInterface::setVisibleField()
     *
     * @param string $field
     */
    public function setVisibleField(string $field)
    {
        $this->_visibleField = $field;
    }
    
    /**
     * @var string 菜单排序的数据库字段名
     */
    private $_orderField = 'order';
    
    /**
     * 获取菜单排序的数据库字段名
     *
     * @see MenuFieldInterface::getOrderField()
     *
     * @return string
     */
    public function getOrderField(): string
    {
        return $this->_orderField;
    }
    
    /**
     * 设置菜单排序的数据库字段名
     *
     * @see MenuFieldInterface::setOrderField()
     *
     * @param string $field
     */
    public function setOrderField(string $field)
    {
        $this->_orderField = $field;
    }
    
    /**
     * @var string 菜单所属的主题的数据库字段名
     */
    private $_themeField = 'theme';
    
    /**
     * 获取菜单所属的主题的数据库字段名
     *
     * @see MenuFieldInterface::getThemeField()
     *
     * @return string
     */
    public function getThemeField(): string
    {
        return $this->_themeField;
    }
    
    /**
     * 设置菜单所属的主题的数据库字段名
     *
     * @see MenuFieldInterface::setThemeField()
     *
     * @param string $field
     */
    public function setThemeField(string $field)
    {
        $this->_themeField = $field;
    }
    
    /**
     * @var string 菜单描述的数据库字段名
     */
    private $_descriptionField = 'description';
    
    /**
     * 获取菜单描述的数据库字段名
     *
     * @see MenuFieldInterface::getDescriptionField()
     *
     * @return string
     */
    public function getDescriptionField(): string
    {
        return $this->_descriptionField;
    }
    
    /**
     * 设置菜单描述的数据库字段名
     *
     * @see MenuFieldInterface::setDescriptionField()
     *
     * @param string $field
     */
    public function setDescriptionField(string $field)
    {
        $this->_descriptionField = $field;
    }
    
    /**
     * @var string 菜单分类的数据库字段名
     */
    private $_categoryField = 'category';
    
    /**
     * 获取菜单分类的数据库字段名
     *
     * @see MenuFieldInterface::getCategoryField()
     *
     * @return string
     */
    public function getCategoryField(): string
    {
        return $this->_categoryField;
    }
    
    /**
     * 设置菜单分类的数据库字段名
     *
     * @see MenuFieldInterface::setCategoryField()
     *
     * @param string $field
     */
    public function setCategoryField(string $field)
    {
        $this->_categoryField = $field;
    }
    
    /**
     * @var string 菜单创建类型的数据库字段名
     */
    private $_createdByField = 'created_by';
    
    /**
     * 获取菜单创建类型的数据库字段名
     *
     * @see MenuFieldInterface::getCreatedByField()
     *
     * @return string
     */
    public function getCreatedByField(): string
    {
        return $this->_createdByField;
    }
    
    /**
     * 设置菜单创建类型的数据库字段名
     *
     * @see MenuFieldInterface::setCreatedByField()
     *
     * @param string $field
     */
    public function setCreatedByField(string $field)
    {
        $this->_createdByField = $field;
    }
    
}
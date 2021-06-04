<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\menu\components;

/**
 * 视图页面服务接口
 *
 * @property string $showTitleField 显示菜单标题的字段名
 * @property string $pkField 主键字段名
 * @property string $parentIdField 父级id字段名
 * @property string $title 页面标题
 * @property string $theme 主题名
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface PageServiceInterface
{
    
    /**
     * 获取主键字段名
     *
     * @return string
     */
    public function getPkField(): string;
    
    /**
     * 设置主键字段名
     *
     * @param string $field
     */
    public function setPkField($field);
    
    /**
     * 获取父级id字段名
     *
     * @return string
     */
    public function getParentIdField(): string;
    
    /**
     * 设置父级id字段名
     *
     * @param string $field
     */
    public function setParentIdField($field);
    
    /**
     * 获取显示菜单标题的字段名
     *
     * @return string
     */
    public function getShowTitleField(): string;
    
    /**
     * 设置显示菜单标题的字段名
     *
     * @param string $field
     */
    public function setShowTitleField($field);
    
    /**
     * 设置用来索引符合$url要求的菜单数据
     *
     * @param null|string $url
     *
     * @return $this
     */
    public function setUrl($url);
    
    /**
     * 设置$url地址所属的菜单级别
     *
     * @param int $level
     *
     * @return $this
     */
    public function setLevel($level);
    
    /**
     * 设置父级id
     *
     * @param int $id
     *
     * @return $this
     */
    public function setParentId($id);
    
    /**
     * 设置面包屑导航url参数
     *
     * @param array|callable $params
     *
     * @return $this
     */
    public function setBreadcrumbsUrlParams($params);
    
    /**
     * 设置面包屑导航基础地址
     *
     * @param null|string $url
     *
     * @return $this
     */
    public function setBreadcrumbsBaseUrl($url);
    
    /**
     * 设置查询条件
     *
     * @param array $conditions
     *
     * @return $this
     */
    public function setConditions($conditions);
    
    /**
     * 是否开启调试模式
     *
     * @param bool $debug
     *
     * @return $this
     */
    public function debug(bool $debug = true);
    
    /**
     * 设置主题名
     *
     * @param string $theme
     *
     * @return $this
     */
    public function setTheme($theme);
    
    /**
     * 获取主题名
     *
     * @return string
     */
    public function getTheme();
    
    /**
     * 根据`$condition`条件生成指定分类的导航菜单数据
     *
     * @param string|array $category 分类ID
     * @param array $condition 查询条件
     *
     * @return array
     */
    public function generateNavigation($category, array $condition = []): array;
    
    /**
     * 获取页面标题
     *
     * @param string|callable $defaultTitle 默认标题，当获取不到标题时默认显示的标题
     *
     * @return string
     */
    public function getTitle($defaultTitle = ''): string;
    
    /**
     * 根据查询条件获取面包屑导航
     *
     * @param bool $useUrl 最后一个面包屑是否添加url链接
     * @param int $levelCount 层级总数，表示最多获取多少层父级菜单数据
     *
     * @return array
     * @see \yii\widgets\Breadcrumbs
     */
    public function getBreadcrumbs($useUrl = false, $levelCount = 10): array;
    
    /**
     * 生成面包屑
     *
     * @param string|callable $defaultTitle 默认标题
     *
     * @return array
     */
    public function generateBreadcrumbs($defaultTitle = ''): array;
    
    /**
     * 根据查询条件获取菜单数据
     *
     * @return array
     */
    public function getMenus(): array;
    
    /**
     * 重置查询参数
     *
     * @return $this
     */
    public function reset();
    
}
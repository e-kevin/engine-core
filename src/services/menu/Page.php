<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\menu;

use EngineCore\services\menu\components\PageServiceInterface;
use EngineCore\services\Menu;
use EngineCore\Ec;
use EngineCore\helpers\ArrayHelper;
use EngineCore\base\Service;
use Yii;
use yii\base\InvalidConfigException;

/**
 * 视图页面服务类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Page extends Service implements PageServiceInterface
{
    
    /**
     * @var Menu 父级服务类
     */
    public $service;
    
    private $_pkField = 'id';
    
    /**
     * {@inheritdoc}
     */
    public function getPkField(): string
    {
        return $this->_pkField;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setPkField($field)
    {
        $this->_pkField = $field;
    }
    
    private $_parentIdField = 'parent_id';
    
    /**
     * {@inheritdoc}
     */
    public function getParentIdField(): string
    {
        return $this->_parentIdField;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setParentIdField($field)
    {
        $this->_parentIdField = $field;
    }
    
    private $_titleField;
    
    /**
     * {@inheritdoc}
     */
    public function getShowTitleField(): string
    {
        if (null === $this->_titleField) {
            $this->setShowTitleField($this->service->getConfig()->getProvider()->getAliasField());
        }
        
        return $this->_titleField;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setShowTitleField($field)
    {
        $this->_titleField = $field;
    }
    
    private $_url;
    
    /**
     * {@inheritdoc}
     */
    public function setUrl($url)
    {
        $this->_url = $url === null ? '/' . Yii::$app->controller->action->getUniqueId() : $url;
        
        return $this;
    }
    
    private $_level;
    
    /**
     * {@inheritdoc}
     */
    public function setLevel($level)
    {
        $this->_level = $level;
        
        return $this;
    }
    
    private $_parentId;
    
    /**
     * {@inheritdoc}
     */
    public function setParentId($id)
    {
        $this->_parentId = $id;
        
        return $this;
    }
    
    private $_breadcrumbsUrlParams;
    
    /**
     * {@inheritdoc}
     *
     * @param array|callable $params 如果为可调函数，则第一个参数为父级菜单数组
     *
     * @example
     * ```php
     *      function($menu) {
     *          return [
     *              'pid' => $menu['id'],
     *          ];
     *      }
     * ```
     * @see getBreadcrumbs()
     */
    public function setBreadcrumbsUrlParams($params)
    {
        $this->_breadcrumbsUrlParams = $params;
        
        return $this;
    }
    
    private $_breadcrumbsBaseUrl;
    
    /**
     * {@inheritdoc}
     */
    public function setBreadcrumbsBaseUrl($url)
    {
        $this->_breadcrumbsBaseUrl = $url ?: '/' . Yii::$app->controller->action->getUniqueId();
        
        return $this;
    }
    
    private $_conditions;
    
    /**
     * {@inheritdoc}
     */
    public function setConditions($conditions)
    {
        $this->_conditions = $conditions;
        
        return $this;
    }
    
    private $_debug;
    
    /**
     * {@inheritdoc}
     */
    public function debug(bool $debug = true)
    {
        $this->_debug = $debug;
        
        return $this;
    }
    
    private $_theme;
    
    /**
     * {@inheritdoc}
     */
    public function setTheme($theme)
    {
        $this->_theme = $theme;
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getTheme()
    {
        if ($this->_theme === null) {
            $this->setTheme(Ec::$service->getExtension()->getThemeRepository()->getConfig('name'));
        }
        
        return $this->_theme;
    }
    
    /**
     * {@inheritdoc}
     * @return array 如果$category分类ID为字符串，则返回该分类的一维数组，
     * 否则返回二维数组['backend' => [], 'frontend' => [], 'main' => []]
     */
    public function generateNavigation($category, array $condition = []): array
    {
        $menus = $this->_getMenusByCategory($category, $condition);
        foreach ($menus as &$row) {
            $row = ArrayHelper::listToTree($row);
        }
        
        return is_string($category) ? ($menus[$category] ?? []) : $menus;
    }
    
    /**
     * 根据查询条件获取指定[单个|多个]分类的菜单数据
     *
     * @param string|array $category  分类ID
     * @param array        $condition 查询条件
     *
     * @return array ['backend' => [], 'frontend' => [], 'main' => []]
     */
    private function _getMenusByCategory($category, array $condition = [])
    {
        $menus = ArrayHelper::listSearch(
            $this->service->getConfig()->getProvider()->getAll(),
            array_merge(
                [
                    'category' => [is_array($category) ? 'in' : 'eq', $category],
                    'theme'    => ['in', [
                        Ec::$service->getExtension()->getThemeRepository()->getDefaultConfig('name'), // 默认主题
                        $this->getTheme() // 当前主题
                    ]],
                ],
                $condition
            )
        );
        
        return $menus ? ArrayHelper::index($menus, 'id', 'category') : [];
    }
    
    /**
     * {@inheritdoc}
     */
    public function getTitle($defaultTitle = ''): string
    {
        if ($menus = $this->getMenus()) {
            return $menus[$this->getShowTitleField()];
        } elseif ($defaultTitle) {
            if (is_callable($defaultTitle)) {
                $defaultTitle = call_user_func($defaultTitle);
            }
            
            return ($defaultTitle ?: $this->_url) . ' ~'; // 添加默认标题标记`~`
        } else {
            return $this->_url . ' ~';
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function getBreadcrumbs($useUrl = false, $levelCount = 10): array
    {
        $breadcrumbs = [];
        if (null === $this->_parentId) {
            if ($menus = $this->getMenus()) {
                $breadcrumbs[$levelCount] = ['label' => $menus[$this->getShowTitleField()]];
                if ($useUrl && strpos($menus['url'], '/') !== false) {
                    $breadcrumbs[$levelCount]['url'] = array_merge([$menus['url']], $menus['params']);
                }
                $levelCount--;
                $this->setParentId($menus[$this->getParentIdField()]);
            } else {
                $breadcrumbs[$levelCount] = ['label' => $this->_url . ' ~']; // 添加默认标题标记`~`
                
                return $breadcrumbs;
            }
        }
        
        // 递归获取父级面包屑导航
        $allMenus = $this->service->getConfig()->getProvider()->getAll();
        while ($levelCount > 0 && $this->_parentId !== null) {
            if ($parentMenus = $allMenus[$this->_parentId] ?? []) {
                $breadcrumbs[$levelCount]['label'] = $parentMenus[$this->getShowTitleField()];
                // 如果父级菜单存在有效url或面包屑导航基础地址不为空，则为父级面包屑导航附加链接
                if (strpos($parentMenus['url'], '/') !== false || null !== $this->_breadcrumbsBaseUrl) {
                    /**
                     * @see setBreadcrumbsUrlParams()
                     */
                    if (is_callable($this->_breadcrumbsUrlParams)) {
                        $breadcrumbsUrlParams = call_user_func($this->_breadcrumbsUrlParams, $parentMenus);
                    } else {
                        $breadcrumbsUrlParams = $this->_breadcrumbsUrlParams;
                    }
                    $breadcrumbs[$levelCount]['url'] = array_merge(
                        [$this->_breadcrumbsBaseUrl ?: $parentMenus['url']], // 优先使用面包屑导航基础地址
                        $parentMenus['params'],
                        $breadcrumbsUrlParams ?: []
                    );
                }
                $this->setParentId($parentMenus[$this->getParentIdField()]);
            } else {
                $this->setParentId(null);
            }
            $levelCount--;
        }
        
        ksort($breadcrumbs);
        
        return $breadcrumbs;
    }
    
    /**
     * {@inheritdoc}
     */
    public function generateBreadcrumbs($defaultTitle = ''): array
    {
        $breadcrumbs = [];
        if ($menus = $this->getMenus()) {
            if (is_callable($defaultTitle)) {
                $defaultTitle = call_user_func($defaultTitle);
            }
            $breadcrumbs['label'] = $defaultTitle ?: $menus[$this->getShowTitleField()]; // 默认标题优先级最高
            // 如果父级菜单存在有效url或面包屑导航基础地址不为空，则为父级面包屑导航附加链接
            if (strpos($menus['url'], '/') !== false || null !== $this->_breadcrumbsBaseUrl) {
                /**
                 * @see setBreadcrumbsUrlParams()
                 */
                if (is_callable($this->_breadcrumbsUrlParams)) {
                    $urlParams = call_user_func($this->_breadcrumbsUrlParams, $menus);
                } else {
                    $urlParams = $this->_breadcrumbsUrlParams;
                }
                $breadcrumbs['url'] = array_merge(
                    [$this->_breadcrumbsBaseUrl ?: $menus['url']],  // 优先使用面包屑导航基础地址
                    $menus['params'],
                    $urlParams ?: []
                );
            }
        }
        
        return $breadcrumbs;
    }
    
    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->_url = $this->_level = $this->_conditions = $this->_theme =
        $this->_breadcrumbsUrlParams = $this->_breadcrumbsBaseUrl = $this->_parentId = $this->_menus = null;
        
        return $this;
    }
    
    private $_menus;
    
    /**
     * {@inheritdoc}
     */
    public function getMenus(): array
    {
        if (null === $this->_menus) {
            $this->_initConditions();
            $provider = $this->service->getConfig()->getProvider();
            if (isset($this->_conditions[$this->getPkField()])) {
                $this->_menus = $provider->getAll()[$this->_conditions[$this->getPkField()]] ?? [];
            } elseif (isset($this->_conditions[$this->getParentIdField()])) {
                $this->_menus = $provider->getAll()[$this->_conditions[$this->getParentIdField()]] ?? [];
            } else {
                // 优先获取主题菜单
                if ($oldMenus = ArrayHelper::listSearch($provider->getAllByLevel($this->_level), $this->_conditions)) {
                    $this->_menus = ArrayHelper::listSearch($oldMenus, ['theme' => $this->getTheme()]) ?: $oldMenus;
                } else {
                    $this->_menus = $oldMenus;
                }
            }
        }
        
        // 开启调试模式
        if ($this->_debug) {
            Ec::dump([
                'conditions' => $this->_conditions,
                'url'        => $this->_url,
                'level'      => $this->_level,
                'parent_id'  => $this->_parentId,
                'menus'      => $this->_menus,
            ]);
        }
        
        return $this->_menus[0] ?? $this->_menus;
    }
    
    /**
     * 初始化查询条件
     *
     * @throws InvalidConfigException
     */
    private function _initConditions()
    {
        // 设置默认url地址
        if (null === $this->_url) {
            $this->setUrl(null);
        }
        // $conditions查询条件优先级最高
        if (null !== $this->_conditions) {
            // 如果查询条件包含主键，则优先级最高，并剔除其他查询条件
            if (isset($this->_conditions[$this->getPkField()])) {
                $this->_conditions = [$this->getPkField() => $this->_conditions[$this->getPkField()]];
            } // 父类ID字段查询条件
            elseif (isset($this->_conditions[$this->getParentIdField()]) || null !== $this->_parentId) {
                $this->_conditions = [$this->getParentIdField() => $this->_conditions[$this->getParentIdField()] ?? $this->_parentId];
            } // 其他查询条件
            else {
                $this->_level = $this->_conditions['level'] ?? null;
                if (null === $this->_level) {
                    throw new InvalidConfigException('The "level" property must be set.');
                }
                if (!isset($this->_conditions['url'])) {
                    $this->_conditions['url'] = $this->_url;
                } else {
                    $this->_url = $this->_conditions['url'];
                }
            }
        } elseif (null === $this->_level) {
            throw new InvalidConfigException('The "level" property must be set.');
        } // 默认使用`$url`和`$level`查询条件即可定位到所需的菜单数据
        else {
            $this->_conditions = [
                'url'   => $this->_url,
                'level' => $this->_level,
            ];
            if (null !== $this->_parentId) {
                $this->_conditions[$this->getParentIdField()] = $this->_parentId;
            }
        }
        // 添加对多主题菜单的支持
        $this->_conditions['theme'] = ['in', [
            Ec::$service->getExtension()->getThemeRepository()->getDefaultConfig('name'),
            $this->getTheme(),
        ]];
    }
    
}
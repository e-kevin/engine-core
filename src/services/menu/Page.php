<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
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
    
    /**
     * @var string 显示菜单标题的字段名
     */
    public $titleField = 'label';
    
    /**
     * @var string 主键字段名
     */
    public $pkField = 'id';
    
    /**
     * @var string 父级id字段名
     */
    public $parentIdField = 'parent_id';
    
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
    public function setTheme($theme = null)
    {
        $this->_theme = $theme ?: Yii::$app->params['themeConfig']['name'];
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getTheme()
    {
        if ($this->_theme === null) {
            $this->setTheme();
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
        // 菜单数据不存在时，调用系统默认的菜单数据
        if (empty($menus)) {
            $this->service->getConfig()->setProvider([
                'class' => 'wocenter\core\MenuProvider',
            ]);
            $menus = $this->_getMenusByCategory($category, $condition);
        }
        foreach ($menus as &$row) {
            $row = ArrayHelper::listToTree($row);
        }
        
        return is_string($category) ? ($menus[$category] ?? []) : $menus;
    }
    
    /**
     * 根据查询条件获取指定[单个|多个]分类的菜单数据
     *
     * @param string|array $category 分类ID
     * @param array $condition 查询条件
     *
     * @return array ['backend' => [], 'frontend' => [], 'main' => []]
     */
    private function _getMenusByCategory($category, array $condition = [])
    {
        $menus = ArrayHelper::listSearch(
            $this->service->getConfig()->getProvider()->getAll(),
            array_merge(
                [
                    'category_id' => [is_array($category) ? 'in' : 'eq', $category],
                    'theme' => ['in', ['common', $this->getTheme()]], // 获取公共菜单和主题菜单
                ],
                $condition
            )
        );
        
        return $menus ? ArrayHelper::index($menus, 'id', 'category_id') : [];
    }
    
    /**
     * {@inheritdoc}
     */
    public function getTitle($defaultTitle = ''): string
    {
        if ($menus = $this->getMenus()) {
            return $menus[$this->titleField];
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
    public function getBreadcrumbs($levelCount = 10, $useUrl = false): array
    {
        $breadcrumbs = [];
        if (null === $this->_parentId) {
            if ($menus = $this->getMenus()) {
                $breadcrumbs[$levelCount] = ['label' => $menus[$this->titleField]];
                if ($useUrl && strpos($menus['url'], '/') !== false) {
                    $breadcrumbs[$levelCount]['url'] = array_merge([$menus['url']], $menus['params']);
                }
                $levelCount--;
                $this->setParentId($menus[$this->parentIdField]);
            } else {
                $breadcrumbs[$levelCount] = ['label' => $this->_url . ' ~']; // 添加默认标题标记`~`
                
                return $breadcrumbs;
            }
        }
        
        $provider = $this->service->getConfig()->getProvider();
        while ($levelCount > 0 && $this->_parentId !== null) {
            if ($parentMenus = $provider->getAll()[$this->_parentId] ?? []) {
                $breadcrumbs[$levelCount]['label'] = $parentMenus[$this->titleField];
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
                        [$this->_breadcrumbsBaseUrl ?: $parentMenus['url']],
                        $parentMenus['params'],
                        $breadcrumbsUrlParams ?: []
                    );
                }
                $this->setParentId($parentMenus[$this->parentIdField]);
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
            $breadcrumbs['label'] = $defaultTitle ?: $menus[$this->titleField]; // 默认标题优先级最高
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
                    [$this->_breadcrumbsBaseUrl ?: $menus['url']],
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
        $this->_breadcrumbsUrlParams = $this->_breadcrumbsBaseUrl = $this->_parentId = null;
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMenus(): array
    {
        $this->_initConditions();
        $provider = $this->service->getConfig()->getProvider();
        if (isset($this->_conditions[$this->pkField])) {
            $menus = $provider->getAll()[$this->_conditions[$this->pkField]] ?? [];
        } elseif (isset($this->_conditions[$this->parentIdField])) {
            $menus = $provider->getAll()[$this->_conditions[$this->parentIdField]] ?? [];
        } else {
            // 优先获取主题菜单
            if ($oldMenus = ArrayHelper::listSearch($provider->getAll($this->_level), $this->_conditions)) {
                $menus = ArrayHelper::listSearch($oldMenus, ['theme' => $this->_theme]) ?: $oldMenus;
            } else {
                $menus = $oldMenus;
            }
        }
        
        // 开启调试模式
        if ($this->_debug) {
            Ec::dump([
                'conditions' => $this->_conditions,
                'url' => $this->_url,
                'level' => $this->_level,
                'parent_id' => $this->_parentId,
                'menus' => $menus,
            ]);
        }
        
        return $menus[0] ?? $menus;
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
        // 设置默认主题
        if (null === $this->_theme) {
            $this->setTheme();
        }
        // $conditions查询条件优先级最高
        if (null !== $this->_conditions) {
            // 如果查询条件包含主键，则优先级最高，并剔除其他查询条件
            if (isset($this->_conditions[$this->pkField])) {
                $this->_conditions = [$this->pkField => $this->_conditions[$this->pkField]];
            } // 父类ID字段查询条件
            elseif (isset($this->_conditions[$this->parentIdField]) || null !== $this->_parentId) {
                $this->_conditions = [$this->parentIdField => $this->_conditions[$this->parentIdField] ?? $this->_parentId];
            } // 其他查询条件
            else {
                $this->_level = $this->_conditions['level'] ?? null;
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
                'url' => $this->_url,
                'level' => $this->_level,
            ];
            if (null !== $this->_parentId) {
                $this->_conditions[$this->parentIdField] = $this->_parentId;
            }
        }
        // 添加对多主题菜单的支持
        $this->_conditions['theme'] = ['in', ['common', $this->_theme]];
    }
    
}

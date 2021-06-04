<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\services\menu;

use EngineCore\Ec;
use EngineCore\enums\VisibleEnum;
use EngineCore\extension\menu\FileProvider;
use EngineCore\services\menu\components\ConfigServiceInterface;
use EngineCore\services\Menu;
use EngineCore\extension\menu\MenuProviderInterface;
use EngineCore\helpers\ArrayHelper;
use EngineCore\helpers\UrlHelper;
use EngineCore\base\Service;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\VarDumper;

/**
 * 配置服务类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Config extends Service implements ConfigServiceInterface
{
    
    /**
     * @var Menu 父级服务类
     */
    public $service;
    
    private $_all;
    
    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        if (null === $this->_all) {
            $this->_all = $this->getProvider()->getAll();
        }
        
        return $this->_all;
    }
    
    /**
     * {@inheritdoc}
     */
    public function clearCache()
    {
        $this->_all = null;
        $this->getProvider()->clearCache();
    }
    
    /**
     * @var MenuProviderInterface 菜单数据提供器
     */
    private $_provider;
    
    /**
     * {@inheritdoc}
     */
    public function getProvider(): MenuProviderInterface
    {
        if (null === $this->_provider) {
            $this->setProvider($this->providerDefinition());
        }
        
        return $this->_provider;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setProvider($provider)
    {
        $this->_provider = Ec::createObject($provider, [], MenuProviderInterface::class);
    }
    
    /**
     * 设置数据提供器默认配置
     *
     * @return string|array|callable
     */
    private function providerDefinition()
    {
        // 存在自定义菜单数据提供器则优先获取该数据提供器，否则使用系统默认的菜单数据提供器
        if (Yii::$container->has('MenuProvider')) {
            $definition = Yii::$container->definitions['MenuProvider'];
        } else {
            $definition['class'] = FileProvider::class;
        }
        
        return $definition;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCreatedByList(): array
    {
        return [
            MenuProviderInterface::CREATED_BY_USER      => Yii::t('ec/menu', 'Created by user'),
            MenuProviderInterface::CREATED_BY_EXTENSION => Yii::t('ec/menu', 'Created by extension'),
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMenuConfig(): array
    {
        $menuFile = Yii::getAlias(Ec::$service->getExtension()->getEnvironment()->menuFile);
        $userMenuFile = Yii::getAlias(Ec::$service->getExtension()->getEnvironment()->userMenuFile);
        $menus = ArrayHelper::superMerge(
            require("$menuFile"),
            require("$userMenuFile"),
            Yii::$app->params[MenuProviderInterface::MENU_KEY] ?? []
        );
        
        foreach ($menus as $category => &$menu) {
            $menu = $this->initMenu($menu, $category, 1);
        }
        
        return $menus;
    }
    
    /**
     * {@inheritdoc}
     */
    public function sync(): bool
    {
//        Ec::$service->getSystem()->getCache()->getComponent()->delete(Local::CACHE_LOCAL_EXTENSION_CONFIGURATION);
//        Ec::$service->getExtension()->getDb()->clearCache();
        /**
         * 获取数据库里的所有应用的菜单数据，不包括用户自建菜单数据
         *
         * @var array $menuInDb ['backend' => [], 'frontend' => [], 'main' => []]
         */
//        $menuInDb = ArrayHelper::listSearch($this->getProvider()->getAll(), [
//            'category'  => ['in', array_keys(AppEnum::list())],
//            'created_by' => MenuProviderInterface::CREATED_BY_EXTENSION,
//        ]);
//        $menuInDb = $menuInDb ? ArrayHelper::index($menuInDb, 'id', 'category') : [];
        $menuInDb = [];
        
        
        // 需要更新或新增操作的数据库菜单数据
        $todoMenus = [];
//        $all = ArrayHelper::index($this->getProvider()->getAll(), 'id', 'category');
        foreach ($this->getMenuConfig() as $category => $menus) {
            $todoMenus = ArrayHelper::merge($todoMenus, $this->_compareMenus($menus, $menuInDb[$category]));
        }
//        Ec::dump($all);
        exit;
        
        
        // 修正菜单数据
        $this->_fixMenuData($menuInDb, $todoMenus);
        // 执行数据库操作
        $this->_todoMenus($todoMenus);
        // 删除菜单缓存
        $this->clearCache();
        
        return true;
    }
    
    /**
     * 对比数据库和配置里的菜单数据，获取需要更新或新增的菜单数据
     *
     * @param array   $menus     菜单配置信息
     * @param array   &$menuInDb 数据库里的菜单数据
     * @param integer $parentId  父级ID
     *
     * @return array
     * ```php
     * [
     *      'insert' => [], // 需要插入数据库的菜单数据
     *      'update' => [], // 需要更新数据库的菜单数据
     *      'config' => [], // 扩展配置里的菜单配置数据
     * ]
     * ```
     */
    private function _compareMenus(array $menus, &$menuInDb, $parentId = 0)
    {
        if (empty($menus)) {
            return [];
        }
        $arr = [];
        foreach ($menus as $menu) {
            $items = ArrayHelper::remove($menu, 'items', []);
            $menu['parent_id'] = $parentId; // 添加菜单父级ID
            $this->_formatFields($menu);
            // 查询条件
            $condition = [
                'parent_id' => $menu['parent_id'],
                'label'     => $menu['label'],
                'url'       => $menu['url'],
                'theme'     => $menu['theme'],
            ];
            // 剔除数据库里不存在的字段
            unset($menu['level']);
            // 数据库里存在数据
            if ($data = ArrayHelper::listSearch($menuInDb, $condition)) {
                $data = $data[0];
                $this->_formatFields($data);
                $menu['id'] = $data['id'];
                // 检测数据是否改变
                $this->_isChanged($menu, $data, $arr);
            } else {
                if ($items) {
                    $table = 'viMJHk_menu';
                    $command = Yii::$app->getDb()->createCommand();
                    // 不存在父级菜单则递归新建父级菜单
                    // fixme 开启事务时容易因为锁表导致数据查询出错
                    if ($command->insert($table, $menu)->execute()) {
                        $menu['id'] = $command->select('id')->where($condition)->scalar();
                        $menuInDb[] = $menu; // 同步更新数据库已有数据
                    }
                } else {
                    ksort($menu); // 排序，保持键序一致，便于批量插入数据库
                    $arr['insert'][] = $menu; // 不存在子类菜单则列入待新建数组里
                    $menuInDb[] = $menu; // 同步更新数据库已有数据
                }
            }
            if ($items) {
                $arr = ArrayHelper::merge($arr, $this->_compareMenus($items, $menuInDb, $menu['id']));
            }
            $arr['config'][] = $menu;
        }
        
        return $arr;
    }
    
    /**
     * 格式化字段，用于储存进数据库
     *
     * @param array &$data
     */
    private function _formatFields(&$data)
    {
        $data['config'] = $data['config'] ? json_encode($data['config']) : '';
        $data['params'] = $data['params'] ? UrlHelper::getUrlQuery($data['params']) : '';
    }
    
    /**
     * 检测数据是否改变
     *
     * @param $menu
     * @param $menuInDb
     * @param $arr
     */
    private function _isChanged($menu, $menuInDb, &$arr)
    {
        foreach ($menu as $key => $value) {
            if ($menuInDb[$key] != $value) {
                $arr['update'][$menu['id']][$key] = $value;
            }
        }
    }
    
    /**
     * 对比数据库已有数据，修正待写入数据库的菜单数据
     *
     * @param array $menuInDb 数据库里的菜单数据
     * @param array $arr      待处理数组
     *                        ```php
     *                        [
     *                        'insert' => [], // 需要插入数据库的菜单数据
     *                        'update' => [], // 需要更新数据库的菜单数据
     *                        'config' => [], // 扩展配置里的菜单配置数据
     *                        ]
     *                        ```
     */
    private function _fixMenuData($menuInDb = [], &$arr = [])
    {
        if (isset($arr['config'])) {
            foreach ($menuInDb as $app => $menus) {
                if (empty($menus)) {
                    continue;
                }
                foreach ($menus as $menu) {
                    // 配置数据里已删除，则删除数据库对应数据
                    if (
                        !ArrayHelper::listSearch($arr['config'], [
                            'category' => $menu['category'],
                            'label'    => $menu['label'],
                            'url'      => $menu['url'],
                            'theme'    => $menu['theme'],
                        ])
                        && (!key_exists($menu['id'], $arr['update'] ?? []))
                    ) {
                        $arr['delete'][$menu['id']] = $menu['id'];
                    }
                }
            }
        }
    }
    
    /**
     * 执行所有菜单操作
     *
     * @param array $array 需要操作的数据 ['delete', 'insert', 'update']
     *                     ```php
     *                     [
     *                     'insert' => [], // 需要插入数据库的菜单数据
     *                     'update' => [], // 需要更新数据库的菜单数据
     *                     'delete' => [], // 需要删除的数据库菜单数据
     *                     ]
     *                     ```
     */
    private function _todoMenus($array)
    {
        if (isset($array['delete']) && !empty($array['delete'])) {
            Yii::$app->getDb()->createCommand()
                     ->delete($this->getProvider()->getModel()::tableName(), ['id' => $array['delete']])
                     ->execute();
        }
        if (isset($array['insert']) && !empty($array['insert'])) {
            Yii::$app->getDb()->createCommand()
                     ->batchInsert($this->getProvider()->getModel()::tableName(), array_keys($array['insert'][0]), $array['insert'])
                     ->execute();
        }
        if (isset($array['update']) && !empty($array['update'])) {
            foreach ($array['update'] as $id => $menu) {
                Yii::$app->getDb()->createCommand()
                         ->update($this->getProvider()->getModel()::tableName(), $menu, ['id' => $id])
                         ->execute();
            }
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function initMenu($items, $category, $level): array
    {
        // 默认排序起始号，通常同级菜单里，不会有超过50个菜单的情况，系统已为开发者预留了足够的调整空间
        $sortOrder = 50;
        foreach ($items as $key => $item) {
            $this->_normalize($item, $category, $level, $sortOrder);
            ++$sortOrder;
            if (isset($item['items'])) {
                $item['items'] = $this->initMenu($item['items'], $category, $level + 1);
            }
            // 转换菜单数据键名，便于使用\EngineCore\helpers\ArrayHelper::merge()合并相同键名的数组到同一分组下
            if (is_string($key)) {
                if (strpos($key, '@' !== false)) {
                    $item['theme'] = substr($key, 1, strpos($key, '/'));
                }
                $uniqueKey = "@{$item['theme']}/$key";
            } else {
                $uniqueKey = "@{$item['theme']}/{$item['label']}";
            }
            $items[$uniqueKey] = ArrayHelper::merge($items[$uniqueKey] ?? [], $item);
            unset($items[$key]);
        }
        
        return $items;
    }
    
    /**
     * 补全修正菜单数据，确保可用字段存在于菜单属性字段里
     *
     * @see MenuFieldInterface
     *
     * @param array  &$item     单条菜单数据
     * @param string $category  菜单分类
     * @param int    $level     菜单层级数
     * @param int    $sortOrder 排序序号
     *
     * @throws InvalidConfigException
     */
    private function _normalize(&$item = [], $category, $level, $sortOrder)
    {
        if (!isset($item['label'])) {
            throw new InvalidConfigException("The `label` option is required.");
        }
        $emptyArray = ['params', 'config'];
        foreach ($emptyArray as $field) {
            $item[$field] = $item[$field] ?? [];
            if (!is_array($item[$field])) {
                throw new InvalidConfigException("Unsupported type for {$field}: " . gettype($item[$field]) .
                    "\n" . VarDumper::dumpAsString($item));
            }
        }
        $item['alias'] = $item['alias'] ?? $item['label'];
        $item['url'] = $item['url'] ?? '#'; // url默认为`#`
        $item['order'] = $item['order'] ?? $sortOrder;
        $item['visible'] = intval($item['visible'] ?? VisibleEnum::INVISIBLE);
        $item['theme'] = $item['theme'] ?? Ec::$service->getExtension()->getThemeRepository()->getDefaultConfig('name');
        // 补全空字符串的字段
        $emptyString = ['icon', 'description'];
        foreach ($emptyString as $field) {
            if (!isset($item[$field])) {
                $item[$field] = '';
            }
        }
        $item['category'] = $category;
        $item['created_by'] = $item['created_by'] ?? MenuProviderInterface::CREATED_BY_EXTENSION;
        $item['visible_on_dev'] = intval($item['visible_on_dev'] ?? VisibleEnum::VISIBLE);
        // 添加菜单层级数
        $item['level'] = $level;
    }
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\menu;

use EngineCore\Ec;
use EngineCore\enums\AppEnum;
use EngineCore\services\extension\Local;
use EngineCore\services\menu\components\ConfigServiceInterface;
use EngineCore\services\Menu;
use EngineCore\extension\menu\MenuProviderInterface;
use EngineCore\helpers\ArrayHelper;
use EngineCore\helpers\UrlHelper;
use EngineCore\base\Service;
use Yii;
use yii\base\InvalidConfigException;

/**
 * 配置服务类
 *
 * @property MenuProviderInterface $provider
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ConfigService extends Service implements ConfigServiceInterface
{
    
    /**
     * @var Menu 父级服务类
     */
    public $service;
    
    /**
     * @var MenuProviderInterface 菜单数据提供者
     */
    private $_provider;
    
    /**
     * @inheritdoc
     */
    public function __construct(MenuProviderInterface $provider, array $config = [])
    {
        $this->_provider = $provider;
        parent::__construct($config);
    }
    
    /**
     * @inheritdoc
     */
    public function clearCache()
    {
        $this->getProvider()->clearCache();
    }
    
    /**
     * @inheritdoc
     */
    public function getProvider(): MenuProviderInterface
    {
        if (null === $this->_provider) {
            $this->setProvider();
        }
        
        return $this->_provider;
    }
    
    /**
     * @inheritdoc
     */
    public function setProvider($config = [])
    {
        if (!isset($config['class'])) {
            throw new InvalidConfigException('The `$provider` property must contain the `class` key name.');
        } elseif (!is_subclass_of($config['class'], MenuProviderInterface::class)) {
            throw new InvalidConfigException("The {$config['class']} must implement `" . MenuProviderInterface::class . "`.");
        }
        $this->_provider = Yii::createObject($config);
    }
    
    /**
     * @inheritdoc
     */
    public function sync(): bool
    {
        Ec::$service->getExtension()->getCache()->delete(Local::CACHE_LOCAL_EXTENSION_CONFIGURATION);
        Ec::$service->getExtension()->getDb()->clearCache();
        /**
         * 获取数据库里的所有应用的菜单数据，不包括用户自建菜单数据
         *
         * @var array $menuInDb ['backend' => [], 'frontend' => [], 'main' => []]
         */
        $menuInDb = ArrayHelper::listSearch($this->getProvider()->getAll(), [
            'category_id' => ['in', array_keys(AppEnum::list())],
            'created_type' => MenuProviderInterface::CREATE_TYPE_BY_EXTENSION,
        ]);
        $menuInDb = $menuInDb ? ArrayHelper::index($menuInDb, 'id', 'category_id') : [];
        // 需要更新或新增操作的数据库菜单数据
        $todoMenus = [];
        foreach ($this->getProvider()->getMenuConfig() as $app => $menus) {
            $todoMenus = ArrayHelper::merge($todoMenus, $this->_compareMenus($menus, $menuInDb[$app]));
        }
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
     * @param array $menus 菜单配置信息
     * @param array &$menuInDb 数据库里的菜单数据
     * @param integer $parentId 父级ID
     *
     * @return array
     * ```php
     * [
     *      'create' => [], // 需要插入数据库的菜单数据
     *      'update' => [], // 需要更新数据库的菜单数据
     *      'menuConfig' => [], // 扩展配置里的菜单配置数据
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
            // 排除没有设置归属模块的数据以及中断该数据的子数据
            // todo 改为系统日志记录该错误或抛出系统异常便于更正?
            if (empty($menu['modularity'])) {
                continue;
            }
            
            $items = ArrayHelper::remove($menu, 'items', []);
            $menu['parent_id'] = $parentId; // 添加菜单父级ID
            $this->_formatFields($menu);
            // 查询条件
            $condition = [
                'category_id' => $menu['category_id'],
                'label' => $menu['label'],
                'modularity' => $menu['modularity'],
                'url' => $menu['url'],
                'parent_id' => $menu['parent_id'],
                'theme' => $menu['theme'],
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
                    // 不存在父级菜单则递归新建父级菜单
                    // fixme 开启事务时容易因为锁表导致数据查询出错
                    if (Yii::$app->getDb()->createCommand()->insert($this->getProvider()->getModel()::tableName(), $menu)->execute()) {
                        $menu['id'] = $this->getProvider()->getModel()::find()->select('id')->where($condition)->scalar();
                        $menuInDb[] = $menu; // 同步更新数据库已有数据
                    }
                } else {
                    ksort($menu); // 排序，保持键序一致，便于批量插入数据库
                    $arr['create'][] = $menu; // 不存在子类菜单则列入待新建数组里
                    $menuInDb[] = $menu; // 同步更新数据库已有数据
                }
            }
            if ($items) {
                $arr = ArrayHelper::merge($arr, $this->_compareMenus($items, $menuInDb, $menu['id']));
            }
            $arr['menuConfig'][] = $menu;
        }
        
        return $arr;
    }
    
    /**
     * 特殊字段处理，转换为保存进数据库时的格式
     *
     * @param array $data
     */
    private function _formatFields(&$data)
    {
        $data['menu_config'] = $data['menu_config'] ? json_encode($data['menu_config']) : '';
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
     * @param array $arr 待处理数组
     * ```php
     * [
     *      'create' => [], // 需要插入数据库的菜单数据
     *      'update' => [], // 需要更新数据库的菜单数据
     *      'menuConfig' => [], // 扩展配置里的菜单配置数据
     * ]
     * ```
     */
    private function _fixMenuData($menuInDb = [], &$arr = [])
    {
        if (isset($arr['menuConfig'])) {
            foreach ($menuInDb as $app => $menus) {
                if (empty($menus)) {
                    continue;
                }
                foreach ($menus as $menu) {
                    // 配置数据里已删除，则删除数据库对应数据
                    if (
                        !ArrayHelper::listSearch($arr['menuConfig'], [
                            'category_id' => $menu['category_id'],
                            'label' => $menu['label'],
                            'modularity' => $menu['modularity'],
                            'url' => $menu['url'],
                            'theme' => $menu['theme'],
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
     * @param array $array 需要操作的数据 ['delete', 'create', 'update']
     * ```php
     * [
     *      'create' => [], // 需要插入数据库的菜单数据
     *      'update' => [], // 需要更新数据库的菜单数据
     *      'delete' => [], // 需要删除的数据库菜单数据
     * ]
     * ```
     */
    private function _todoMenus($array)
    {
        if (isset($array['delete']) && !empty($array['delete'])) {
            Yii::$app->getDb()->createCommand()
                ->delete($this->getProvider()->getModel()::tableName(), ['id' => $array['delete']])
                ->execute();
        }
        if (isset($array['create']) && !empty($array['create'])) {
            Yii::$app->getDb()->createCommand()
                ->batchInsert($this->getProvider()->getModel()::tableName(), array_keys($array['create'][0]), $array['create'])
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
    
}

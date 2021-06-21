<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

use EngineCore\db\Migration;
use EngineCore\Ec;
use EngineCore\enums\StatusEnum;
use EngineCore\enums\VisibleEnum;
use EngineCore\extension\menu\FileProvider;
use EngineCore\extension\menu\MenuProviderInterface;
use EngineCore\helpers\ArrayHelper;
use EngineCore\helpers\UrlHelper;

class m200822_065628_create_menu_table extends Migration
{
    
    public function safeUp()
    {
        $provider = Ec::$service->getMenu()->getConfig()->getProvider();
        $this->createTable($this->createTableNameWithCode('menu'), [
            'id'                             => $this->primaryKey()->unsigned()->comment('ID'),
            'parent_id'                      => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('父类ID'),
            $provider->getCategoryField()    => $this->string(64)->notNull()->comment('分类'),
            $provider->getLabelField()       => $this->string(64)->notNull()->comment('名称'),
            $provider->getAliasField()       => $this->string(64)->notNull()->comment('菜单别名，用于菜单显示'),
            $provider->getIconField()        => $this->string(30)->notNull()->defaultValue('')->comment('图标'),
            $provider->getUrlField()         => $this->string(512)->notNull()->comment('菜单路由地址'),
            $provider->getParamsField()      => $this->string(200)->defaultValue('')->comment('URL参数'),
            $provider->getDescriptionField() => $this->string(512)->notNull()->defaultValue('')->comment('描述'),
            $provider->getConfigField()      => $this->string(200)->defaultValue('')->comment('菜单配置参数'),
            $provider->getVisibleField()     => $this->boolean()->unsigned()->notNull()
                                                     ->defaultValue(VisibleEnum::INVISIBLE)
                                                     ->comment('显示菜单，默认不显示'),
            $provider->getThemeField()       => $this->string(20)
                                                     ->defaultValue('basic')
                                                     ->comment('所属主题，basic表示所有主题通用'),
            $provider->getOrderField()       => $this->smallInteger(3)->unsigned()->notNull()
                                                     ->defaultValue(0)->comment('排序'),
            $provider->getCreatedByField()   => $this->boolean()->unsigned()->notNull()
                                                     ->defaultValue(MenuProviderInterface::CREATED_BY_USER)
                                                     ->comment('创建方式 0-用户(手动) 1-扩展(自动)'),
            'visible_on_dev'                 => $this->boolean()->unsigned()->notNull()
                                                     ->defaultValue(StatusEnum::STATUS_ON)->comment('是否开发环境可见'),
        ], $this->tableOptions . $this->buildTableComment('菜单表'));
        
        $this->createIndexWithCode('menu', $provider->getCategoryField());
        
        // 转存数据
        $this->transferToDb();
    }
    
    public function safeDown()
    {
        // 转存数据
        if ($this->transferToFile()) {
            // 清除旧的缓存数据
            Ec::$service->getMenu()->getConfig()->clearCache();
            // 数据转存成功后，临时调用文件方式的数据提供器
            Ec::$service->getMenu()->getConfig()->setProvider(FileProvider::class);
            $this->dropTable($this->createTableNameWithCode('menu'));
        } else {
            throw new \Exception('Failed to transfer configuration file.');
        }
    }
    
    /**
     * 转存数据到数据库
     */
    protected function transferToDb()
    {
        // 数据库里的菜单数据
        $menuInDb = [];
        // 需要更新或新增操作的数据库菜单数据
        $todoMenus = [];
        foreach (Ec::$service->getMenu()->getConfig()->getMenuConfig() as $category => $menus) {
            $todoMenus = ArrayHelper::merge($todoMenus, $this->_compareMenus($menus, $menuInDb[$category]));
        }
        
        // 修正菜单数据
        $this->_fixMenuData($menuInDb, $todoMenus);
        // 执行数据库操作
        $this->_todoMenus($todoMenus);
    }
    
    /**
     * 转存数据到文件
     *
     * @return bool
     */
    protected function transferToFile(): bool
    {
        return true;
        $config = [];
        $fields = array_keys(Ec::$service->getMenu()->getConfig()->getProvider()->getFieldMap());
        $menus = Yii::getAlias(Ec::$service->getExtension()->getEnvironment()->menuFile);
        $menus = require("$menus");
        foreach (Ec::$service->getMenu()->getConfig()->getAll() as $key => $row) {
            $arr = [];
            foreach ($row as $field => $value) {
                // 只转存默认字段的数值
                if (in_array($field, $fields)) {
                    if (isset($menus[$key][$field])) {
                        if ($menus[$key][$field] != $value) {
                            $arr[$field] = $value;
                        }
                    } else {
                        $arr[$field] = $value;
                    }
                }
            }
            if (!empty($arr)) {
                $config[$key] = $arr;
            }
        }
        
        return Ec::$service->getExtension()->getEnvironment()->flushUserSettingFile($config);
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
            $this->_normalizeFields($menu);
            // 查询条件
            $condition = [
                'parent_id'  => $menu['parent_id'],
                'label'      => $menu['label'],
                'url'        => $menu['url'],
                'theme'      => $menu['theme'],
                'created_by' => $menu['created_by'],
            ];
            // 数据库里存在数据
            if ($data = ArrayHelper::listSearch($menuInDb, $condition)) {
                $data = $data[0];
                $menu['id'] = $data['id'];
                // 检测数据是否改变
                $this->_isChanged($menu, $data, $arr);
            } else {
                // 存在子菜单则先创建父级菜单
                if ($items) {
                    // 只转存默认字段的数值
                    if ($this->insert($this->createTableNameWithCode('menu'), $menu) !== 0) {
                        $menu['id'] = $this->db->getLastInsertID();
                        $menuInDb[] = $menu; // 同步更新数据库已有数据
                    } else {
                        $items = []; // 父级菜单创建失败则中断子级菜单
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
     * 规范字段值，用于储存进数据库
     *
     * @param array &$data
     */
    private function _normalizeFields(&$data)
    {
        $data['config'] = $data['config'] ? json_encode($data['config']) : '';
        $data['params'] = $data['params'] ? UrlHelper::getUrlQuery($data['params']) : '';
        $fieldMap = Ec::$service->getMenu()->getConfig()->getProvider()->getFieldMap();
        foreach ($data as $field => $value) {
            // 只转存默认字段的数值
            if (!isset($fieldMap[$field])) {
                unset($data[$field]);
            }
        }
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
     *
     * ```php
     * [
     *      'insert' => [], // 需要插入数据库的菜单数据
     *      'update' => [], // 需要更新数据库的菜单数据
     *      'config' => [], // 扩展配置里的菜单配置数据
     * ]
     * ```
     */
    private function _fixMenuData($menuInDb = [], &$arr = [])
    {
        if (isset($arr['config'])) {
            foreach ($menuInDb as $menus) {
                if (!empty($menus)) {
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
            $this->delete($this->createTableNameWithCode('menu'), ['id' => $array['delete']]);
        }
        if (isset($array['insert']) && !empty($array['insert'])) {
            $this->batchInsert($this->createTableNameWithCode('menu'), array_keys($array['insert'][0]), $array['insert']);
        }
        if (isset($array['update']) && !empty($array['update'])) {
            foreach ($array['update'] as $id => $menu) {
                $this->update($this->createTableNameWithCode('menu'), $menu, ['id' => $id]);
            }
        }
    }
    
}
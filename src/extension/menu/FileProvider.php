<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\menu;

use EngineCore\Ec;
use EngineCore\helpers\ArrayHelper;
use Yii;
use yii\base\BaseObject;

/**
 * 文件方式的菜单数据提供器
 *
 * 通过菜单配置文件获取菜单数据
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class FileProvider extends BaseObject implements MenuProviderInterface
{
    
    use MenuProviderTrait;
    
    private $_all;
    
    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        if (null === $this->_all) {
            $this->_all = [];
            $increaseId = 0;
            $menus = Ec::$service->getMenu()->getConfig()->getMenuConfig();
            foreach ($menus as $category => $menu) {
                $this->_all = array_merge($this->_all, $this->_buildData($menu, $increaseId, 0));
            }
            
            $this->_all = ArrayHelper::index($this->_all, 'id');
        }
        
        return $this->_all;
    }
    
    /**
     * {@inheritdoc}
     */
    public function clearCache()
    {
        $this->_all = null;
    }
    
    /**
     * 构建菜单数据
     *
     * 把多维菜单数据格式化为一维数组，并为菜单数据虚构`$id`和`$parent_id`值，用于关联子级菜单数据
     *
     * @param array $items      菜单数据
     * @param int   $increaseId 递增菜单ID
     * @param int   $parentId   菜单父级ID
     *
     * @return array
     */
    private function _buildData($items, &$increaseId, $parentId)
    {
        foreach ($items as &$item) {
            $item['id'] = ++$increaseId;
            $item['parent_id'] = $parentId;
            if (isset($item['items'])) {
                $item['items'] = $this->_buildData($item['items'], $increaseId, $increaseId);
            }
        }
        
        return ArrayHelper::treeToList($items, 'items', 'order');
    }
    
}
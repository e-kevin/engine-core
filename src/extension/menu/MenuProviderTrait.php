<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\menu;

use EngineCore\base\CacheDurationTrait;
use EngineCore\enums\AppEnum;
use EngineCore\helpers\ArrayHelper;

/**
 * Trait MenuProviderTrait
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
trait MenuProviderTrait
{
    
    use CacheDurationTrait, MenuFieldTrait;
    
    private $_allGroupByLevel;
    
    /**
     * 获取指定菜单层级的所有菜单数据
     *
     * @see MenuProviderInterface::getAllByLevel()
     *
     * @param int $level
     *
     * @return array
     */
    public function getAllByLevel(int $level): array
    {
        if (null === $this->_allGroupByLevel) {
            $this->_allGroupByLevel = ArrayHelper::index($this->getAll(), 'id', 'level');
        }
        
        return $this->_allGroupByLevel[$level] ?? [];
    }
    
    /**
     * 获取默认菜单配置
     *
     * 数组的一维键名为菜单分类名，如：'backend', 'frontend', 'main'等
     *
     * @see MenuProviderInterface::getDefaultConfig()
     *
     *  - `items` array: 子类菜单配置数组
     *
     * @return array
     */
    public function getDefaultConfig(): array
    {
        return [
            AppEnum::BACKEND  => [
                'engine-core' => [
                    'label' => 'EngineCore',
                    'icon'  => 'info',
                    'visible'  => true,
                    'order' => 9999,
                    'items' => [
                        [
                            'label' => '最新动态',
                            'alias' => '最新动态',
                            'visible'  => true,
                        ],
                        [
                            'label' => '权威指南',
                            'alias' => '权威指南',
                            'visible'  => true,
                        ],
                    ],
                ],
            ],
            AppEnum::FRONTEND => [],
        ];
    }
    
}
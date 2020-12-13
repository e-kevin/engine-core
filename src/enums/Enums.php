<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\enums;

use EngineCore\helpers\ArrayHelper;
use Yii;

/**
 * 枚举类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
abstract class Enums implements EnumInterface
{
    
    /**
     * {@inheritdoc}
     */
    public static function list($showUnlimited = false)
    {
        return $showUnlimited
            ? ArrayHelper::merge(
                [EnumInterface::UNLIMITED => Yii::t('Ec/app', 'Unlimited')]
                , static::_list()
            )
            : static::_list();
    }
    
    /**
     * 获取列表
     *
     * @return array
     */
    protected static function _list(): array
    {
        return [];
    }
    
    /**
     * {@inheritdoc}
     */
    public static function value($key)
    {
        return ArrayHelper::getValue(static::list(), $key);
    }
    
}
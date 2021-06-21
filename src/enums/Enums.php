<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

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
    
    private static $_list;
    
    /**
     * {@inheritdoc}
     */
    public static function list($showUnlimited = false)
    {
        if (!isset(static::$_list[$showUnlimited])) {
            self::$_list[$showUnlimited] = $showUnlimited
                ? ArrayHelper::merge(
                    [EnumInterface::UNLIMITED => Yii::t('ec/app', 'Unlimited')],
                    static::_list()
                )
                : static::_list();
        }
        
        return self::$_list[$showUnlimited];
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
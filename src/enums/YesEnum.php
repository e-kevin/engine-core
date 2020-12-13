<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\enums;

use Yii;

/**
 * 是否枚举
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class YesEnum extends Enums
{
    
    const
        NO = 0,
        YES = 1;
    
    /**
     * {@inheritdoc}
     */
    protected static function _list(): array
    {
        return [
            self::NO => Yii::t('Ec/app', 'No'),
            self::YES => Yii::t('Ec/app', 'Yes'),
        ];
    }
    
}

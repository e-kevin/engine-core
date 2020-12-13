<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\enums;

use Yii;

/**
 * 链接打开方式枚举
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class TargetEnum extends Enums
{
    
    const
        TARGET_SELF = 0,
        TARGET_BLANK = 1;
    
    /**
     * {@inheritdoc}
     */
    protected static function _list(): array
    {
        return [
            self::TARGET_SELF => Yii::t('Ec/app', 'Target self'),
            self::TARGET_BLANK => Yii::t('Ec/app', 'Target blank'),
        ];
    }
    
}

<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

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
            self::TARGET_SELF => Yii::t('ec/app', 'Target self'),
            self::TARGET_BLANK => Yii::t('ec/app', 'Target blank'),
        ];
    }
    
}

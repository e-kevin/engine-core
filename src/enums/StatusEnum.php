<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\enums;

use Yii;

/**
 * 系统应用枚举
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class StatusEnum extends Enums
{
    
    const
        STATUS_OFF = 0,
        STATUS_ON = 1;
    
    /**
     * {@inheritdoc}
     */
    protected static function _list(): array
    {
        return [
            self::STATUS_OFF => Yii::t('ec/app', 'Status off'),
            self::STATUS_ON  => Yii::t('ec/app', 'Status on'),
        ];
    }
    
}
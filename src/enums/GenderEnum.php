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
 * 性别枚举
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class GenderEnum extends Enums
{
    
    const
        UNKNOWN = 0,
        MALE = 1,
        FEMALE = 2;
    
    /**
     * {@inheritdoc}
     */
    protected static function _list(): array
    {
        return [
            self::UNKNOWN => Yii::t('ec/app', 'Secrecy'),
            self::MALE => Yii::t('ec/app', 'Male'),
            self::FEMALE => Yii::t('ec/app', 'Female'),
        ];
    }
    
}

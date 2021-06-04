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
 * 显隐状态枚举
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class VisibleEnum extends Enums
{
    
    const
        INVISIBLE = 0,
        VISIBLE = 1;
    
    /**
     * {@inheritdoc}
     */
    protected static function _list(): array
    {
        return [
            self::INVISIBLE => Yii::t('ec/app', 'Hidden'),
            self::VISIBLE => Yii::t('ec/app', 'Display'),
        ];
    }
    
}

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
 * 启用状态枚举
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class EnableEnum extends Enums
{
    
    const
        DISABLE = 0,
        ENABLE = 1;
    
    /**
     * {@inheritdoc}
     */
    protected static function _list(): array
    {
        return [
            self::DISABLE => Yii::t('ec/app', 'Disable'),
            self::ENABLE => Yii::t('ec/app', 'Enable'),
        ];
    }
    
}

<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\enums;

/**
 * 系统应用枚举
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class AppEnum extends Enums
{
    
    const
        BACKEND = 'backend',
        FRONTEND = 'frontend',
        CONSOLE = 'console',
        COMMON = 'common';
    
    /**
     * {@inheritdoc}
     */
    protected static function _list(): array
    {
        return [
            self::BACKEND  => '后台应用',
            self::FRONTEND => '前台应用',
            self::CONSOLE  => '控制台应用',
            self::COMMON   => '公共应用',
        ];
    }
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\enums;

/**
 * 枚举接口
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface EnumInterface
{
    
    /**
     * @var integer 不限
     */
    const UNLIMITED = 999;
    
    /**
     * 获取列表
     *
     * @param bool $showUnlimited 是否显示`不限`选项，默认不显示
     *
     * @return array
     */
    public static function list($showUnlimited = false);
    
    /**
     * 获取值
     *
     * @param string|\Closure|array $key
     *
     * @return mixed
     */
    public static function value($key);
    
}
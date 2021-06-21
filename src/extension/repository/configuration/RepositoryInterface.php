<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\configuration;

/**
 * 扩展仓库资源接口
 *
 * @property string $type
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface RepositoryInterface
{
    
    /**
     * 获取仓库资源类型
     *
     * @return string
     */
    public function getType();
    
}

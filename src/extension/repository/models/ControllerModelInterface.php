<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\models;

/**
 * 控制器扩展仓库模型接口
 *
 * ==== 数据库字段
 * @property string $module_id     模块ID
 * @property string $controller_id 控制器ID
 * ====
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
interface ControllerModelInterface extends RepositoryModelInterface
{
}
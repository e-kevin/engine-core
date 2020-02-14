<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\web;

use EngineCore\dispatch\DispatchTrait;

/**
 * 支持系统调度功能（Dispatch）的基础Controller类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Controller extends \yii\web\Controller
{
    
    use DispatchTrait;
    
}
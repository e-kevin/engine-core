<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\console;

use EngineCore\dispatch\DispatchTrait;
use EngineCore\Ec;
use yii\helpers\Console;

/**
 * 支持系统调度功能（Dispatch）的基础Controller类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Controller extends \yii\console\Controller
{
    
    use DispatchTrait;
    
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $version = Ec::getVersion();
            $this->stdout(<<<ICON
 __      __      _________                __
/  \    /  \____ \_   ___ \  ____   _____/  |_  ___________
\   \/\/   /  _ \/    \  \/_/ __ \ /    \   __\/ __ \_  __ \
 \        (  <_> )     \___\  ___/|   |  \  | \  ___/|  | \/
  \__/\  / \____/ \______  /\___  >___|  /__|  \___  >__|
       \/                \/     \/     \/          \/
ICON
                , Console::FG_GREEN);
            $this->stdout("\n\n(based on EngineCore v{$version})\n\n");
            
            return true;
        }
        
        return false;
    }
    
}
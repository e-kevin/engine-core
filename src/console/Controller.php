<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\console;

use EngineCore\Ec;

/**
 * console的基础Controller类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Controller extends \yii\console\Controller
{
    
    public function beforeAction($action)
    {
        $version = Ec::getVersion();
        $this->stdout("\n");
        $this->stdout(<<<ICON
__________              _____                  _________
___  ____/_____________ ___(_)___________      __  ____/_________________
__  __/  __  __ \_  __ `/_  /__  __ \  _ \     _  /    _  __ \_  ___/  _ \
_  /___  _  / / /  /_/ /_  / _  / / /  __/     / /___  / /_/ /  /   /  __/
/_____/  /_/ /_/_\__, / /_/  /_/ /_/\___/      \____/  \____//_/    \___/
                /____/
ICON
        );
//            $this->stdout(<<<ICON
//▓█████  ███▄    █   ▄████  ██▓ ███▄    █ ▓█████     ▄████▄   ▒█████   ██▀███  ▓█████
//▓█   ▀  ██ ▀█   █  ██▒ ▀█▒▓██▒ ██ ▀█   █ ▓█   ▀    ▒██▀ ▀█  ▒██▒  ██▒▓██ ▒ ██▒▓█   ▀
//▒███   ▓██  ▀█ ██▒▒██░▄▄▄░▒██▒▓██  ▀█ ██▒▒███      ▒▓█    ▄ ▒██░  ██▒▓██ ░▄█ ▒▒███
//▒▓█  ▄ ▓██▒  ▐▌██▒░▓█  ██▓░██░▓██▒  ▐▌██▒▒▓█  ▄    ▒▓▓▄ ▄██▒▒██   ██░▒██▀▀█▄  ▒▓█  ▄
//░▒████▒▒██░   ▓██░░▒▓███▀▒░██░▒██░   ▓██░░▒████▒   ▒ ▓███▀ ░░ ████▓▒░░██▓ ▒██▒░▒████▒
//░░ ▒░ ░░ ▒░   ▒ ▒  ░▒   ▒ ░▓  ░ ▒░   ▒ ▒ ░░ ▒░ ░   ░ ░▒ ▒  ░░ ▒░▒░▒░ ░ ▒▓ ░▒▓░░░ ▒░ ░
// ░ ░  ░░ ░░   ░ ▒░  ░   ░  ▒ ░░ ░░   ░ ▒░ ░ ░  ░     ░  ▒     ░ ▒ ▒░   ░▒ ░ ▒░ ░ ░  ░
//   ░      ░   ░ ░ ░ ░   ░  ▒ ░   ░   ░ ░    ░      ░        ░ ░ ░ ▒    ░░   ░    ░
//   ░  ░         ░       ░  ░           ░    ░  ░   ░ ░          ░ ░     ░        ░  ░
//ICON
//);
        $this->stdout("\n\n(based on EngineCore v{$version})\n\n");
        if (!parent::beforeAction($action)) {
            return false;
        }
        
        return true;
    }
    
}
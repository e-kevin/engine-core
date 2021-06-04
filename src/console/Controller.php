<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\console;

use EngineCore\Ec;
use yii\helpers\Console;

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
    
    /**
     * 多项选择列表
     *
     * @param string $prompt
     * @param array  $options
     *
     * @return mixed
     */
    public function multiSelect(string $prompt, $options = [])
    {
        $out = [];
        $oldOptions = $options;
        $operation = [
            '!' => 'Show selected',
            '*' => 'Reselect',
            '%' => 'Selection complete',
            '/' => 'Exit',
            '?' => 'Show help',
        ];
        top:
        $this->stdout("$prompt: [" . implode(',', array_keys($operation)) . ']' . PHP_EOL, Console::FG_YELLOW);
        foreach ($options as $key => $value) {
            Console::output(" $key - $value");
        }
        $input = Console::stdin();
        if ($input === '?') {
            foreach ($operation as $key => $value) {
                Console::output(" $key - $value");
            }
            goto top;
        } elseif ($input === '/') {
            return $input;
        } elseif ($input === '*') {
            $out = [];
            $options = $oldOptions;
            goto top;
        } elseif ($input === '!') {
            $this->stdout('You chose: ' . implode(',', $out) . PHP_EOL, Console::FG_RED);
            goto top;
        } elseif ($input === '%') {
            $this->stdout('You chose: ' . implode(',', $out) . PHP_EOL, Console::FG_RED);
        } elseif (!array_key_exists($input, $options)) {
            goto top;
        } else {
            $out[$input] = $options[$input];
            unset($options[$input]);
            foreach ($out as $key => $value) {
                $this->stdout("$value selected." . PHP_EOL, Console::FG_GREEN);
            }
            goto top;
        }
        
        return $input;
    }
    
}
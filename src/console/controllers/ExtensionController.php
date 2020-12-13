<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\console\controllers;

use EngineCore\console\Controller;
use EngineCore\Ec;
use yii\{
    console\ExitCode, helpers\Console
};
use Yii;

/**
 * Class ExtensionController
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ExtensionController extends Controller
{
    
    /**
     * 刷新扩展配置文件
     */
    public function actionFlushConfigFiles()
    {
        $this->stdout("====== Creating the extension config files ======\n", Console::FG_YELLOW);
        foreach (Ec::$service->getExtension()->getEnvironment()->flushConfigFiles() as $file) {
            $this->stdout(" {$file}\n");
        }
        $this->stdout("\n");
        
        return ExitCode::OK;
    }
    
    /**
     * 更新本地扩展仓库，获取最新的扩展数据
     */
    public function actionUpdateRepository()
    {
        $this->stdout("====== Extension repository updated ======\n", Console::FG_YELLOW);
        $this->stdout("\n");
    
        Ec::$service->getExtension()->clearCache();
    
        return ExitCode::OK;
    }
    
}
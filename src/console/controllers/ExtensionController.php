<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
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
        $files = Ec::$service->getExtension()->getEnvironment()->flushConfigFiles();
        if (!empty($files['success'])) {
            $this->stdout(" " . (count($files['success'])) . " configuration files are created successfully:\n", Console::FG_GREEN);
            foreach ($files['success'] as $file) {
                $this->stdout(" {$file}\n");
            }
        }
        if (!empty($files['fail'])) {
            $this->stdout(" " . (count($files['fail'])) . " configuration files are created failed:\n", Console::FG_RED);
            foreach ($files['fail'] as $file) {
                $this->stdout(" {$file}\n");
            }
        }
        $this->stdout("\n");
        if (!Ec::$service->getExtension()->getRepository()->hasModel()) {
            $this->stdout("The extension model class is not set.\n\n", Console::FG_RED);
        }
        
        return ExitCode::OK;
    }
    
    /**
     * 更新本地扩展仓库，获取最新的扩展数据
     */
    public function actionUpdateRepository()
    {
        $this->stdout("====== Extension repository updated ======\n\n", Console::FG_YELLOW);
    
        Ec::$service->getExtension()->clearCache();
    
        return ExitCode::OK;
    }
    
}
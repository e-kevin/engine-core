<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\widgets;

use EngineCore\Ec;
use EngineCore\extension\entity\ExtensionEntityInterface;
use Yii;
use yii\base\Widget;

/**
 * 反馈信息视图小部件，方便用户向开发者提交bug等信息
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Issue extends Widget
{
    
    /**
     * @var \yii\base\Controller 上下文控制器
     */
    public $context;
    
    public function init()
    {
        parent::init();
        
        /** @var ExtensionEntityInterface $runningExtension */
        $runningExtension = Ec::$service->getExtension()->entity($this->context ?? Yii::$app->controller);
        $config = $runningExtension->getInfo()->getConfiguration();
        $issueUrl = $config->getSupport()->getIssues();
        if (empty($issueUrl) && strpos($config->getHomepage(), 'github.com') !== false) {
            $issueUrl = $config->getHomepage() . '/issues';
        }
        
        echo $this->render('@EngineCore/views/_issue-message', [
            'issueUrl' => $issueUrl,
        ]);
    }
    
}
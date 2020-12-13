<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension;

use EngineCore\Ec;
use EngineCore\extension\repository\info\EngineCoreInfo;
use Yii;

/**
 * EngineCore核心框架扩展
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class EngineCoreExtension extends BaseRunningExtension
{
    
    /**
     * {@inheritdoc}
     */
    public function getInfo()
    {
        $configuration = Ec::$service->getExtension()->getRepository()->getFinder()
                                     ->getConfigurationByFile(Yii::getAlias('@vendor/e-kevin/engine-core/composer.json'));
        
        return Yii::createObject(EngineCoreInfo::class, [
            $configuration,
        ]);
    }
    
}
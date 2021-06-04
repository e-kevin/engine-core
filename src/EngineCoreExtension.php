<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore;

use EngineCore\extension\entity\BaseExtensionEntity;
use Yii;

/**
 * EngineCore核心框架扩展
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class EngineCoreExtension extends BaseExtensionEntity
{
    
    /**
     * {@inheritdoc}
     */
    public function getInfo()
    {
        $configuration = Ec::$service->getExtension()->getRepository()->getFinder()
                                     ->getConfigurationByFile(Yii::getAlias('@vendor/e-kevin/engine-core/composer.json'));
        
        return Yii::createObject(EngineCoreInfo::class, [
            'backend',
            'engine-core',
            [
                'configuration' => $configuration
            ]
        ]);
    }
    
}
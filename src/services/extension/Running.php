<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\services\extension;

use EngineCore\Ec;
use EngineCore\extension\entity\ExtensionEntity;
use EngineCore\extension\entity\ExtensionEntityInterface;
use EngineCore\services\Extension;
use EngineCore\base\Service;
use Yii;

/**
 * 获取指定实体所属的扩展服务类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Running extends Service
{
    
    /**
     * @var Extension 父级服务类
     */
    public $service;
    
    /**
     * 获取指定实体所属的扩展信息详情，如果实体不属于任何一个扩展，则默认为EngineCore扩展
     *
     * @param object $object
     *
     * @return object|ExtensionEntityInterface
     */
    public static function entity($object): ExtensionEntityInterface
    {
        if (Yii::$container->has('ExtensionEntity')) {
            $definition = Yii::$container->definitions['ExtensionEntity'];
        } else {
            $definition['class'] = ExtensionEntity::class;
        }
        
        return Ec::createObject($definition, [$object], ExtensionEntityInterface::class);
    }
    
}
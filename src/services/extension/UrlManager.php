<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\services\Extension;
use EngineCore\extension\repository\info\ControllerInfo;
use EngineCore\extension\repository\info\ModularityInfo;
use EngineCore\base\Service;
use Yii;
use yii\base\InvalidRouteException;
use yii\base\UserException;
use yii\helpers\Url;

/**
 * 扩展url管理服务类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class UrlManager extends Service
{
    
    /**
     * @var Extension 父级服务类
     */
    public $service;
    
    /**
     * 获取指定扩展下的路由地址，支持任何已存在的扩展路由，包括未安装的扩展
     *
     * @param array|string $route 路由地址
     * @param string       $extensionName 扩展名称，用于获取该扩展下的路由地址。为空默认获取当前模块扩展
     *
     * @return array|string
     * @throws InvalidRouteException|UserException
     */
    public function getUrl($route = '', $extensionName = '')
    {
        $config = $this->service->getRepository()->getLocalConfiguration();
        /** @var ModularityInfo $infoInstance */
        $infoInstance = null;
        if (empty($extensionName)) {
            foreach ($config as $uniqueName => $row) {
                $infoInstance = $row['infoInstance'];
                if (Yii::$app->controller->module->id == $infoInstance->getId()) {
                    $extensionName = $uniqueName;
                    break;
                }
            }
        }
        // fixme: 使用[[Controller::getDispatch()]]时，触发以下异常
        if (!isset($config[$extensionName])) {
            if (YII_DEBUG) {
                throw new UserException('Invalid extended routing address.');
            } else {
                throw new InvalidRouteException('Invalid extended routing address.');
            }
        } else {
            $infoInstance = $config[$extensionName]['infoInstance'];
        }
        if (empty($route)) {
            if (is_subclass_of($infoInstance, ControllerInfo::class)) {
                $route = $infoInstance->getId();
            } elseif (is_subclass_of($infoInstance, ModularityInfo::class)) {
                $route = '/' . $infoInstance->getId();
            }
        } else {
            if (is_subclass_of($infoInstance, ControllerInfo::class)) {
                $route = $infoInstance->getId() . '/' . rtrim($route, '/');
            } elseif (is_subclass_of($infoInstance, ModularityInfo::class)) {
                $route = '/' . $infoInstance->getId() . '/' . rtrim($route, '/');
            }
        }
        
        return Url::to($route);
    }
    
}
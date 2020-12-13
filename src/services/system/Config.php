<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\system;

use EngineCore\extension\config\ConfigTrait;
use EngineCore\services\system\components\ConfigServiceInterface;
use EngineCore\extension\config\ConfigProviderInterface;
use EngineCore\helpers\StringHelper;
use EngineCore\base\Service;
use EngineCore\services\System;
use yii\helpers\Json;

/**
 * 系统配置服务类
 *
 * @property ConfigProviderInterface|ConfigTrait $provider
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Config extends Service implements ConfigServiceInterface
{
    
    /**
     * @var System 父级服务类
     */
    public $service;
    
    /**
     * @var ConfigProviderInterface|ConfigTrait 配置提供者
     */
    protected $_provider;
    
    /**
     * {@inheritdoc}
     */
    public function __construct(ConfigProviderInterface $provider, array $config = [])
    {
        $this->_provider = $provider;
        parent::__construct($config);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        return $this->getProvider()->getAll();
    }
    
    /**
     * {@inheritdoc}
     */
    public function clearCache()
    {
        $this->getProvider()->clearCache();
    }
    
    /**
     * {@inheritdoc}
     */
    public function get($key, $defaultValue = null)
    {
        return $this->getAll()[$key][$this->getProvider()->valueField] ?? $defaultValue;
    }
    
    /**
     * {@inheritdoc}
     */
    public function extra($key, $defaultValue = null)
    {
        return !isset($this->getAll()[$key])
            ? $defaultValue
            : StringHelper::parseString($this->getAll()[$key][$this->getProvider()->extraField]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function sortable($key, $category = 'enable', $defaultValue = [])
    {
        $config = $this->get($key, $defaultValue);
        if (empty($config)) {
            return [];
        }
        $res = [];
        foreach (Json::decode($config, true) as $v) {
            if ($v['group'] == $category) {
                $res = $v['items'];
                break;
            }
        }
        
        return $res;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getProvider()
    {
        return $this->_provider;
    }
    
    /**
     * {@inheritdoc}
     */
//    public function showVerify($scenario = '')
//    {
//        $openVerifyType = $this->get('VERIFY_OPEN');
//        if (empty($openVerifyType)) {
//            return false;
//        } else {
//            $openVerifyType = explode(',', $openVerifyType);
//        }
//
//        return in_array($scenario ?: Yii::$app->controller->action->id, $openVerifyType);
//    }
    
}
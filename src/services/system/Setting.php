<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

namespace EngineCore\services\system;

use EngineCore\Ec;
use EngineCore\extension\setting\SettingProviderInterface;
use EngineCore\extension\setting\FileProvider;
use EngineCore\helpers\StringHelper;
use EngineCore\base\Service;
use EngineCore\services\System;
use Yii;
use yii\helpers\Json;

/**
 * 系统设置服务类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Setting extends Service implements SettingServiceInterface
{
    
    /**
     * @var System 父级服务类
     */
    public $service;
    
    /**
     * @var SettingProviderInterface 设置提供器
     */
    protected $_provider;
    
    private $_all;
    
    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        if (null === $this->_all) {
            $this->_all = $this->getProvider()->getAll();
        }
        
        return $this->_all;
    }
    
    /**
     * {@inheritdoc}
     */
    public function clearCache()
    {
        $this->_all = null;
        $this->getProvider()->clearCache();
    }
    
    /**
     * {@inheritdoc}
     */
    public function get($key, $defaultValue = null)
    {
        return $this->getAll()[$key]['value'] ?? $defaultValue;
    }
    
    /**
     * {@inheritdoc}
     */
    public function extra($key, $defaultValue = null)
    {
        return !isset($this->getAll()[$key])
            ? $defaultValue
            : StringHelper::parseString($this->getAll()[$key]['extra']);
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
    public function getProvider(): SettingProviderInterface
    {
        if (null === $this->_provider) {
            $this->setProvider($this->providerDefinition());
        }
        
        return $this->_provider;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setProvider($provider)
    {
        $this->_provider = Ec::createObject($provider, [], SettingProviderInterface::class);
    }
    
    /**
     * 设置数据提供器默认配置
     *
     * @return string|array|callable
     */
    private function providerDefinition()
    {
        // 存在自定义设置数据提供器则优先获取该数据提供器，否则使用系统默认的文件方式设置数据提供器
        if (Yii::$container->has('SettingProvider')) {
            $definition = Yii::$container->definitions['SettingProvider'];
        } else {
            $definition['class'] = FileProvider::class;
        }
        
        return $definition;
    }
    
}
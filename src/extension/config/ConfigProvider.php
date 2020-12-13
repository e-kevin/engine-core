<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\config;

use Yii;
use yii\base\BaseObject;

/**
 * 文件形式的配置提供者实现类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ConfigProvider extends BaseObject implements ConfigProviderInterface
{
    
    use ConfigTrait;
    
    /**
     * 配置文件里的数据
     *
     * @var array
     */
    private $_config = [];
    
    /**
     * 默认配置数据
     *
     * @var array
     * @see \EngineCore\extension\config\ConfigModel 数组键名参考配置模型数据表字段
     */
    protected $_defaultConfig = [
        self::WEB_SITE_TITLE => [
            'name' => self::WEB_SITE_TITLE,
            'title' => '网站名称',
            'remark' => '网站名称',
            'value' => 'inOne后台管理系统',
            'extra' => '',
        ],
        self::WEB_SITE_DESCRIPTION => [
            'name' => self::WEB_SITE_DESCRIPTION,
            'title' => '网站简介',
            'remark' => '搜索引擎描述',
            'value' => '',
            'extra' => '',
        ],
        self::WEB_SITE_KEYWORD => [
            'name' => self::WEB_SITE_KEYWORD,
            'title' => '网站关键词',
            'remark' => '搜索引擎关键词',
            'value' => '',
            'extra' => '',
        ],
    ];
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->_config = Yii::$app->params[$this->configKey] ?? $this->_defaultConfig;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        return $this->_config;
    }
    
    /**
     * {@inheritdoc}
     */
    public function clearCache()
    {
    }
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension;

/**
 * 主题扩展信息类
 *
 * @property array $themeConfig 主题配置参数，只读属性
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ThemeInfo extends ExtensionInfo
{
    
    /**
     * @var string 主题ID，如：bootstrap-v3、adminlte、basic。该值决定调度器在哪个主题目录下获取调度器
     */
    public $id;
    
    /**
     * @var string 主题默认调度响应器
     */
    protected $response = '\EngineCore\web\DispatchResponse';
    
    /**
     * @var string 主题视图路径
     */
    private $_viewPath;
    
    /**
     * ThemeInfo constructor.
     *
     * @param string $uniqueId
     * @param string $uniqueName
     * @param string $version
     * @param string $viewPath
     * @param array $config
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct(string $uniqueId, string $uniqueName, string $version, string $viewPath, array $config = [])
    {
        $this->_viewPath = $viewPath;
        parent::__construct($uniqueId, $uniqueName, $version, $config);
    }
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->_name = $this->_name ?: $this->id;
    }
    
    /**
     * 获取主题配置参数
     *
     * @return array
     */
    final public function getThemeConfig(): array
    {
        return [
            'name' => $this->id,
            'response' => $this->response,
            'viewPath' => $this->_viewPath,
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        return [
            'params' => [
                // 添加当前主题配置参数
                'themeConfig' => $this->getThemeConfig(),
            ],
        ];
    }
    
}
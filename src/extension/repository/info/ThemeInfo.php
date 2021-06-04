<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\info;

use EngineCore\Ec;
use EngineCore\extension\setting\SettingProviderInterface;

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
    protected $id;
    
    /**
     * @var string 主题默认调度响应器
     */
    protected $response = 'EngineCore\web\DispatchResponse';
    
    /**
     * {@inheritdoc}
     */
    final public function getType(): string
    {
        return self::TYPE_THEME;
    }
    
    /**
     * 获取主题配置参数
     *
     * @return array
     */
    public function getThemeConfig(): array
    {
        return [
            'name'     => $this->id,
            'response' => $this->response,
            'viewPath' => $this->getAutoloadPsr4()['path'] . DIRECTORY_SEPARATOR . 'views',
        ];
    }
    
    /**
     * {@inheritdoc}
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
    
    /**
     * {@inheritdoc}
     */
    public function getCanUninstall(): bool
    {
        $defaultTheme = Ec::$service->getSystem()->getSetting()->get(SettingProviderInterface::DEFAULT_THEME);
        
        return parent::getCanUninstall() && $this->getUniqueName() !== $defaultTheme;
    }
    
}
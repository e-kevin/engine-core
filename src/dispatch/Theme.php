<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

namespace EngineCore\dispatch;

use EngineCore\Ec;
use EngineCore\extension\setting\SettingProviderInterface;
use EngineCore\helpers\NamespaceHelper;
use yii\base\BaseObject;
use yii\di\Instance;

/**
 * 调度器主题管理器
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Theme extends BaseObject implements DispatchThemeInterface
{
    
    /**
     * @var DispatchManager 调度器管理器
     */
    protected $dm;
    
    /**
     * Theme constructor.
     *
     * @param AbstractDispatchManager $dispatchManager
     * @param array                   $config
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct(AbstractDispatchManager $dispatchManager, array $config = [])
    {
        $this->dm = $dispatchManager;
        parent::__construct($config);
    }
    
    private $_isEnable;
    
    /**
     * {@inheritdoc}
     *
     * 用于判断调度器是否启用了主题功能，由系统的多主题设置和各级的'$enableTheme'配置参数决定
     */
    public function isEnableTheme(): bool
    {
        /**
         * 假如一个调度管理器的配置如下，优先级：控制器配置 > 全局配置 > 系统设置
         * ```php
         * 'container' => [
         *      'definitions' => [
         *          'DispatchManager' => [
         *              'class' => 'EngineCore\dispatch\DispatchManager',
         *              'enableTheme' => {bool}, // 全局配置
         *              'config' => [
         *                  '{route}' => [
         *                      'enableTheme' => {bool}, // 控制器配置
         *                      'dispatchMap' => [
         *                          'index' => [],
         *                      ],
         *                  ],
         *              ],
         *          ],
         *      ],
         * ],
         * ```
         */
        if (null === $this->_isEnable) {
            if (!$this->dm->isSupportRender()) {
                $this->_isEnable = false;
            } elseif (null !== $this->dm->getController()->enableTheme) {
                $this->_isEnable = $this->dm->getController()->enableTheme;
            } elseif (null !== $this->dm->enableTheme) {
                $this->_isEnable = $this->dm->enableTheme;
            } else {
                $this->_isEnable = (bool)Ec::$service->getSystem()->getSetting()->get(SettingProviderInterface::ENABLE_THEME);
            }
        }
        
        return $this->_isEnable;
    }
    
    private $_isStrict;
    
    /**
     * {@inheritdoc}
     *
     * 如果开启主题严谨模式，则调度器会优先在主题目录下获取需要的视图文件，否则从`views`目录里获取。
     */
    public function isStrict(): bool
    {
        /**
         * 假如一个调度管理器的配置如下，优先级：控制器配置 > 全局配置 > 系统设置
         * ```php
         * 'container' => [
         *      'definitions' => [
         *          'DispatchManager' => [
         *              'class' => 'EngineCore\dispatch\DispatchManager',
         *              'strict' => {bool}, // 全局配置
         *              'config' => [
         *                  '{route}' => [
         *                      'strict' => {bool}, // 控制器配置
         *                      'dispatchMap' => [
         *                          'index' => [],
         *                      ],
         *                  ],
         *              ],
         *          ],
         *      ],
         * ],
         * ```
         */
        if (null === $this->_isStrict) {
            if (!$this->dm->isSupportRender() || !$this->isEnableTheme()) {
                $this->_isStrict = false;
            } elseif (null !== $this->dm->getController()->strict) {
                $this->_isStrict = $this->dm->getController()->strict;
            } elseif (null !== $this->dm->strict) {
                $this->_isStrict = $this->dm->strict;
            } else {
                $this->_isStrict = (bool)Ec::$service->getSystem()->getSetting()->get(SettingProviderInterface::STRICT_THEME);
            }
        }
        
        return $this->_isStrict;
    }
    
    /**
     * @var string 默认主题
     */
    private $_default;
    
    /**
     * {@inheritdoc}
     * @see NamespaceHelper::normalizeStringForNamespace()
     */
    public function getDefault(): string
    {
        if (null === $this->_default) {
            $this->_default = Ec::$service->getExtension()->getThemeRepository()->getDefaultConfig('name');
            $this->_default = NamespaceHelper::normalizeStringForNamespace($this->_default);
        }
        
        return $this->_default;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setPathMap()
    {
        $pathMap = [];
        // 开发者运行模式下，只有当前控制器属于系统扩展控制器才生效
        if ($this->dm->getRunRule()->isDeveloperMode()) {
            // 优先加载开发者目录下的视图文件
            $pathMap = [
                '@developer'  => [
                    '@developer',
                    '@extensions',
                ],
                '@extensions' => [
                    '@developer',
                    '@extensions',
                ],
            ];
        }
        if ($this->isStrict()) {
            // 设置主题视图目录
            $themeName = Ec::$service->getExtension()->getThemeRepository()->getConfig('name');
            $themePath = $this->dm->getController()->module->getBasePath() . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $themeName;
            $this->dm->getController()->module->setViewPath($themePath);
        }
        if ($this->isEnableTheme()) {
            $viewPath = Ec::$service->getExtension()->getThemeRepository()->getConfig('viewPath'); // 当前主题的视图路径映射
        } else {
            $viewPath = Ec::$service->getExtension()->getThemeRepository()->getDefaultConfig('viewPath'); // 默认主题的视图路径映射
        }
        
        foreach ((array)$viewPath as $key => $value) {
            $pathMap['@app/views'][$key] = $value;
        }
        
        $this->dm->getController()->getView()->theme = Instance::ensure(['pathMap' => $pathMap], '\yii\base\Theme');
    }
    
}
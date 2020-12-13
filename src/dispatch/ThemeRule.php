<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

use yii\base\BaseObject;

/**
 * 调度器主题规则，主要用于判断调度器是否启用了主题功能，由各级的'$enableTheme'配置参数决定
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ThemeRule extends BaseObject implements DispatchThemeRuleInterface
{
    
    /**
     * @var DispatchManager 调度器管理器
     */
    protected $dm;
    
    /**
     * ThemeRule constructor.
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
    
    protected $_isEnable;
    
    /**
     * {@inheritdoc}
     */
    public function isEnableTheme(): bool
    {
        /**
         * 假如一个调度管理器的配置如下，优先级：控制器配置 > 全局配置
         * ```php
         * 'EngineCore\dispatch\DispatchManagerInterface' => [
         *      'class' => 'EngineCore\dispatch\DispatchManager',
         *      'enableTheme' => bool,  // 全局配置
         *      'config' => [
         *          '{route}' => [
         *              'enableTheme' => bool   // 控制器配置
         *              'dispatchMap' => [
         *                  'index' => [
         *                  ],
         *              ],
         *          ],
         *      ],
         * ]
         * ```
         */
        
        if (null === $this->_isEnable) {
            if (null !== $this->dm->getController()->enableTheme) {
                $this->_isEnable = $this->dm->getController()->enableTheme;
            } else {
                $this->_isEnable = boolval($this->dm->enableTheme);
            }
        }
        
        return $this->_isEnable;
    }
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

use EngineCore\extension\ExtensionInfo;
use EngineCore\helpers\ArrayHelper;
use yii\base\BaseObject;

/**
 * 调度器运行模式规则
 *
 * 运行模式，可选值有：
 *  - 0: 运行系统扩展，运行在'@extensions'目录下的扩展
 * @see \EngineCore\extension\ExtensionInfo::RUN_MODULE_EXTENSION
 *  - 1: 运行开发者扩展，运行在'@developer'目录下的扩展
 * @see \EngineCore\extension\ExtensionInfo::RUN_MODULE_DEVELOPER
 *
 * 约定：
 *  - 系统扩展控制器，位于项目内'@extensions'目录下的控制器
 *  - 开发者控制器，位于项目内'@developer'目录下的控制器
 *  - 用户自定义控制器，位于项目内任何地方，如'@backend/controllers'、'@frontend/controllers'目录下的控制器
 *
 * @extensions为系统扩展
 * @developer、@app(如：@backend、@frontend)等均被视为开发者扩展，因为这些目录均是开发者自行开发的功能文件。
 * 而@developer目录，则是专门用于存放对@extensions目录进行二次开发的文件。
 *
 * 注意：
 * 为保证所有基于EngineCore核心框架的项目以及扩展能够无缝甚至低改变成本来使用你的项目或扩展，我们不建议开发者自定义
 * ‘调度器运行模式规则’（RunRule）。遵守EngineCore的约定和规范，可以让其它开发者不需付出任何改变成本，
 * 即可做到最大的兼容性和保持EngineCore配置习惯与逻辑顺序的一致性。
 *
 * 当然，如果是在不更改任何配置习惯和逻辑顺序的前提下对‘调度器运行模式规则’进行优化或功能完善，这是非常好的，
 * 建议可以提交你的优化或反馈bug。
 *
 * EngineCore一直秉持着对二次开发友好和便捷的理念，尽量考虑到所有可能需要被更改或扩展的环节，我们统一规范，设计接口。
 * 因此，我们同样把'调度器运行模式规则'解耦出来，在你确定需要自定义的时候，依然可以很方便地进行二次开发，
 * 而不需要更改EngineCore核心框架文件，除了重大的结构和功能更新外，丝毫不影响后续的升级和使用。
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class RunRule extends BaseObject implements DispatchRunRuleInterface
{
    
    /**
     * @var DispatchManager 调度器管理器
     */
    protected $dm;
    
    /**
     * RunRule constructor.
     *
     * @param BaseDispatchManager $dispatchManager
     * @param array               $config
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct(BaseDispatchManager $dispatchManager, array $config = [])
    {
        $this->dm = $dispatchManager;
        parent::__construct($config);
    }
    
    /**
     * @var bool 是否开发者运行模式
     */
    protected $_isDeveloperMode;
    
    /**
     * 是否启用开发者运行模式，只有当前控制器属于系统扩展控制器才生效。
     * 当控制器为开发者控制器或用户自定义控制器时，将禁用开发者运行模式。
     *
     * 可以通过设置该值为'1'，启用开发者运行模式，然后在开发者目录里添加需要替换的调度器，
     * 即可简单实现对系统扩展调度器的替换，此方法无需修改系统扩展内的调度器，
     * 从而避免系统扩展升级后可能带来不兼容或修改被覆盖重置等问题。
     *
     * 具体调用哪个调度器的操作由[[Generator::_guessDispatchClass()]]执行。
     * @see \EngineCore\dispatch\Generator::_guessDispatchClass()
     *
     * 注意：
     *  - 当控制器为开发者控制器或用户自定义控制器时，系统将自动视为开发者运行模式。
     *
     * @return bool
     */
    public function isDeveloperMode(): bool
    {
        if (null === $this->_isDeveloperMode) {
            $this->_isDeveloperMode = false;
            // 当控制器为开发者控制器或用户自定义控制器时，系统将自动视为开发者运行模式。
            if (!$this->dm->getController()->getExtension()->isExtensionController()) {
                return $this->_isDeveloperMode;
            }
            // 当前调度器不存在调度配置信息则默认为系统扩展运行模式
            if (null === ($config = $this->dm->getCurrentDispatchMap())) {
                return $this->_isDeveloperMode;
            }
            // 当前调度器配置的运行模式
            $run = ArrayHelper::getValue($config, 'run', null);
            /**
             * 假如一个调度管理器的配置如下，优先级：全局配置 > 调度器配置 > 控制器配置 > 扩展数据库配置
             * ```php
             * 'EngineCore\dispatch\DispatchManagerInterface' => [
             *      'class' => 'EngineCore\dispatch\DispatchManager',
             *      'run' => bool,  // 全局配置
             *      'config' => [
             *          '{route}' => [
             *              'run' => bool,  // 控制器配置
             *              'dispatchMap' => [
             *                  'index' => [
             *                      'run' => bool,  // 调度器配置
             *                  ],
             *              ],
             *          ],
             *      ],
             * ]
             * ```
             */
            if (ExtensionInfo::RUN_MODULE_EXTENSION === $this->dm->run) {
                // 全局禁用时优先级最高
                $this->_isDeveloperMode = false;
            } elseif (null !== $run) {
                // 调度器配置
                $this->_isDeveloperMode = $run;
            } elseif (null !== $this->dm->getController()->run) {
                // 控制器配置
                $this->_isDeveloperMode = $this->dm->getController()->run;
            } elseif (
                // 扩展数据库配置启用
                ExtensionInfo::RUN_MODULE_DEVELOPER === $this->dm->getController()->getExtension()->dbConfig['run'] ||
                // 全局启用，可以避免为每个扩展启用开发者运行模式
                ExtensionInfo::RUN_MODULE_DEVELOPER === $this->dm->run
            ) {
                $this->_isDeveloperMode = true;
            }
        }
        
        return $this->_isDeveloperMode;
    }
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

use EngineCore\base\Modularity;
use EngineCore\Ec;
use EngineCore\helpers\ArrayHelper;
use EngineCore\helpers\NamespaceHelper;
use EngineCore\helpers\StringHelper;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\web\Application;

/**
 * 调度器生成器
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Generator extends BaseObject implements DispatchGeneratorInterface
{
    
    /**
     * @var BaseDispatchManager 调度器管理器
     */
    protected $dm;
    
    /**
     * Generator constructor.
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
     * 根据当前请求调度器ID的调度器配置，生成所需的调度器
     *
     * @param array $config 当前请求调度器ID的调度器配置
     *
     * @return Dispatch|null
     * @throws InvalidConfigException
     */
    public function createDispatch(array $config)
    {
        // 不存在请求的调度器ID，则终止生成操作
        if (null === $id = $this->dm->getRequestDispatchId()) {
            return null;
        }
        $config = $this->initConfig($id, $config);
        /** @var Dispatch $dispatch */
        $dispatch = null;
        if (class_exists($config['class'])) {
            if (is_subclass_of($config['class'], Dispatch::class)) {
                $dispatch = Yii::createObject($config, [$id, $this->dm->getController(),]);
                if (get_class($dispatch) !== $config['class']) {
                    $dispatch = null;
                }
            } elseif (YII_DEBUG) {
                throw new InvalidConfigException("`{$config['class']}` class must extend from `" . Dispatch::class . "`.");
            }
        }
        
        if (null === $dispatch) {
            $this->_generateDispatchFile($config['class']);
        } else {
            Yii::debug('Loading dispatch: ' . $config['class'], __METHOD__);
        }
        
        return $dispatch;
    }
    
    /**
     * 根据当前请求调度器ID所需的调度器配置初始化为实际可用的调度器配置
     *
     * @param string $id 调度器ID
     * @param array  $config 调度器配置
     *
     * @return array
     */
    protected function initConfig($id, array $config): array
    {
        // 调度器支持视图渲染功能
        if ($this->isSupportRender()) {
            // 调度器需要渲染的视图文件，默认使用当前调度器ID的视图文件名
            $config['response']['viewFile'] = $config['response']['viewFile'] ?? $id;
            if ($this->dm->getThemeRule()->isEnableTheme()) {
                // 调度器默认在该主题目录下寻找需要渲染的视图文件
                $config['response']['themeName'] = $config['response']['themeName'] ?? Ec::getThemeConfig('name');
            }
        }
        // 开启调试模式
        $debug = ArrayHelper::remove($config, 'debug', null);
        // 调度器类名未指定，则根据调度器配置猜测调度器类名
        if (!isset($config['class'])) {
            $config['class'] = $this->_guessDispatchClass($id, $config);
        } else {
            // `class`被明确指定时，`map`映射配置将不生效
            unset($config['class']['map']);
        }
        
        // 初始化默认调度响应器
        $this->_initDispatchResponse($config);
        
        // 设置调度器视图文件
        $this->_setViewFile($config);
        
        // 剔除不必要的配置数据
        $this->_fixConfig($config);
        
        // 开启调度器调试信息
        if ($debug) {
            $this->_debug($id, $config);
        }
        
        return $config;
    }
    
    /**
     * 根据调度器配置猜测调度器类名
     *
     * @param string $id
     * @param array  $config
     *
     * @return string
     */
    private function _guessDispatchClass($id, &$config)
    {
        if ($this->isSupportRender() && $this->dm->getThemeRule()->isEnableTheme()) {
            // 转换为适用于命名空间的主题名格式
            $themeName = NamespaceHelper::normalizeStringForNamespace($config['response']['themeName']);
        } else {
            $themeName = '';
        }
        // 替换为映射后的调度路由
        $route = $this->dm->getController()->id . '/' . ArrayHelper::remove($config, 'map', $id);
        // 调度器路由类名
        $classString = NamespaceHelper::normalizeStringForNamespace($route);
        // 调度器命名空间
        $dispatchNs = $this->dm->getController()->module instanceof Modularity ?
            $this->dm->getController()->module->dispatchNamespace :
            $this->dm->getController()->getExtension()->getNamespace() . '\\dispatches';
        $dispatchNs .= '\\';
        // 开发者运行模式下，只有当前控制器属于系统扩展控制器才生效
        if ($this->getRunRule()->isDeveloperMode()) {
            // 开发者调度器命名空间
            $devDispatchNs = StringHelper::replace($dispatchNs, 'extensions', 'developer');
            // 开启主题功能，调度管理器将会在指定的主题目录内调用调度器
            if ($this->dm->getThemeRule()->isEnableTheme()) {
                // 如果指定主题的开发者调度器不存在，则获取开发者目录下的默认主题的调度器
                if (!class_exists($config['class'] = $devDispatchNs . $themeName . '\\' . $classString)) { // 开发者调度器
                    // 开发者默认主题调度器不存在，则获取系统扩展内该指定主题的调度器
                    if (!class_exists($config['class'] = $this->_getDefaultDispatch($devDispatchNs, $classString, $themeName))) {
                        Yii::info(
                            $devDispatchNs . $themeName . '\\' . $classString .
                            ': Developer extension dispatch does not exist and automatically calls system extension dispatch.',
                            __METHOD__
                        );
                        // 系统扩展内指定主题的调度器不存在，则获取系统扩展内的默认主题调度器
                        if (!class_exists($config['class'] = $dispatchNs . $themeName . '\\' . $classString)) {
                            if (!class_exists($config['class'] = $this->_getDefaultDispatch($dispatchNs, $classString, $themeName))) {
                                // 还原为原来的调度器，有助于系统准确提醒具体哪个调度器需要创建
                                $config['class'] = $dispatchNs . $themeName . '\\' . $classString;
                            }
                        }
                    }
                }
            } // 关闭主题功能
            else {
                // 如果开发者调度器不存在，则获取系统扩展调度器
                if (!class_exists($config['class'] = $devDispatchNs . $classString)) { // 开发者调度器
                    if (class_exists($config['class'] = $dispatchNs . $classString)) { // 系统扩展调度器
                        Yii::info(
                            $devDispatchNs . $classString .
                            ': Developer extension dispatch does not exist and automatically calls system extension dispatch.',
                            __METHOD__
                        );
                    } else {
                        // 还原为原来的调度器，有助于系统准确提醒具体哪个调度器需要创建
                        $config['class'] = $devDispatchNs . $classString;
                    }
                }
            }
        } else {
            // 开启主题功能，调度管理器将会在指定的主题目录内调用调度器
            if ($this->dm->getThemeRule()->isEnableTheme()) {
                // 系统扩展内指定主题的调度器不存在，则获取系统扩展内的默认主题调度器
                if (!class_exists($config['class'] = $dispatchNs . $themeName . '\\' . $classString)) {
                    $config['class'] = $this->_getDefaultDispatch($dispatchNs, $classString, $themeName);
                }
            } // 关闭主题功能
            else {
                $config['class'] = $dispatchNs . $classString;
            }
        }
        
        return $config['class'];
    }
    
    /**
     * 初始化默认调度响应器
     *
     * @param array &$config
     */
    private function _initDispatchResponse(&$config)
    {
        if (!isset($config['response']['class'])) {
            if ($this->isSupportRender()) {
                $config['response']['class'] = Ec::getThemeConfig('response');
            }
        }
    }
    
    /**
     * 设置调度器视图文件
     *
     * @param array &$config
     */
    private function _setViewFile(&$config)
    {
        if ($this->isSupportRender()) {
            // 视图文件是否存在视图同步标记'#'
            if (strrpos($config['response']['viewFile'], '#') === 0) {
                // 存在视图同步标记'#'，则跟随调度器当前位置自动同步需要渲染的视图目录
                $config['response']['viewFile'] = substr($config['response']['viewFile'], 1);
                if (($pos = strrpos($config['class'], '\\dispatches')) !== false) {
                    // 将视图文件替换为别名路径
                    $viewFile = '@' . substr(str_replace('\\', '/', $config['class']), 0, $pos);
                    $viewFile .= ($this->dm->getThemeRule()
                            ->isEnableTheme() ? '/themes/' . $config['response']['themeName'] : '/views') .
                        '/' . $this->dm->getController()->id . '/' . $config['response']['viewFile'];
                    $config['response']['viewFile'] = $viewFile;
                }
            }
        }
    }
    
    /**
     * 调度器调试信息
     *
     * @param string $id
     * @param array  $config
     */
    private function _debug($id, $config)
    {
        if (YII_ENV_DEV) {
            if (Yii::$app instanceof Application) {
                echo get_called_class() . ":</br></br>以下是 `" . $this->dm->getController()->getUniqueId()
                    . '/' . $id . "` 的调试信息：</br></br>";
                echo '当前调度器（' . NamespaceHelper::normalizeStringForNamespace($id) . '）的配置信息：';
                Ec::dump($config);
                echo '控制器调度配置信息：';
                Ec::dump($this->dm->getControllerDispatchMap());
                Yii::$app->end();
            }
        }
    }
    
    /**
     * 剔除不必要的配置数据
     *
     * @param array &$config
     */
    private function _fixConfig(&$config)
    {
        unset($config['response']['themeName'], $config['run']);
    }
    
    /**
     * 获取默认调度器
     *
     * @param string $dispatchNs
     * @param string $classString
     * @param string $themeName
     *
     * @return string
     */
    private function _getDefaultDispatch($dispatchNs, $classString, $themeName)
    {
        $oldClass = $dispatchNs . $themeName . '\\' . $classString;
        // 调度器不是默认调度器则获取默认调度器
        if ($themeName !== $this->dm->defaultThemeName) {
            if (class_exists($class = $dispatchNs . $this->dm->defaultThemeName . '\\' . $classString)) {
                Yii::info(
                    $oldClass . ': Dispatch does not exist and automatically calls default dispatch.',
                    __METHOD__
                );
                
                return $class;
            }
        }
        
        return $oldClass;
    }
    
    /**
     * 调度器不存在则抛出友好提示信息
     *
     * @param string $className 调度器类名
     *
     * @throws InvalidConfigException
     */
    private function _generateDispatchFile($className)
    {
        if (YII_DEBUG) {
            throw new InvalidConfigException("请在该路径下创建调度器文件:\r\n"
                . NamespaceHelper::namespace2Path($className) . '.php');
        }
    }
    
    /**
     * @var SimpleParser 调度器配置解析器
     */
    private $_parser;
    
    /**
     * 获取调度器配置解析器
     *
     * @return DispatchConfigParserInterface
     */
    public function getParser()
    {
        if (null === $this->_parser) {
            $this->setParser(SimpleParser::class);
        }
        
        return $this->_parser;
    }
    
    /**
     * 设置调度器配置解析器
     *
     * @param string|array|callable $parser
     *
     * @throws InvalidConfigException
     */
    public function setParser($parser)
    {
        $this->_parser = Ec::createObject($parser, [$this->dm], DispatchConfigParserInterface::class);
    }
    
    /**
     * @var RunRule 调度器运行模式规则
     */
    private $_runRule;
    
    /**
     * 获取调度器运行模式规则
     *
     * @return DispatchRunRuleInterface
     */
    public function getRunRule()
    {
        if (null === $this->_runRule) {
            $this->setRunRule(RunRule::class);
        }
        
        return $this->_runRule;
    }
    
    /**
     * 设置调度器运行模式规则
     *
     * @param string|array|callable $runRule
     *
     * @throws InvalidConfigException
     */
    public function setRunRule($runRule)
    {
        $this->_runRule = Ec::createObject($runRule, [$this->dm], DispatchRunRuleInterface::class);
    }
    
    /**
     * 调度器是否支持视图渲染功能
     *
     * @param bool $throwException 是否抛出异常
     *
     * @return bool
     * @throws NotSupportedException
     */
    public function isSupportRender($throwException = false): bool
    {
        if (!Yii::$app instanceof Application) {
            if ($throwException) {
                throw new NotSupportedException('The current application does not support the dispatch response render function.');
            } else {
                return false;
            }
        }
        
        return true;
    }
    
}
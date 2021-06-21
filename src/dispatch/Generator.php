<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

use EngineCore\base\Modularity;
use EngineCore\Ec;
use EngineCore\enums\EnableEnum;
use EngineCore\helpers\ArrayHelper;
use EngineCore\helpers\NamespaceHelper;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\web\Application;

/**
 * 调度器生成器
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Generator extends BaseObject implements DispatchGeneratorInterface
{
    
    /**
     * @var AbstractDispatchManager 调度器管理器
     */
    protected $dm;
    
    /**
     * Generator constructor.
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
     * @param string $id     调度器ID
     * @param array  $config 调度器配置
     *
     * @return array
     */
    protected function initConfig($id, array $config): array
    {
        // 初始化调度响应器
        $this->_initDispatchResponse($id, $config);
        // 开启调试模式
        $debug = ArrayHelper::remove($config, 'debug', false);
        // 调度器类名未指定，则根据调度器配置猜测调度器类名
        if (!isset($config['class'])) {
            $config['class'] = $this->_guessDispatchClass($id, $config);
        } else {
            // `class`被明确指定时，`map`映射配置将不生效
            unset($config['map']);
        }
        
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
     * @param array  &$config
     *
     * @return string
     */
    private function _guessDispatchClass($id, &$config)
    {
        // 替换为映射后的调度路由
        $route = $this->dm->getController()->id . '/' . ArrayHelper::remove($config, 'map', $id);
        // 转换为适用于命名空间的主题名格式
        $themeName = NamespaceHelper::normalizeStringForNamespace($config['response']['themeName']);
        // 调度器路由类名
        $classString = NamespaceHelper::normalizeStringForNamespace($route);
        // 调度器命名空间
        $dispatchNs = $this->dm->getController()->module instanceof Modularity ?
            $this->dm->getController()->module->dispatchNamespace :
            $this->dm->getController()->getExtension()->getNamespace() . '\\dispatches';
        $dispatchNs .= '\\';
        // 开发者运行模式下，只有当前控制器属于系统扩展控制器才生效
        if ($this->dm->getRunRule()->isDeveloperMode()) {
            // 开发者调度器命名空间
            $devDispatchNs = 'developer\\' . $dispatchNs;
            // 开启主题功能，优先从主题目录内获取调度器
            if ($this->dm->getTheme()->isEnableTheme()) {
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
                                $config['class'] = $devDispatchNs . $themeName . '\\' . $classString;
                            }
                        }
                    }
                }
            } // 关闭主题功能
            else {
                // 如果开发者调度器不存在，则获取系统扩展调度器
                if (!class_exists($config['class'] = $devDispatchNs . $themeName . '\\' . $classString)) { // 开发者调度器
                    if (class_exists($config['class'] = $dispatchNs . $themeName . '\\' . $classString)) { // 系统扩展调度器
                        Yii::info(
                            $devDispatchNs . $themeName . '\\' . $classString .
                            ': Developer extension dispatch does not exist and automatically calls system extension dispatch.',
                            __METHOD__
                        );
                    } else {
                        // 还原为原来的调度器，有助于系统准确提醒具体哪个调度器需要创建
                        $config['class'] = $devDispatchNs . $themeName . '\\' . $classString;
                    }
                }
            }
        } else {
            // 开启主题功能，优先从主题目录内获取调度器
            if ($this->dm->getTheme()->isEnableTheme()) {
                $config['class'] = $dispatchNs . $themeName . '\\' . $classString;
                // 系统扩展内指定主题的调度器不存在，则获取系统扩展内的默认主题调度器
                if (!class_exists($config['class'])) {
                    $config['class'] = $this->_getDefaultDispatch($dispatchNs, $classString, $themeName);
                }
            } // 关闭主题功能
            else {
                $config['class'] = $dispatchNs . $themeName . '\\' . $classString;
            }
        }
        
        return $config['class'];
    }
    
    /**
     * 初始化调度响应器
     *
     * @param string $id
     * @param array  &$config
     */
    private function _initDispatchResponse($id, &$config)
    {
        // 调度器支持视图渲染功能
        if ($this->dm->isSupportRender()) {
            $responseConfig = Ec::$service->getExtension()->getThemeRepository()->getConfig();
            $defaultConfig = Ec::$service->getExtension()->getThemeRepository()->getDefaultConfig();
            // 调度响应器
            $config['response']['class'] = $config['response']['class'] ?? (
                $this->dm->getTheme()->isEnableTheme() ? $responseConfig['response'] : $defaultConfig['response']
                );
            // 调度器需要渲染的视图文件，默认使用当前调度器ID的视图文件名
            $config['response']['viewFile'] = $config['response']['viewFile'] ?? $id;
            // 调度器默认在该主题目录下寻找需要渲染的视图文件
            $config['response']['themeName'] = $config['response']['themeName'] ?? (
                $this->dm->getTheme()->isEnableTheme() ? $responseConfig['name'] : $defaultConfig['name']
                );
        }
    }
    
    /**
     * 设置调度响应器视图文件
     *
     * @param array &$config
     */
    private function _setViewFile(&$config)
    {
        if ($this->dm->isSupportRender()) {
            // 视图文件存在视图同步标记'#'
            if (strrpos($config['response']['viewFile'], '#') === 0) {
                // 存在视图同步标记'#'，则跟随调度器当前位置自动同步需要渲染的视图文件
                $config['response']['viewFile'] = substr($config['response']['viewFile'], 1);
                if (($pos = strrpos($config['class'], '\\dispatches')) !== false) {
                    // 将视图文件替换为别名路径
                    $viewFile = '@' . substr(str_replace('\\', '/', $config['class']), 0, $pos);
                    $viewFile .= ($this->dm->getTheme()->isStrict()
                            ? '/themes/' . $config['response']['themeName']
                            : '/views')
                        . '/' . $this->dm->getController()->id . '/' . $config['response']['viewFile'];
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
                echo get_called_class() . ":</br></br>" .
                    
                    "以下是路由 `" . $this->dm->getController()->getUniqueId()
                    . '/' . $id . "` 的调试信息：</br></br>";
                echo '多主题功能：' . EnableEnum::value($this->dm->getTheme()->isEnableTheme()) . "</br>";
                echo '主题严谨模式：' . EnableEnum::value($this->dm->getTheme()->isStrict()) . "</br></br>";
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
     * @return string 如果默认调度器不存在，则返回原来的调度器
     */
    private function _getDefaultDispatch($dispatchNs, $classString, $themeName)
    {
        $oldClass = $dispatchNs . $themeName . '\\' . $classString;
        // 调度器不是默认调度器则获取默认调度器
        if ($themeName !== $this->dm->getTheme()->getDefault() &&
            class_exists($class = $dispatchNs . $this->dm->getTheme()->getDefault() . '\\' . $classString)
        ) {
            Yii::info(
                $oldClass . ': Dispatch does not exist and automatically calls default dispatch.',
                __METHOD__
            );
            
            return $class;
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
            throw new InvalidConfigException("请在该路径下创建调度器文件:\r\n" . NamespaceHelper::namespace2Path($className) . '.php');
        }
    }
    
}
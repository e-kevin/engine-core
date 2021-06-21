<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\dispatch;

use Yii;
use yii\base\InvalidRouteException;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;

/**
 * 系统调度功能（Dispatch）管理基础实现类
 *
 * 系统的调度功能由调度管理类负责管理和调度所需调度器
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class DispatchManager extends AbstractDispatchManager
{
    
    /**
     * @var array 当前控制器的调度器配置
     */
    protected $_controllerDispatchMap;
    
    /**
     * {@inheritdoc}
     */
    public function getControllerDispatchMap(): array
    {
        if (null === $this->_controllerDispatchMap) {
            // 当前控制器的调度配置数据
            // 合并当前控制器的调度配置数据，全局配置数据优先级最高
            $dispatchMap = ArrayHelper::merge(
                $this->getController()->dispatchMap
                    ? $this->getParser()->normalize($this->getController()->dispatchMap)
                    : [],
                isset($this->config[$this->getController()->getUniqueId()]['dispatchMap'])
                    // 当前控制器的全局调度配置数据
                    ? $this->getParser()->normalize($this->config[$this->getController()->getUniqueId()]['dispatchMap'])
                    : []
            );
            // 当前控制器的默认调度配置数据
            $defaultDispatchMap = $this->getParser()->normalize($this->getController()->getDefaultDispatchMap());
            // 当前调度器不存在调度配置数据，则获取默认调度配置数据
            if (empty($dispatchMap)) {
                return $this->_controllerDispatchMap = $defaultDispatchMap;
            }
            $this->_controllerDispatchMap = ArrayHelper::merge($defaultDispatchMap, $dispatchMap);
        }
        
        return $this->_controllerDispatchMap;
    }
    
    /**
     * @var array 全局调度器配置
     */
    private $_config = [];
    
    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return $this->_config;
    }
    
    /**
     * 设置全局调度器配置，通过配置方式动态调节控制器内的调度器配置数据，支持新增或替换等操作方法，
     * 而无需在原有的目录或文件里作任何更改，从而避免升级等操作可能导致更改被覆盖的问题。
     *
     * @param array $config 调度器配置数据，支持的键名参见：
     *
     * @see       getDispatchConfigKey()
     *
     * @example
     * 环境:
     * @extensions目录，系统扩展目录，用于存放系统扩展。
     * @developer 目录，开发者目录，用于存放一些需要对系统扩展进行二次开发的文件。
     * @app       目录，项目目录，如：@backend、@frontend，目录结构如下：
     * @app
     *      - controllers // 存放控制器
     *          - SiteController
     *      - dispatches // 存放调度器
     *          - Index // Index调度器
     *          - Test // 本案例用于测试的调度器
     *
     * 1、为控制器添加新的操作：
     *    用户可通过路由地址'site/index'进行访问，'site'对应的控制器名为'SiteController'，
     *    现在为'SiteController'控制器添加一个用于测试的调度器，调度器位于'@app/dispatches'目录下，名为'Test'。
     *    现在可通过以下方式进行添加，而无需更改'SiteController'文件，从而避免升级等操作可能导致更改被覆盖的问题。
     *    [
     *         // 路由地址为键名，该键名为控制器实际对应的路由名称
     *         'site' => [
     *             // 通过调度映射配置进行添加
     *             'dispatchMap' => [ // 通过该键名对调度器进行配置
     *                 // '调度器名可以为任何规范的路由地址名称，如：'config-manager'，此处用'test'名
     *                 'test' => [
     *                     'class' => 'app\dispatches\Test', // Test调度器，位于'@app/dispatches/Test'
     *                     'property1' => 'value1',
     *                     'property2' => 'value2',
     *                     ......, // 可以用其他属性值对该类进行配置
     *                 ],
     *             ],
     *         ],
     *    ]
     *    配置完成后，即可通过路由地址'site/test'进行访问。
     * 2、控制器原有操作使用别的调度器实现：
     *    继续上一个例子，将'Index'的实现由'Test'来替换。
     *    [
     *         'site' => [
     *             'dispatchMap' => [
     *                 'index' => [
     *                     'class' => 'app\dispatches\Test', // 用Test调度器来实现
     *                 ],
     *                 // 或者
     *                 'index' => 'app\dispatches\Test', // 直接指定类名
     *                 'index' => 'test', // 调度器映射，'Test'和'Index'必须位于同个目录内，此处为'@app/dispatches'
     *             ],
     *         ],
     *    ]
     *    配置完成后，通过路由地址'site/index'进行访问的结果其实是由Test调度器来实现的。
     * 3、多个操作用同一个调度器来实现：
     *    假如'SiteController'有三个操作，'index','about','contact'。这三个操作只是简单地进行页面渲染输出，
     *    如'return $this->render();'。因为拥有相似的业务内容，我们可以新建一个名为'Common'的调度器，内容大概如下：
     * ```php
     *  public function run()
     * {
     *      return $this->response->render();
     * }
     * ```
     * [[render($view, $assign)]]为调度器基类封装用来渲染页面的方法，此处不明确指定'$view'参数，[[render()]]函数会根据
     * 当前的请求调度器ID来渲染相关的页面，此处分别为'index','about','contact'。
     * @see       \EngineCore\web\DispatchResponse::render();
     *
     * 调度器位于'@app/dispatches/Common'，现在进行配置：
     *    [
     *         'site' => [
     *             'dispatchMap' => [
     *                  // 直接用'Common'调度器映射来替换原来的调度器
     *                 'index' => 'common',
     *                 'about' => 'common',
     *                 'contact' => 'common',
     *             ],
     *         ],
     *    ]
     * 配置完成后，通过路由地址'site/index'，'site/about'，'site/contact'进行访问的结果其实是由Common调度器来实现的。
     * 4、对系统扩展进行二次开发：
     *    假如现在有一个系统扩展'@extensions/engine-core/controller-backend-site'，目录结构如下：
     * @extensions
     *      - engine-core
     *          - controller-backend-site
     *              - SiteController
     *              - dispatches // 存放调度器
     *                  - Index //Index调度器
     *    开发者目录结构如下（和@extensions保持一致）：
     * @developer
     *      - engine-core
     *          - controller-backend-site
     *              - dispatches // 存放调度器
     *
     *    当系统扩展需要进行二次开发时，我们不建议直接修改源码，此时只需把需要修改的文件或需要新增的文件存放在
     * @developer 目录里对应的路径目录内，再进行配置即可。遵循该守则，即可通过配置['run' => 1]属性值，
     * 让调度管理器直接从@developer目录里调用相关文件。
     *
     *    现在为系统扩展'SiteController'新增一个没有的操作方法：
     *    [
     *      'site' => [
     *          // 'run'值为'1'，即可启用开发者运行模式。该模式下，调度管理器会优先获取开发者目录内的调度器
     *          'run' => 1,
     *          'dispatchMap' => [
     *              // 此处只需设置路由地址名即可，系统会自动根据'test'名获取'Test'调度器
     *              'test', // 位于@developer/engine-core/controller-backend-site/dispatches/Test
     *          ],
     *      ],
     *    ]
     *
     * 配置完成后，通过路由地址'site/test'即可正常看到由Test生成的结果。
     *
     * 调度器配置同样适用于模块扩展的设置：
     * [
     *      // 支持模块路由地址
     *      'system/config-manage' => [
     *          'dispatchMap' => [
     *          ],
     *      ],
     * ]
     *
     * @return array
     */
    public function setConfig(array $config): array
    {
        foreach ($config as $rout => $value) {
            // 过滤不规范的配置格式
            if (is_int($rout) || !is_array($value)) {
                continue;
            }
            foreach ($this->getDispatchConfigKey() as $key) {
                // 目前仅支持设置调度器所需要的参数配置
                if (isset($value[$key])) {
                    $this->_config[$rout][$key] = $value[$key];
                }
            }
        }
        
        return $this->_config;
    }
    
    /**
     * 获取调度器配置所需要的参数配置键值
     *
     * 'dispatchMap'的设置格式参考：
     * @see \EngineCore\dispatch\SimpleParser::normalizeArrayConfig() 配置格式参考
     * @see \EngineCore\dispatch\SimpleParser::normalizeStringConfig() 配置格式参考
     *
     * @return array
     */
    protected function getDispatchConfigKey()
    {
        return ['dispatchMap', 'run', 'enableTheme'];
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDispatch($route)
    {
        if (null === $route) {
            throw new InvalidRouteException('The `$route` property must be set.');
        }
        $pos = strpos($route, '/');
        if ($pos === false) {
            return $this->createDispatch($route);
        } elseif ($pos > 0) {
            $parts = $this->getController()->module->createController($route);
        } else {
            $parts = Yii::$app->createController($route);
        }
        if (is_array($parts)) {
            /* @var \EngineCore\web\Controller|\yii\console\Controller $controller */
            list($controller, $actionID) = $parts;
            // 当前控制器没有开启系统调度功能，无法获取到相关调度器
            if (!$controller->hasMethod('getDispatchManager')) {
                throw new NotSupportedException('The current controller does not support the dispatch function and cannot obtain the relevant Dispatch.');
            }
            $oldController = Yii::$app->controller;
            Yii::$app->controller = $controller;
        } else {
            throw new InvalidRouteException('Unable to resolve the request: ' . $route);
        }
        
        $dispatch = $controller->getDispatchManager()->createDispatch($actionID);
        
        if ($oldController !== null) {
            Yii::$app->controller = $oldController;
        }
        
        return $dispatch;
    }
    
}
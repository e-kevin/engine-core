<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\services;

use EngineCore\Ec;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\base\Service;
use EngineCore\extension\entity\ExtensionEntity;
use EngineCore\extension\entity\ExtensionEntityInterface;
use EngineCore\services\extension\ConfigRepository;
use EngineCore\services\extension\ControllerRepository;
use EngineCore\services\extension\Dependent;
use EngineCore\services\extension\Environment;
use EngineCore\services\extension\ModularityRepository;
use EngineCore\services\extension\Repository;
use EngineCore\services\extension\ThemeRepository;
use EngineCore\services\extension\UrlManager;
use Yii;

/**
 * 系统扩展管理服务类，用于管理'@extensions'目录下的扩展文件
 *
 * @property ControllerRepository|Service $controllerRepository
 * @property ModularityRepository|Service $modularityRepository
 * @property ThemeRepository|Service $themeRepository
 * @property ConfigRepository|Service $configRepository
 * @property Dependent|Service $dependent
 * @property UrlManager|Service $urlManager
 * @property Environment|Service $environment
 * @property Repository|Service $repository
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Extension extends Service
{

    const
        CONTROLLER_REPOSITORY_SERVICE = 'controller', // 控制器仓库管理服务类
        MODULARITY_REPOSITORY_SERVICE = 'modularity', // 模块仓库管理服务类
        THEME_REPOSITORY_SERVICE = 'theme', // 主题仓库管理服务类
        CONFIG_REPOSITORY_SERVICE = 'config', // 系统配置仓库管理服务类
        DEPENDENT_SERVICE = 'dependent', // 扩展依赖服务类
        URL_MANAGER_SERVICE = 'urlManager', // 扩展路由管理服务类
        ENVIRONMENT_SERVICE = 'environment', // 扩展环境服务类
        REPOSITORY_SERVICE = 'repository'; // 扩展仓库服务类

    /**
     * 获取运行模式列表
     *
     * @return array
     */
    public function getRunModeList()
    {
        return [
            ExtensionInfo::RUN_MODULE_DEVELOPER => '开发者扩展',
            ExtensionInfo::RUN_MODULE_EXTENSION => '系统扩展',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function coreServices()
    {
        return [
            self::REPOSITORY_SERVICE => [
                'class' => 'EngineCore\services\extension\Repository',
                'finder' => [
                    'class' => 'EngineCore\extension\repository\configuration\JsonConfigurationFinder',
                ],
            ],
            self::CONTROLLER_REPOSITORY_SERVICE => ['class' => 'EngineCore\services\extension\ControllerRepository'],
            self::MODULARITY_REPOSITORY_SERVICE => ['class' => 'EngineCore\services\extension\ModularityRepository'],
            self::THEME_REPOSITORY_SERVICE => ['class' => 'EngineCore\services\extension\ThemeRepository'],
            self::CONFIG_REPOSITORY_SERVICE => ['class' => 'EngineCore\services\extension\ConfigRepository'],
            self::ENVIRONMENT_SERVICE => ['class' => 'EngineCore\services\extension\Environment'],
            self::DEPENDENT_SERVICE => ['class' => 'EngineCore\services\extension\Dependent'],
            self::URL_MANAGER_SERVICE => ['class' => 'EngineCore\services\extension\UrlManager'],
        ];
    }

    /**
     * 控制器仓库管理服务类
     *
     * @return ControllerRepository|Service
     */
    public function getControllerRepository()
    {
        return $this->getService(self::CONTROLLER_REPOSITORY_SERVICE);
    }

    /**
     * 模块仓库管理服务类
     *
     * @return ModularityRepository|Service
     */
    public function getModularityRepository()
    {
        return $this->getService(self::MODULARITY_REPOSITORY_SERVICE);
    }

    /**
     * 主题仓库管理服务类
     *
     * @return ThemeRepository|Service
     */
    public function getThemeRepository()
    {
        return $this->getService(self::THEME_REPOSITORY_SERVICE);
    }

    /**
     * 系统配置仓库管理服务类
     *
     * @return ConfigRepository|Service
     */
    public function getConfigRepository()
    {
        return $this->getService(self::CONFIG_REPOSITORY_SERVICE);
    }

    /**
     * 扩展依赖服务类
     *
     * @return Dependent|Service
     */
    public function getDependent()
    {
        return $this->getService(self::DEPENDENT_SERVICE);
    }

    /**
     * 扩展路由管理服务类
     *
     * @return UrlManager|Service
     */
    public function getUrlManager()
    {
        return $this->getService(self::URL_MANAGER_SERVICE);
    }

    /**
     * 扩展环境服务类
     *
     * @return Environment|Service
     */
    public function getEnvironment()
    {
        return $this->getService(self::ENVIRONMENT_SERVICE);
    }

    /**
     * 扩展仓库服务类
     *
     * @return Repository|Service
     */
    public function getRepository()
    {
        return $this->getService(self::REPOSITORY_SERVICE);
    }

    /**
     * 获取指定对象所属的扩展信息详情实体，如果对象不属于任何一个扩展，则默认为EngineCore扩展
     *
     * @param object $object
     *
     * @return object|ExtensionEntityInterface
     */
    public static function entity($object): ExtensionEntityInterface
    {
        if (Yii::$container->has('ExtensionEntity')) {
            $definition = Yii::$container->definitions['ExtensionEntity'];
        } else {
            $definition['class'] = ExtensionEntity::class;
        }

        return Ec::createObject($definition, [$object], ExtensionEntityInterface::class);
    }

    /**
     * {@inheritdoc}
     * 删除扩展有关的所有缓存信息
     */
    public function clearCache()
    {
        $this->getRepository()->getFinder()->clearCache();
        $this->getRepository()->clearCache();
        $this->getDependent()->clearCache();
    }

}
<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\services\extension;

use EngineCore\Ec;
use EngineCore\enums\StatusEnum;
use EngineCore\extension\repository\models\ThemeModelInterface;
use EngineCore\extension\repository\info\ThemeInfo;
use EngineCore\extension\setting\SettingProviderInterface;
use Exception;
use Yii;
use yii\base\InvalidConfigException;

/**
 * 主题仓库管理服务类
 *
 * @property string                                   $currentTheme   当前主题名，只读属性
 * @property array                                    $allActiveTheme 所有激活的主题，只读属性
 * @property \yii\db\ActiveRecord|ThemeModelInterface $model
 * @property array                                    $defaultConfig  默认主题的参数配置
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ThemeRepository extends BaseCategoryRepository
{
    
    protected $extensionInfo = ThemeInfo::class;
    
    private $_model;
    
    /**
     * {@inheritdoc}
     * @return \yii\db\ActiveRecord|ThemeModelInterface
     */
    public function getModel(): ThemeModelInterface
    {
        if (null === $this->_model) {
            throw new InvalidConfigException(get_class($this) . ' - The `model` property must be set.');
        }
        
        return $this->_model;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setModel($config = [])
    {
        $this->_model = Ec::createObject($config, [], ThemeModelInterface::class);
    }
    
    /**
     * {@inheritdoc}
     */
    public function hasModel(): bool
    {
        return null !== $this->_model;
    }
    
    private $_activeTheme;
    
    /**
     * 获取应用激活的主题信息类
     *
     * @param string $app
     * @param bool   $throwException
     *
     * @return ThemeInfo|null
     * @throws Exception
     */
    public function getActiveTheme($app = null, $throwException = true)
    {
        $app = $app ?: Yii::$app->id;
        if (!isset($this->_activeTheme[$app])) {
            $uniqueName = $this->getModel()->getActiveTheme($app);
            $this->_activeTheme[$app] = $this->getConfigurationByApp(false, $app)[$uniqueName] ?? null;
            if (null === $this->_activeTheme[$app] && $throwException) {
                throw new Exception('The active theme does not exist for the current application.');
            }
        }
        
        return $this->_activeTheme[$app];
    }
    
    /**
     * {@inheritdoc}
     *
     * @return \EngineCore\extension\repository\models\Theme|null|\yii\db\ActiveRecord
     */
    public function findOne(string $uniqueName, $app = null)
    {
        $app = $app ?: Yii::$app->id;
        $configuration = $this->getConfigurationByApp(false, $app);
        if (!isset($configuration[$uniqueName])) {
            return null;
        }
        
        /** @var ThemeInfo $infoInstance */
        $infoInstance = $configuration[$uniqueName];
        $model = $this->getModel()->findByUniqueName($uniqueName, $app);
        if (null === $model) {
            $model = $this->getModel()->loadDefaultValues();
            // 根据扩展配置信息构建模型基础数据
            $model->setAttributes([
                'unique_id'   => $infoInstance->getUniqueId(),
                'unique_name' => $uniqueName,
                'theme_id'    => $infoInstance->getId(),
                'status'      => StatusEnum::STATUS_ON,
                'app'         => $app,
                'version'     => $infoInstance->getConfiguration()->getVersion(),
            ]);
        }
        
        $model->setInfoInstance($infoInstance);
        
        return $model;
    }
    
    private $_config;
    
    /**
     * 获取指定应用激活的主题参数配置
     *
     * @param string|null $key
     * @param string      $app
     *
     * @return array|null|string
     */
    public function getConfig($key = null, $app = null)
    {
        $app = $app ?: Yii::$app->id;
        if (!isset($this->_config[$app])) {
            if ($app === Yii::$app->id && isset(Yii::$app->params['themeConfig'])) {
                $this->_config[$app] = Yii::$app->params['themeConfig'];
            } else {
                $activeTheme = $this->hasModel() ? $this->getActiveTheme($app, false) : null;
                $this->_config[$app] = $activeTheme ? $activeTheme->getThemeConfig() : $this->getDefaultConfig(null, $app);
            }
        }

        return $key ? ($this->_config[$app][$key] ?? null) : $this->_config[$app];
    }
    
    private $_defaultConfig;
    
    /**
     * 获取指定应用默认主题的参数配置
     *
     * @param string|null $key
     * @param string      $app
     *
     * @return array|null|string
     */
    public function getDefaultConfig($key = null, $app = null)
    {
        $app = $app ?: Yii::$app->id;
        if (!isset($this->_defaultConfig[$app])) {
            $defaultTheme = Ec::$service->getSystem()->getSetting()->get(SettingProviderInterface::DEFAULT_THEME);
            /** @var ThemeInfo $infoInstance */
            $infoInstance = $this->getLocalConfiguration()[$app][$defaultTheme] ?? null;
            $this->_defaultConfig[$app] = $infoInstance ? $infoInstance->getThemeConfig() : [];
        }
        
        return $key ? ($this->_defaultConfig[$app][$key] ?? null) : $this->_defaultConfig[$app];
    }
    
}
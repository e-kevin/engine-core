<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\Ec;
use EngineCore\enums\StatusEnum;
use EngineCore\extension\repository\models\ThemeModelInterface;
use EngineCore\extension\repository\info\ThemeInfo;
use Exception;
use Yii;
use yii\base\InvalidConfigException;

/**
 * 主题仓库管理服务类
 *
 * @property string                                   $currentTheme   当前主题名，只读属性
 * @property array                                    $allActiveTheme 所有激活的主题，只读属性
 * @property \yii\db\ActiveRecord|ThemeModelInterface $model
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
    
    private $_currentTheme;
    
    /**
     * 获取当前主题
     *
     * @param bool $throwException
     *
     * @return ThemeInfo|object
     * @throws Exception
     */
    public function getCurrentTheme($throwException = true)
    {
        if (null === $this->_currentTheme) {
            $uniqueName = $this->getModel()->getCurrentUniqueName();
            $this->_currentTheme = $this->getConfigurationByApp()[$uniqueName];
            if (null === $this->_currentTheme && $throwException) {
                throw new Exception('The active theme does not exist for the current application.');
            }
        }
        
        return $this->_currentTheme;
    }
    
    /**
     * 获取所有激活的主题
     *
     * 注意：
     * 必须返回以应用名为索引格式的数组
     *
     * @return array
     * [
     *  {app} => [],
     * ]
     */
    public function getAllActiveTheme(): array
    {
        return $this->getModel()->getAllActiveTheme();
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
            ]);
        }
        
        $model->setInfoInstance($infoInstance);
        
        return $model;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureInfo($info, $config = [])
    {
        Yii::configure($info, [
            'id' => $config['theme_id'],
        ]);
    }
    
}
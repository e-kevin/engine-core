<?php
/**
 * @link https://github.com/EngineCore/module-extension
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository;

use EngineCore\Ec;
use EngineCore\enums\EnableEnum;
use EngineCore\extension\ExtensionInfo;
use EngineCore\extension\repository\models\RepositoryModelInterface;
use EngineCore\extension\repository\models\Theme;
use EngineCore\extension\ThemeInfo;
use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * 主题扩展仓库类
 *
 * @property Theme $model
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ThemeRepository extends BaseObject implements ThemeRepositoryInterface
{
    
    /**
     * @inheritdoc
     */
    public function getInfo($extensionName): RepositoryModelInterface
    {
        $configuration = Ec::$service->getExtension()->getThemeRepository()->getConfigurationByApp();
        if (!isset($configuration[$extensionName])) {
            throw new NotFoundHttpException('主题扩展不存在');
        }
        
        /** @var ThemeInfo $infoInstance */
        $infoInstance = $configuration[$extensionName]['infoInstance'];
        Yii::configure($this->getModel(), $configuration[$extensionName]['data'] ?
            array_merge($configuration[$extensionName]['data'], ['oldAttributes' => $configuration[$extensionName]['data']]) :
            [
                'id'             => $infoInstance->getUniqueId(),
                'extension_name' => $infoInstance->getUniqueName(),
                'is_system'      => intval($infoInstance->isSystem),
                'run'            => ExtensionInfo::RUN_MODULE_EXTENSION,
                'status'         => EnableEnum::ENABLE,
            ]);
        $this->getModel()->setInfoInstance($infoInstance);
        $this->getModel()->on($this->getModel()::EVENT_SYNC, [new SyncExtensionDataEvent(), 'themeEvent']);
        
        return $this->getModel();
    }
    
    /**
     * @inheritdoc
     */
    public function getAll()
    {
        return ArrayHelper::index($this->getModel()->getAll(), 'extension_name');
    }
    
    /**
     * @var Theme 扩展模型
     */
    private $_model;
    
    /**
     * @inheritdoc
     * @return Theme
     */
    public function getModel(): RepositoryModelInterface
    {
        if (null === $this->_model) {
            $this->setModel(Theme::class);
        }
        
        return $this->_model;
    }
    
    /**
     * @inheritdoc
     */
    public function setModel($config = [])
    {
        $this->_model = Ec::createObject($config, [], RepositoryModelInterface::class);
    }
    
    /**
     * @inheritdoc
     */
    public function getCurrentTheme(): string
    {
        $themeName = $this->getModel()::find()
                          ->where([
                              'status'         => EnableEnum::ENABLE,
                              'extension_name' => array_keys(Ec::$service->getExtension()->getThemeRepository()->getConfigurationByApp()),
                          ])->select('extension_name')->scalar();
        
        return $themeName ?: 'EngineCore/theme-bootstrap-v3'; // 默认主题
    }
    
    /**
     * @inheritdoc
     */
    public function getAllActiveTheme(): array
    {
        $installed = $this->getAll();
        foreach ($installed as $uniqueName => $row) {
            if ($row['status'] == EnableEnum::DISABLE) {
                unset($installed[$uniqueName]);
            }
        }
        
        return $installed;
    }
    
}

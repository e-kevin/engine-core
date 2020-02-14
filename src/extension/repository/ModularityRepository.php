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
use EngineCore\extension\ModularityInfo;
use EngineCore\extension\repository\models\Module;
use EngineCore\extension\repository\models\RepositoryModelInterface;
use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * 模块扩展仓库类
 *
 * @property Module $model
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ModularityRepository extends BaseObject implements ModularityRepositoryInterface
{
    
    /**
     * @inheritdoc
     */
    public function getInfo($extensionName, $onDataBase = true): RepositoryModelInterface
    {
        $configuration = Ec::$service->getExtension()->getModularityRepository()->getConfigurationByApp();
        if (!isset($configuration[$extensionName])) {
            throw new NotFoundHttpException('模块扩展不存在');
        }
        
        /** @var ModularityInfo $infoInstance */
        $infoInstance = $configuration[$extensionName]['infoInstance'];
        Yii::configure($this->getModel(), $configuration[$extensionName]['data'] ?
            array_merge($configuration[$extensionName]['data'], ['oldAttributes' => $configuration[$extensionName]['data']]) :
            [
                'id'             => $infoInstance->getUniqueId(),
                'extension_name' => $infoInstance->getUniqueName(),
                'module_id'      => $infoInstance->id,
                'is_system'      => intval($infoInstance->isSystem),
                'run'            => ExtensionInfo::RUN_MODULE_EXTENSION,
                'status'         => EnableEnum::ENABLE,
            ]);
        $this->getModel()->setInfoInstance($infoInstance);
        $this->getModel()->on($this->getModel()::EVENT_SYNC, [new SyncExtensionDataEvent(), 'moduleEvent']);
        
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
     * @var Module 扩展模型
     */
    private $_model;
    
    /**
     * @inheritdoc
     * @return Module
     */
    public function getModel(): RepositoryModelInterface
    {
        if (null === $this->_model) {
            $this->setModel(Module::class);
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
    
}
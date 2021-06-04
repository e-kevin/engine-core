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
use EngineCore\extension\repository\info\ControllerInfo;
use EngineCore\extension\repository\models\ControllerModelInterface;
use Yii;
use yii\base\InvalidConfigException;

/**
 * 控制器仓库管理服务类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ControllerRepository extends BaseCategoryRepository
{
    
    protected $extensionInfo = ControllerInfo::class;
    
    private $_model;
    
    /**
     * {@inheritdoc}
     * @return \yii\db\ActiveRecord|ControllerModelInterface
     */
    public function getModel(): ControllerModelInterface
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
        $this->_model = Ec::createObject($config, [], ControllerModelInterface::class);
    }
    
    /**
     * {@inheritdoc}
     */
    public function hasModel(): bool
    {
        return null !== $this->_model;
    }
    
    /**
     * {@inheritdoc}
     *
     * @return \EngineCore\extension\repository\models\Controller|null|\yii\db\ActiveRecord
     */
    public function findOne(string $uniqueName, $app = null)
    {
        $app = $app ?: Yii::$app->id;
        $configuration = $this->getConfigurationByApp(false, $app);
        if (!isset($configuration[$uniqueName])) {
            return null;
        }
        
        /** @var ControllerInfo $infoInstance */
        $infoInstance = $configuration[$uniqueName];
        $model = $this->getModel()->findByUniqueName($uniqueName, $app);
        if (null === $model) {
            $model = $this->getModel()->loadDefaultValues();
            // 根据扩展配置信息构建模型基础数据
            $model->setAttributes([
                'unique_id'     => $infoInstance->getUniqueId(),
                'unique_name'   => $uniqueName,
                'controller_id' => $infoInstance->getId(),
                'module_id'     => $infoInstance->getModuleId(),
                'status'        => StatusEnum::STATUS_ON,
                'app'           => $app,
                'version'       => $infoInstance->getConfiguration()->getVersion(),
            ]);
        }
        
        $model->setInfoInstance($infoInstance);
        
        return $model;
    }
    
}
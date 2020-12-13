<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\Ec;
use EngineCore\enums\StatusEnum;
use EngineCore\extension\repository\info\ModularityInfo;
use EngineCore\extension\repository\models\ModuleModelInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * 模块仓库管理服务类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ModularityRepository extends BaseCategoryRepository
{
    
    protected $extensionInfo = ModularityInfo::class;
    
    private $_model;
    
    /**
     * {@inheritdoc}
     * @return \EngineCore\db\ActiveRecord|ModuleModelInterface
     */
    public function getModel(): ModuleModelInterface
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
        $this->_model = Ec::createObject($config, [], ModuleModelInterface::class);
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
     * @return \EngineCore\extension\repository\models\Module|null|\EngineCore\db\ActiveRecord
     */
    public function findOne(string $uniqueName, $app = null)
    {
        $app = $app ?: Yii::$app->id;
        $configuration = $this->getConfigurationByApp(false, $app);
        if (!isset($configuration[$uniqueName])) {
            return null;
        }
        
        /** @var ModularityInfo $infoInstance */
        $infoInstance = $configuration[$uniqueName];
        $model = $this->getModel()->findByUniqueName($uniqueName, $app);
        if (null === $model) {
            $model = $this->getModel()->loadDefaultValues();
            // 根据扩展配置信息构建模型基础数据
            $model->setAttributes([
                'unique_id'   => $infoInstance->getUniqueId(),
                'unique_name' => $uniqueName,
                'module_id'   => $infoInstance->getId(),
                'status'      => StatusEnum::STATUS_ON,
                'app'         => $app,
            ]);
        }
        
        $model->setInfoInstance($infoInstance);
        
        return $model;
    }
    
    /**
     * 获取指定应用【所有|已安装】的模块名称，主要用于列表筛选
     *
     * @param bool $installed 是否获取已安装的扩展，默认为`true`，获取
     * @param null $app
     *
     * @return array e.g.
     * ```php
     * [
     * 'engine-core/module-account' => 'engine-core/module-account',
     * 'engine-core/module-rbac' => 'engine-core/module-rbac',
     * ]
     * ```
     */
    public function getSelectListByApp($installed = true, $app = null)
    {
        return ArrayHelper::getColumn(
            $this->getConfigurationByApp($installed, $app),
            'uniqueName'
        );
    }
    
    /**
     * 获取当前应用未安装的模块ID
     *
     * todo 是否需要删除
     *
     * @return array 未安装的模块ID
     */
//    public function getUninstalledModuleIdByApp()
//    {
//        $installed = $this->getDbConfiguration();
//        $configuration = $this->getConfigurationByApp();
//        // 获取未安装的模块扩展配置
//        foreach ($configuration as $uniqueName => $row) {
//            // 剔除已安装的模块
//            if (isset($installed[$uniqueName])) {
//                unset($configuration[$uniqueName]);
//                continue;
//            }
//        }
//
//        return ArrayHelper::getColumn($configuration, 'infoInstance.id');
//    }
    
    /**
     * {@inheritdoc}
     */
    public function configureInfo($info, $config = [])
    {
        Yii::configure($info, [
            'id' => $config['module_id'],
        ]);
    }
    
}
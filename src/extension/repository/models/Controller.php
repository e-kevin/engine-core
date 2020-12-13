<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository\models;

use EngineCore\Ec;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%viMJHk_controller}}".
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Controller extends BaseExtensionModel implements ControllerModelInterface
{
    
    public static function tableName()
    {
        return '{{%viMJHk_controller}}';
    }
    
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            // module_id rules
            'moduleIdRequired'     => ['module_id', 'required'],
            'moduleIdLength'       => ['module_id', 'string', 'max' => 15],
            // controller_id rules
            'controllerIdRequired' => ['controller_id', 'required'],
            'controllerIdLength'   => ['controller_id', 'string', 'max' => 15],
            // uniqueId rules
            'uniqueIdUnique'       => [
                ['unique_name', 'app', 'module_id', 'controller_id']
                , 'unique', 'targetAttribute' => ['unique_name', 'app', 'module_id', 'controller_id'],
            ],
        ]);
    }
    
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'module_id'     => Yii::t('ec/extension', 'Module id'),
            'controller_id' => Yii::t('ec/extension', 'Controller id'),
        ]);
    }
    
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'module_id' => Yii::t('ec/extension',
                'When empty, the extension is `{app}` application extension.',
                ['app' => Yii::$app->id]
            ),
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $this->getInfoInstance()->install();
        }
        $sameModuleId = $sameControllerId = false;
        if (isset($changedAttributes['module_id'])) {
            // 暂不支持多层模块id设置
            if ($sameModuleId = Yii::$app->controller->module->id == $this->getOldAttribute('module_id')) {
                Yii::$app->controller->module->id = $changedAttributes['module_id'];
            }
        }
        if (isset($changedAttributes['controller_id'])) {
            if ($sameModuleId && ($sameControllerId = Yii::$app->controller->id == $this->getOldAttribute('controller_id'))) {
                Yii::$app->controller->id = $changedAttributes['controller_id'];
            }
        }
        if ($sameControllerId || $sameModuleId) {
            Ec::$service->getMenu()->getConfig()->clearCache();
            Ec::$service->getMenu()->getConfig()->sync();
            Ec::$service->getExtension()->getEnvironment()->flushConfigFiles(false);
        }
    }
    
}
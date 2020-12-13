<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository\models;

use EngineCore\Ec;
use Yii;

/**
 * This is the model class for table "{{%viMJHk_module}}".
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Module extends BaseExtensionModel implements ModuleModelInterface
{
    
    public static function tableName()
    {
        return '{{%viMJHk_module}}';
    }
    
    public function rules()
    {
        return array_merge(parent::rules(), [
            // module_id rules
            'moduleIdRequired' => ['module_id', 'required'],
            'moduleIdLength'   => ['module_id', 'string', 'max' => 15],
            // uniqueId rules
            'uniqueIdUnique'   => [
                ['unique_name', 'app', 'module_id']
                , 'unique', 'targetAttribute' => ['unique_name', 'app', 'module_id'],
            ],
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'module_id' => Yii::t('ec/extension', 'Module id'),
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
        if (isset($changedAttributes['module_id'])) {
            // 暂不支持多层模块id设置
            if (Yii::$app->controller->module->id == $this->getOldAttribute('module_id')) {
                Yii::$app->controller->module->id = $changedAttributes['module_id'];
            }
            Ec::$service->getMenu()->getConfig()->clearCache();
            Ec::$service->getMenu()->getConfig()->sync();
            Ec::$service->getExtension()->getEnvironment()->flushConfigFiles(false);
        }
    }
    
}
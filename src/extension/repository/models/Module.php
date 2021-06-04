<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

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
                ['unique_name', 'app', 'module_id'], 'unique', 'targetAttribute' => ['unique_name', 'app', 'module_id'],
            ],
            // bootstrap rules
            ['bootstrap', 'integer'],
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'module_id' => Yii::t('ec/modules/extension', 'Module Id'),
            'bootstrap' => Yii::t('ec/modules/extension', 'Bootstrap'),
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'bootstrap' => Yii::t('ec/modules/extension','When bootstrap is enabled, the current module will automatically load the bootstrap program after the application starts.'),
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            // 调用扩展内置安装方法
            $this->getInfoInstance()->install();
        }
        parent::afterSave($insert, $changedAttributes);
        
        if (isset($changedAttributes['module_id'])) {
            // 暂不支持多层模块id设置
            if (Yii::$app->controller->module->id == $changedAttributes['module_id']) {
                Yii::$app->controller->module->id = $this->module_id;
            }
        }
        if ($insert || isset($changedAttributes['status']) || isset($changedAttributes['bootstrap']) || isset($changedAttributes['module_id'])) {
            // 清理缓存
            Ec::$service->getExtension()->getRepository()->clearCache();
            Ec::$service->getMenu()->getConfig()->clearCache();
            Ec::$service->getSystem()->getSetting()->clearCache();
            // 刷新配置
            Ec::$service->getExtension()->getEnvironment()->flushConfigFiles();
        }
    }
    
}
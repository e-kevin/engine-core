<?php
/**
 * @link https://github.com/EngineCore/module-extension
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%viMJHk_controller}}".
 *
 * @property string $module_id
 * @property string $controller_id
 */
class Controller extends BaseExtensionModel
{
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%viMJHk_controller}}';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['controller_id'], 'required'],
            [['module_id', 'controller_id'], 'string', 'max' => 64],
        ]);
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'module_id'     => Yii::t('ec/extension', 'Module Id'),
            'controller_id' => Yii::t('ec/extension', 'Controller Id'),
        ]);
    }
    
    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'module_id' => Yii::t('ec/extension',
                'When empty, the extension is `{app}` application extension.',
                ['app' => Yii::$app->id]
            ),
        ]);
    }
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

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
            'moduleIdLength' => ['module_id', 'string', 'max' => 15],
            // controller_id rules
            'controllerIdRequired' => ['controller_id', 'required'],
            'controllerIdLength' => ['controller_id', 'string', 'max' => 15],
            // uniqueId rules
            'uniqueIdUnique' => [
                ['unique_name', 'app', 'module_id', 'controller_id']
                , 'unique', 'targetAttribute' => ['unique_name', 'app', 'module_id', 'controller_id'],
            ],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'module_id' => Yii::t('ec/extension', 'Module Id'),
            'controller_id' => Yii::t('ec/extension', 'Controller Id'),
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

        if ($insert || isset($changedAttributes['status']) || isset($changedAttributes['module_id']) || isset($changedAttributes['controller_id'])) {
            // 清理缓存
            Ec::$service->getExtension()->getRepository()->clearCache();
            Ec::$service->getMenu()->getConfig()->clearCache();
            Ec::$service->getSystem()->getSetting()->clearCache();
            // 刷新配置
            Ec::$service->getExtension()->getEnvironment()->flushConfigFiles();
        }
    }

}
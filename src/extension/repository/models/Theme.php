<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\models;

use EngineCore\Ec;
use EngineCore\enums\StatusEnum;
use Yii;

/**
 * This is the model class for table "{{%viMJHk_theme}}".
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Theme extends BaseExtensionModel implements ThemeModelInterface
{

    public static function tableName()
    {
        return '{{%viMJHk_theme}}';
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            // theme_id rules
            'themeIdRequired' => ['theme_id', 'required'],
            'themeIdLength' => ['theme_id', 'string', 'max' => 15],
            // uniqueId rules
            'uniqueIdUnique' => [
                ['unique_name', 'app', 'theme_id']
                , 'unique', 'targetAttribute' => ['unique_name', 'app', 'theme_id'],
            ],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'theme_id' => Yii::t('ec/extension', 'Theme Id'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveTheme($app = null)
    {
        return self::find()
            ->select(['unique_name'])
            ->where([
                'status' => StatusEnum::STATUS_ON,
                'app' => $app ?: Yii::$app->id,
            ])->scalar();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllActiveTheme(): array
    {
        return self::find()->where([
            'status' => StatusEnum::STATUS_ON,
        ])->asArray()->indexBy('app')->all();
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert || isset($changedAttributes['status'])) {
            // 清理缓存
            Ec::$service->getExtension()->getRepository()->clearCache();
            Ec::$service->getMenu()->getConfig()->clearCache();
            Ec::$service->getSystem()->getSetting()->clearCache();
            // 刷新配置
            Ec::$service->getExtension()->getEnvironment()->flushConfigFiles();
        }
    }

}
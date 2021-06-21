<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\setting;

use EngineCore\db\ActiveRecord;
use EngineCore\Ec;
use Yii;

/**
 * This is the base setting model class for table "{{%viMJHk_setting}}".
 *
 * 当前模型类满足了最基础的设置数据库表要求，如有其它需要，可继承该类进行个性化配置
 *
 * @property integer $id
 *
 * ====== 默认字段 @see SettingProviderTrait::getDefaultFields()
 * @property string $name 设置键名的数据库字段名
 * @property string $value 设置值的数据库字段名
 * @property string $extra 设置额外数据的数据库字段名
 * @property string $title 设置标题的数据库字段名
 * @property string $description 设置描述的数据库字段名
 * @property string $type 设置类型的数据库字段名
 * @property string $category 设置分组的数据库字段名
 * @property string $rule 验证规则的数据库字段名
 * ====== 默认字段
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class SettingModel extends ActiveRecord implements SettingProviderInterface
{

    use SettingProviderTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%viMJHk_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // name rules
            'nameRequired' => ['name', 'required'],
            'nameLength' => ['name', 'string', 'max' => 30],
            'nameUnique' => ['name', 'unique'],
            // title rules
            'titleRequired' => ['title', 'required'],
            'titleLength' => ['title', 'string', 'max' => 20],
            // extra rules
            'extraLength' => ['extra', 'string', 'max' => 255],
            // description rules
            'descriptionLength' => ['description', 'string', 'max' => 255],
            // rule rules
            'ruleRequired' => ['rule', 'required'],
            'ruleLength' => ['rule', 'string', 'max' => 500],
            // other rules
            'otherString' => ['value', 'string'],
            'otherInteger' => [['type', 'category'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('ec/setting', 'Name'),
            'title' => Yii::t('ec/setting', 'Title'),
            'extra' => Yii::t('ec/setting', 'Extra'),
            'description' => Yii::t('ec/setting', 'Description'),
            'value' => Yii::t('ec/setting', 'Value'),
            'type' => Yii::t('ec/setting', 'Type'),
            'category' => Yii::t('ec/setting', 'Category'),
            'rule' => Yii::t('ec/setting', 'Rule'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeHints()
    {
        return [
            'name' => Yii::t('ec/setting', 'Only English can be used and cannot be repeated.'),
            'title' => Yii::t('ec/setting', 'Configuration Title for Background Display.'),
            'value' => Yii::t('ec/setting', 'Value'),
            'description' => Yii::t('ec/setting', 'Configuration details.'),
            'extra' => Yii::t('ec/setting', 'This item needs to be configured for the type of select, radio and checkbox.'),
            'type' => Yii::t('ec/setting', 'The system will analyze the configuration data according to different types.'),
            'category' => Yii::t('ec/setting', 'Settings without grouping will not appear in system settings.'),
            'rule' => Yii::t('ec/setting', 'Set validation rules. Many rules are signed in english ; or newline separation.'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAll(): array
    {
        return Ec::$service->getSystem()->getCache()->getOrSet(self::SETTING_KEY, function () {
            return self::find()->indexBy('name')->asArray()->all();
        }, $this->getCacheDuration());
    }

    /**
     * @inheritdoc
     */
    public function clearCache()
    {
        Ec::$service->getSystem()->getCache()->getComponent()->delete(self::SETTING_KEY);
    }

}
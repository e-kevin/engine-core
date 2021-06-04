<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
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
            'nameRequired'      => [$this->getNameField(), 'required'],
            'nameLength'        => [$this->getNameField(), 'string', 'max' => 30],
            'nameUnique'        => [$this->getNameField(), 'unique'],
            // title rules
            'titleRequired'     => [$this->getTitleField(), 'required'],
            'titleLength'       => [$this->getTitleField(), 'string', 'max' => 20],
            // extra rules
            'extraLength'       => [$this->getExtraField(), 'string', 'max' => 255],
            // description rules
            'descriptionLength' => [$this->getDescriptionField(), 'string', 'max' => 255],
            // other rules
            'otherString'       => [$this->getValueField(), 'string'],
            'otherInteger'      => [[$this->getTypeField(), $this->getCategoryField()], 'integer'],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                         => 'ID',
            $this->getNameField()        => Yii::t('ec/setting', 'Name'),
            $this->getTitleField()       => Yii::t('ec/setting', 'Title'),
            $this->getExtraField()       => Yii::t('ec/setting', 'Extra'),
            $this->getDescriptionField() => Yii::t('ec/setting', 'Description'),
            $this->getValueField()       => Yii::t('ec/setting', 'Value'),
            $this->getTypeField()        => Yii::t('ec/setting', 'Type'),
            $this->getCategoryField()    => Yii::t('ec/setting', 'Category'),
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeHints()
    {
        return [
            $this->getNameField()        => Yii::t('ec/setting', 'Only English can be used and cannot be repeated.'),
            $this->getTitleField()       => Yii::t('ec/setting', 'Configuration Title for Background Display.'),
            $this->getValueField()       => Yii::t('ec/setting', 'Value'),
            $this->getDescriptionField() => Yii::t('ec/setting', 'Configuration details.'),
            $this->getExtraField()       => Yii::t('ec/setting', 'This item needs to be configured for the type of select, radio and checkbox.'),
            $this->getTypeField()        => Yii::t('ec/setting', 'The system will analyze the configuration data according to different types.'),
            $this->getCategoryField()    => Yii::t('ec/setting', 'Settings without grouping will not appear in system settings.'),
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function getAll(): array
    {
        return Ec::$service->getSystem()->getCache()->getOrSet(self::SETTING_KEY, function () {
            return self::find()->select($this->getFieldMap())->indexBy($this->getNameField())->asArray()->all();
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
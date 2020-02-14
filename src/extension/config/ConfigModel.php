<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\models;

use EngineCore\db\ActiveRecord;
use EngineCore\extension\config\ConfigProviderInterface;
use Yii;

/**
 * This is the base config model class for table.
 *
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property string $title
 * @property string $category_group
 * @property string $extra
 * @property string $remark
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 * @property string $value
 * @property integer $sort_order
 * @property string $rule
 */
class ConfigModel extends ActiveRecord implements ConfigProviderInterface
{
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'title', 'rule'], 'required'],
            [['type', 'category_group', 'created_at', 'updated_at', 'status', 'sort_order'], 'integer'],
            [['value', 'rule'], 'string'],
            [['name'], 'string', 'max' => 30],
            [['title'], 'string', 'max' => 50],
            [['extra'], 'string', 'max' => 255],
            [['remark'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('Ec/config', 'Name'),
            'type' => Yii::t('Ec/config', 'Type'),
            'title' => Yii::t('Ec/config', 'Title'),
            'category_group' => Yii::t('Ec/config', 'Category Group'),
            'extra' => Yii::t('Ec/config', 'Extra'),
            'remark' => Yii::t('Ec/config', 'Remark'),
            'created_at' => Yii::t('Ec/app', 'Created At'),
            'updated_at' => Yii::t('Ec/app', 'Updated At'),
            'status' => Yii::t('Ec/app', 'Status'),
            'value' => Yii::t('Ec/config', 'Value'),
            'sort_order' => Yii::t('Ec/config', 'Sort Order'),
            'rule' => Yii::t('Ec/config', 'Rule'),
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'name' => Yii::t('Ec/config', 'Only English can be used and cannot be repeated.'),
            'title' => Yii::t('Ec/config', 'Configuration Title for Background Display.'),
            'sort_order' => Yii::t('Ec/config', 'The order used for grouping display.'),
            'type' => Yii::t('Ec/config', 'The system will analyze the configuration data according to different types.'),
            'category_group' => Yii::t('Ec/config', 'No grouping will be displayed in the system settings.'),
            'value' => Yii::t('Ec/config', 'Value'),
            'remark' => Yii::t('Ec/config', 'Configuration details.'),
            'rule' => '配置验证规则</br>多条规则用英文符号 ; 或换行分隔，如：</br>分号 ;</br>required; string,max:10,min:4; string,length:1-3' .
                '</br>换行</br>required</br>string,max:10,min:4</br>string,length:1-3',
            'extra' => '【下拉框、单选框、多选框】类型需要配置该项</br>多个可用英文符号 , ; 或换行分隔，如：</br>逗号 ,</br>key:value, key1:value1, key2:value2' .
                '</br>分号 ;</br>key:value; key1:value1; key2:value</br>换行</br>key:value</br>key1:value1</br>key2:value',
        ];
    }
    
}

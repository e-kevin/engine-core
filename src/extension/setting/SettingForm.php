<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\setting;

use EngineCore\base\Model;
use EngineCore\Ec;
use EngineCore\enums\EnableEnum;
use EngineCore\traits\ParseRulesTrait;
use yii\web\NotFoundHttpException;

/**
 * 设置虚拟表单模型
 */
class SettingForm extends Model
{

    use ParseRulesTrait;

    /**
     * @var string 设置分组
     * @see SettingProviderInterface
     */
    public $category;

    /**
     * @var SettingModel[] 设置模型
     */
    public $models;

    /**
     * @var array 虚拟属性
     */
    private $_property;

    /**
     * @var array 验证规则
     */
    private $_rules = [];

    /**
     * @var array 属性标签名
     */
    private $_labels;

    /**
     * @var array 字段提示信息
     */
    private $_hints;

    /**
     * @inheritdoc
     */
    public function __construct($category, array $config = [])
    {
        $this->category = $category;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        /** @var SettingModel $provider */
        $provider = Ec::$service->getSystem()->getSetting()->getProvider();
        $this->models = $provider::find()->select(array_merge($provider->fields(), $provider->getFieldMap()))
            ->where([
                'status' => EnableEnum::ENABLE,
                'category' => $this->category
            ])
            ->indexBy('name')->orderBy('order')->all();
        if (is_null($this->models)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $this->initProperty();
    }

    /**
     * 初始化模型类内部属性值
     */
    protected function initProperty()
    {
        $fieldMap = Ec::$service->getSystem()->getSetting()->getProvider()->getFieldMap();
        foreach ($this->models as $model) {
            // 虚拟属性
            $this->_property[$model->{$fieldMap['name']}] =
                ($model->{$fieldMap['type']} == SettingModel::TYPE_CHECKBOX && $model->{$fieldMap['value']} !== '')
                    ? explode(',', $model->{$fieldMap['value']})
                    : $model->{$fieldMap['value']};
            // 虚拟标签
            $this->_labels[$model->{$fieldMap['name']}] = $model->{$fieldMap['title']};
            // 虚拟提示
            $this->_hints[$model->{$fieldMap['name']}] = nl2br($model->{$fieldMap['description']});
            if (!empty($model->{$fieldMap['rule']})) {
                $val = $this->parseRulesToArray($model->{$fieldMap['rule']}, $model->{$fieldMap['name']});
                if (!empty($val)) {
                    $this->_rules = array_merge($this->_rules, $val);
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_keys($this->_property);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return $this->_labels;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return $this->_rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return $this->_hints;
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        return $this->_property[$name] ?? parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        if (isset($this->_property[$name])) {
            $this->_property[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * 格式化保存进数据库的设置数据
     *
     * @param SettingModel $model
     *
     * @return string
     */
    protected function formatSettingValue($model): string
    {
        $fieldMap = Ec::$service->getSystem()->getSetting()->getProvider()->getFieldMap();

        return is_array($this->_property[$model->{$fieldMap['name']}])
            ? implode(',', $this->_property[$model->{$fieldMap['name']}])
            : $this->_property[$model->{$fieldMap['name']}];
    }

    /**
     * 保存模型更改
     *
     * @return boolean
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $fieldMap = Ec::$service->getSystem()->getSetting()->getProvider()->getFieldMap();
        foreach ($this->models as $model) {
            $model->{$fieldMap['value']} = $this->formatSettingValue($model);
            if ($model->save(true, ['value', 'updated_at'])) {
                continue;
            } else {
                return false;
            }
        }

        return true;
    }

}
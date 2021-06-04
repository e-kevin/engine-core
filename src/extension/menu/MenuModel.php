<?php
/**
 * @link      https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

namespace EngineCore\extension\menu;

use EngineCore\db\ActiveRecord;
use EngineCore\Ec;
use EngineCore\web\behaviors\TreeBehavior;
use Yii;

/**
 * This is the base menu model class for table "{{%viMJHk_menu}}".
 *
 * 当前模型类满足了最基础的菜单数据库表要求，如有其它需要，可继承该类进行个性化配置
 *
 * @property integer $id
 * @property integer $parent_id
 *
 * 行为方法属性
 * @method TreeBehavior getChildrenIds()
 * @method TreeBehavior getParentIds($rootId = 0)
 * @method TreeBehavior getTreeSelectList(array $list)
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class MenuModel extends ActiveRecord implements MenuProviderInterface
{
    
    use MenuProviderTrait;
    
    /**
     * 缓存所有菜单项
     */
    const CACHE_ALL_MENU = 'allMenus';
    
    const SCENARIO_UPDATE = 'update';
    
    const SCENARIO_CREATE = 'create';
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%viMJHk_menu}}';
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = [
            'class'          => TreeBehavior::class,
            'showTitleField' => $this->getAliasField(),
        ];
        
        return $behaviors;
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // parent_id rules
            'parentRequired'    => ['parent_id', 'required'],
            'parentType'        => ['parent_id', 'integer'],
            // label rules
            'labelRequired'     => [$this->getLabelField(), 'required'],
            'labelLength'       => [$this->getLabelField(), 'string', 'max' => 64],
            // alias rules
            'aliasDefault'      => [$this->getAliasField(), 'default', 'value' => function ($model, $attribute) {
                return $model->$attribute ?: $model->{$this->getLabelField()};
            }],
            'aliasLength'       => [$this->getAliasField(), 'string', 'max' => 64],
            // icon rules
            'iconLength'        => [$this->getIconField(), 'string', 'max' => 30],
            // url rules
            'urlRequired'       => [$this->getUrlField(), 'required'],
            'urlLength'         => [$this->getUrlField(), 'string', 'max' => 512],
            // url params rules
            'paramsLength'      => [$this->getParamsField(), 'string', 'max' => 200],
            // category rules
            'categoryRequired'  => [$this->getCategoryField(), 'required'],
            'categoryLength'    => [$this->getCategoryField(), 'string', 'max' => 64],
            // description rules
            'descriptionLength' => [$this->getDescriptionField(), 'string', 'max' => 512],
            // config rules
            'configLength'      => [$this->getConfigField(), 'string', 'max' => 200],
            'configJSON'        => [$this->getConfigField(), function ($attribute, $params) {
                if (null === json_decode($this->$attribute)) {
                    $this->addError($attribute, Yii::t('wocenter/modules/menu', 'Non-JSON format data'));
                }
            }],
            // theme rules
            'themeLength'       => [$this->getThemeField(), 'string', 'max' => 20],
            // status rules
            'statusType'        => [$this->getStatusField(), 'integer'],
            // created by rules
            'createdByDefault'  => [$this->getCreatedByField(), 'default', 'value' => MenuProviderInterface::CREATED_BY_USER, 'on' => self::SCENARIO_CREATE],
            'createdByType'     => [$this->getCreatedByField(), 'integer'],
            // other rules
            'otherInteger'      => [[$this->getOrderField(), $this->getVisibleField()], 'integer'],
            
            
            [['is_dev'], 'integer'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = [
            self::SCENARIO_DEFAULT => [
                'parent_id', 'is_dev', $this->getCategoryField(), $this->getLabelField(), $this->getAliasField(),
                $this->getUrlField(), $this->getParamsField(), $this->getConfigField(), $this->getStatusField(),
                $this->getOrderField(), $this->getDescriptionField(), $this->getIconField(), $this->getVisibleField(),
                $this->getThemeField(),
            ],
            self::SCENARIO_UPDATE  => [
                'parent_id', 'is_dev', $this->getLabelField(), $this->getAliasField(), $this->getStatusField(),
                $this->getOrderField(), $this->getDescriptionField(), $this->getIconField(),
                $this->getVisibleField(), $this->getConfigField(),
            ],
            self::SCENARIO_CREATE  => [
                'parent_id', 'is_dev', $this->getLabelField(), $this->getAliasField(), $this->getStatusField(),
                $this->getOrderField(), $this->getDescriptionField(), $this->getIconField(),
                $this->getVisibleField(), $this->getUrlField(), $this->getParamsField(), '!' . $this->getCategoryField(),
                $this->getThemeField(),
            ],
        ];
        // 用户自建的菜单可以修改以下字段值
        if ($this->{$this->getCreatedByField()} === MenuProviderInterface::CREATED_BY_USER) {
            $scenarios[self::SCENARIO_UPDATE] = array_merge($scenarios[self::SCENARIO_UPDATE], [
                $this->getUrlField(), $this->getParamsField(), $this->getThemeField(),
            ]);
        }
        
        return $scenarios;
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                         => 'ID',
            'parent_id'                  => Yii::t('wocenter/modules/menu', 'Parent Id'),
            $this->getCategoryField()    => Yii::t('wocenter/modules/menu', 'Category Id'),
            $this->getLabelField()       => Yii::t('wocenter/modules/menu', 'Label'),
            $this->getAliasField()       => Yii::t('wocenter/modules/menu', 'Alias Name'),
            $this->getUrlField()         => Yii::t('wocenter/app', 'Url'),
            $this->getParamsField()      => Yii::t('wocenter/app', 'Url Params'),
            $this->getConfigField()      => Yii::t('wocenter/modules/menu', 'Menu Config'),
            $this->getDescriptionField() => Yii::t('wocenter/app', 'Description'),
            $this->getIconField()        => Yii::t('wocenter/app', 'Icon'),
            $this->getOrderField()       => Yii::t('wocenter/app', 'Sort Order'),
            $this->getVisibleField()     => Yii::t('wocenter/modules/menu', 'Show On Menu'),
            $this->getThemeField()       => Yii::t('wocenter/modules/menu', 'Theme'),
            $this->getStatusField()      => Yii::t('wocenter/app', 'Status'),
            $this->getCreatedByField()   => Yii::t('wocenter/modules/menu', 'Created Type'),
            'createdTypeValue'           => Yii::t('wocenter/modules/menu', 'Created Type'),
            'is_dev'                     => Yii::t('wocenter/modules/menu', 'Is Develop'),
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            $this->getAliasField()  => Yii::t('wocenter/modules/menu',
                'When blank, the default value is `{label}`, which is used for menu display',
                ['label' => $this->getAttributeLabel($this->getLabelField())]
            ),
            $this->getConfigField() => Yii::t('wocenter/modules/menu', 'Use json format, such as: {"linkOptions":{"data-method":"post","data-pjax":1}}'),
            $this->getThemeField()  => Yii::t('wocenter/modules/menu',
                'The default \'common\' theme name represents that the current menu is displayed under all themes, otherwise it will only be displayed when the theme takes effect.'),
        ];
    }
    
    /**
     * 获取数据库所有菜单数据
     *
     * @return mixed
     */
    public function getAll(): array
    {
        return Ec::$service->getSystem()->getCache()->getOrSet(
            self::CACHE_ALL_MENU,
            function () {
                $arr = self::find()->select(array_merge($this->getFieldMap(), [
                    'id'        => 'id',
                    'parent_id' => 'parent_id',
                ]))->orderBy([$this->getOrderField() => SORT_ASC])->asArray()->all();
                $list = [];
                // 特定参数处理并以主键为索引
                foreach ($arr as $key => $row) {
                    foreach ($row as $k => $value) {
                        // 特殊字段处理
                        if ($k == $this->getConfigField()) {
                            $arr[$key][$k] = $value ? json_decode($value, true) : [];
                        }
                        if ($k == $this->getParamsField()) {
                            if ($value) {
                                parse_str($value, $value);
                            } else {
                                $value = [];
                            }
                            $arr[$key][$k] = $value;
                        }
                    }
                    $list[$row['id']] = $arr[$key];
                }
                unset($arr);
                // 添加菜单层级数
                foreach ($list as $key => $row) {
                    $hasParent = $row['parent_id'];
                    $level = 1;
                    while ($hasParent) {
                        $hasParent = isset($list[$hasParent]) ? $list[$hasParent]['parent_id'] : false;
                        $level += 1;
                    }
                    $list[$key]['level'] = $level;
                }
                
                return $list;
            }, $this->getCacheDuration());
    }
    
    /**
     * 获取创建方式值
     *
     * @return string
     */
    public function getCreatedByValue()
    {
        return Ec::$service->getMenu()->getConfig()->getCreatedByList()[$this->getCreatedByField()];
    }
    
    /**
     * @inheritdoc
     */
    public function clearCache()
    {
        Ec::$service->getSystem()->getCache()->getComponent()->delete(self::CACHE_ALL_MENU);
    }
    
}
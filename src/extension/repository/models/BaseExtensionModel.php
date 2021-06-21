<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\models;

use EngineCore\db\ActiveRecord;
use EngineCore\Ec;
use EngineCore\extension\repository\info\ConfigInfo;
use EngineCore\extension\repository\info\ControllerInfo;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\extension\repository\info\ModularityInfo;
use EngineCore\extension\repository\info\ThemeInfo;
use EngineCore\helpers\ArrayHelper;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;

/**
 * 扩展通用模型类，一般主题扩展、模块扩展、控制器扩展等模型需要继承该类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class BaseExtensionModel extends ActiveRecord implements RepositoryModelInterface
{

    /**
     * @var ModularityInfo|ControllerInfo|ThemeInfo|ConfigInfo 扩展信息类
     */
    private $_infoInstance;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // unique_id rules
            'uniqueIdRequired' => ['unique_id', 'required'],
            'uniqueIdLength' => ['unique_id', 'string', 'max' => 32],
            // unique_name rules
            'uniqueNameRequired' => ['unique_name', 'required'],
            'uniqueNameLength' => ['unique_name', 'string', 'max' => 50],
            // app rules
            'appRequired' => ['app', 'required'],
            'appLength' => ['app', 'string', 'max' => 10],
            // version rules
            'versionRequired' => ['version', 'required'],
            'versionLength' => ['version', 'string', 'max' => 30],
            // category rules
            'categoryRequired' => ['category', 'required'],
            'categoryType' => ['category', 'integer'],
            // other rules
            ['is_system', 'integer'],
            ['status', 'integer'],
            ['run', 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => 'created_at',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'unique_id' => 'ID',
            'unique_name' => Yii::t('ec/extension', 'Unique Name'),
            'app' => Yii::t('ec/extension', 'App'),
            'is_system' => Yii::t('ec/extension', 'Is System'),
            'status' => Yii::t('ec/extension', 'Status'),
            'run' => Yii::t('ec/extension', 'Run Mode'),
            'version' => Yii::t('ec/extension', 'Version'),
            'category' => Yii::t('ec/extension', 'Category'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeHints()
    {
        return [
            'is_system' => Yii::t('ec/extension', 'The system extension cannot be uninstall.'),
            'run' => Yii::t('ec/extension', 'Select which extension configuration to run the current extension.'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        return self::find()->asArray()->orderBy('created_at')->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getInfoInstance()
    {
        return $this->_infoInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function setInfoInstance(ExtensionInfo $info)
    {
        $this->_infoInstance = $info;
    }

    /**
     * {@inheritdoc}
     */
    public function hasInfoInstance(): bool
    {
        return null !== $this->_infoInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUniqueName($uniqueName, $app = null)
    {
        return self::find()->where([
            'unique_name' => $uniqueName,
            'app' => $app ?: Yii::$app->id,
        ])->one();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        // 检查是否满足扩展依赖关系
        if (!Ec::$service->getExtension()->getDependent()->checkDependencies($this->unique_name, $this->app, true)) {
            $this->getErrorService()->addModelOtherErrors(
                Ec::$service->getExtension()->getDependent()->getInfo(),
                get_called_class(),
                __METHOD__
            );

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigException
     */
    public function beforeSave($insert)
    {
        if (!$this->hasInfoInstance()) {
            throw new InvalidConfigException('The `$infoInstance` property must be set.');
        }
        if (!parent::beforeSave($insert)) {
            return false;
        }
        // 调用扩展内置安装方法
        if ($insert && !$this->getInfoInstance()->install()) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigException
     */
    public function beforeDelete()
    {
        if (!$this->hasInfoInstance()) {
            throw new InvalidConfigException('The `$infoInstance` property must be set.');
        }
        if (!parent::beforeDelete()) {
            return false;
        }
        if ($this->is_system) {
            $this->getErrorService()->addModelOtherErrors($this->unique_name . ': '
                . Yii::t('ec/extension',
                    'The extension is a system extension and uninstall is not supported for the time being.'
                ), get_called_class(), __METHOD__);

            return false;
        } else {
            // 检测当前扩展是否被其他已安装的扩展依赖
            $arr = [];
            $i = 1;
            foreach (Ec::$service->getExtension()->getRepository()->getDbConfiguration() as $app => $row) {
                foreach ($row as $uniqueName => $config) {
                    if ($uniqueName == $this->unique_name) {
                        continue;
                    }
                    $dependencies = ArrayHelper::getValue(
                        Ec::$service->getExtension()->getDependent()->getDefinitions()[$uniqueName],
                        'extensionDependencies',
                        []
                    );
                    foreach ($dependencies as $a => $v) {
                        if (isset($v[$this->unique_name])) {
                            $arr[] = $i++ . ') ' . $uniqueName . ": $a";
                        }
                    }
                }
            }

            if ($arr) {
                $this->getErrorService()->addModelOtherErrors(Yii::t('ec/extension',
                    'Please remove the following extended dependencies before performing the current operation:{operation}',
                    ['operation' => implode("<br/>", $arr)]
                ), get_called_class(), __METHOD__);

                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();
        // 调用扩展内置卸载方法
        $this->getInfoInstance()->uninstall();
        // 清理缓存
        Ec::$service->getExtension()->getRepository()->clearCache();
        Ec::$service->getMenu()->getConfig()->clearCache();
        Ec::$service->getSystem()->getSetting()->clearCache();
        // 刷新配置
        Ec::$service->getExtension()->getEnvironment()->flushConfigFiles();
        // 延迟，基本可确保以上操作已完成
        sleep(1);
    }

}
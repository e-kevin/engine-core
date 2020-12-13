<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\extension\repository\models;

use EngineCore\db\ActiveRecord;
use EngineCore\Ec;
use EngineCore\extension\repository\info\ControllerInfo;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\extension\repository\info\ModularityInfo;
use EngineCore\extension\repository\info\ThemeInfo;
use Yii;

/**
 * 扩展通用模型类，一般主题扩展、模块扩展、控制器扩展模型需要继承该类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class BaseExtensionModel extends ActiveRecord implements RepositoryModelInterface
{
    
    /**
     * @var ModularityInfo|ControllerInfo|ThemeInfo 扩展信息类
     */
    private $_infoInstance;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // unique_id rules
            'uniqueIdRequired'   => ['unique_id', 'required'],
            'uniqueIdLength'     => ['unique_id', 'string', 'max' => 32],
            // unique_name rules
            'uniqueNameRequired' => ['unique_name', 'required'],
            'uniqueNameLength'   => ['unique_name', 'string', 'max' => 50],
            // app rules
            'appRequired'        => ['app', 'required'],
            'appLength'          => ['app', 'string', 'max' => 10],
            // other rules
            ['is_system', 'integer'],
            ['status', 'integer'],
            ['run', 'integer'],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'unique_id'          => 'ID',
            'unique_name' => Yii::t('ec/extension', 'Unique name'),
            'app'         => Yii::t('ec/extension', 'App'),
            'is_system'   => Yii::t('ec/extension', 'Is system'),
            'status'      => Yii::t('ec/app', 'Status'),
            'run'         => Yii::t('ec/extension', 'Run mode'),
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeHints()
    {
        return [
            'is_system' => Yii::t('ec/extension', 'Cannot uninstall after installation.'),
            'run'       => Yii::t('ec/extension', 'Select which extension configuration to run the current extension.'),
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        return self::find()->asArray()->all();
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
            'app'         => $app ?: Yii::$app->id,
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
        if (!Ec::$service->getExtension()->getDependent()->checkDependencies($this->unique_name, $this->app)) {
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
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($insert && !$this->hasInfoInstance()) {
            $this->getErrorService()->addModelOtherErrors(
                'The `infoInstance` property must be set.', get_called_class(), __METHOD__);
            
            return false;
        }
        
        return true;
    }
    
    /**
     * {@inheritdoc}
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        if (!$this->hasInfoInstance()) {
            $this->getErrorService()->addModelOtherErrors(
                'The `infoInstance` property must be set.', get_called_class(), __METHOD__);
            
            return false;
        }
        if (!$this->getCanUninstall()) {
            $this->getErrorService()->addModelOtherErrors($this->unique_name
                . Yii::t('ec/extension',
                    'The extension is a system extension and uninstallation is not supported for the time being.'
                ), get_called_class(), __METHOD__);
            
            return false;
        } else {
            $arr = [];
            $i = 1;
            // 获取已经安装的扩展，检测当前扩展是否存在依赖关系
            foreach (Ec::$service->getExtension()->getRepository()->getDbConfiguration() as $uniqueName => $row) {
                if ($uniqueName == $this->unique_name) {
                    continue;
                }
                /** @var ExtensionInfo $infoInstance */
                $infoInstance = Ec::$service->getExtension()->getRepository()->getLocalConfiguration()[$uniqueName]['infoInstance'];
                // 获取依赖关系
                foreach ($infoInstance->getDependencies() as $name => $version) {
                    if ($name == $this->unique_name) {
                        $arr[] = $i++ . ') ' . $uniqueName;
                    }
                }
            }
            
            if ($arr) {
                $this->getErrorService()->addModelOtherErrors(Yii::t('ec/extension',
                    'Please remove the following extended dependencies before performing the current operation:{operation}',
                    ['operation' => implode("\n", $arr)]
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
        $this->getInfoInstance()->uninstall(); // 调用扩展内置卸载方法
        Ec::$service->getMenu()->getConfig()->sync(); // 同步菜单
        Ec::$service->getExtension()->getEnvironment()->flushConfigFiles(false); // 刷新配置
    }
    
    /**
     * 获取扩展是否可以卸载
     *
     * @return bool
     */
    public function getCanUninstall(): bool
    {
        if ($this->getIsNewRecord()) {
            return false;
        } else {
            return !$this->is_system;
        }
    }
    
    /**
     * 获取扩展是否可以安装
     *
     * @return bool
     */
    public function getCanInstall(): bool
    {
        return $this->getIsNewRecord();
    }
    
}
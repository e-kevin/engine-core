<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

use EngineCore\db\Migration;
use EngineCore\Ec;
use EngineCore\extension\setting\FileProvider;
use EngineCore\extension\setting\SettingProviderInterface;

class m200822_023219_create_setting_table extends Migration
{

    public function safeUp()
    {
        $this->createTable($this->createTableNameWithCode('setting'), [
            'id' => $this->primaryKey()->unsigned()->comment('ID'),
            'name' => $this->string(30)->unique()->notNull()->comment(Yii::t('ec/setting', 'Name')),
            'title' => $this->string(20)->notNull()->comment(Yii::t('ec/setting', 'Title')),
            'extra' => $this->string(255)->notNull()->defaultValue('')->comment(Yii::t('ec/setting', 'Extra')),
            'description' => $this->string(255)->notNull()->defaultValue('')->comment(Yii::t('ec/setting', 'Description')),
            'value' => $this->text()->notNull()->comment(Yii::t('ec/setting', 'Value')),
            'type' => $this->boolean()->unsigned()->notNull()
                ->defaultValue(SettingProviderInterface::TYPE_STRING)->comment(Yii::t('ec/setting', 'Type')),
            'category' => $this->boolean()->unsigned()->notNull()
                ->defaultValue(SettingProviderInterface::CATEGORY_NONE)->comment(Yii::t('ec/setting', 'Category')),
            'rule' => $this->string(500)->notNull()->defaultValue('required')->comment('验证规则'),
        ], $this->tableOptions . $this->buildTableComment('系统设置表'));

        // 转存数据
        $this->transferToDb();
    }

    public function safeDown()
    {
        // 转存数据
        if ($this->transferToFile()) {
            // 清除旧的缓存数据
            Ec::$service->getSystem()->getSetting()->clearCache();
            // 数据转存成功后，临时调用文件方式的数据提供器
            Ec::$service->getSystem()->getSetting()->setProvider(FileProvider::class);
            $this->dropTable($this->createTableNameWithCode('setting'));
        } else {
            throw new \Exception('Failed to transfer configuration file.');
        }
    }

    /**
     * 转存数据到数据库
     */
    protected function transferToDb()
    {
        $provider = Ec::$service->getSystem()->getSetting()->getProvider();
        $data = [];
        $fields = $provider->getDefaultFields();
        foreach ($provider->getAll() as $row) {
            $arr = [];
            foreach ($row as $field => $value) {
                // 只转存默认字段的数值
                if (in_array($field, $fields)) {
                    $arr[$field] = $value;
                }
            }
            $data[] = $arr;
        }

        $this->batchInsert($this->createTableNameWithCode('setting'), array_keys($data[0]), $data);
    }

    /**
     * 转存数据到文件
     *
     * @return bool
     */
    protected function transferToFile(): bool
    {
        $config = [];
        $fields = Ec::$service->getSystem()->getSetting()->getProvider()->getDefaultFields();
        $settings = Yii::getAlias(Ec::$service->getExtension()->getEnvironment()->settingFile);
        $settings = require("$settings");
        foreach (Ec::$service->getSystem()->getSetting()->getAll() as $key => $row) {
            $arr = [];
            foreach ($row as $field => $value) {
                // 只转存默认字段的数值
                if (in_array($field, $fields)) {
                    // 只储存和扩展设置不一样的数据
                    if (isset($settings[$key][$field])) {
                        if ($settings[$key][$field] != $value) {
                            $arr[$field] = $value;
                        }
                    } else {
                        $arr[$field] = $value;
                    }
                }
            }
            if (!empty($arr)) {
                $config[$key] = $arr;
            }
        }

        return Ec::$service->getExtension()->getEnvironment()->flushUserSettingFile($config);
    }

}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\services\extension;

use EngineCore\base\Service;
use EngineCore\db\ActiveRecord;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\extension\repository\models\RepositoryModelInterface;
use EngineCore\helpers\ArrayHelper;
use EngineCore\services\Extension;
use Yii;
use yii\base\InvalidConfigException;

/**
 * 分类扩展仓库抽象类
 *
 * @property ActiveRecord|RepositoryModelInterface $model
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
abstract class BaseCategoryRepository extends Service implements RepositoryInterface
{
    
    /**
     * @var Extension 父级服务类
     */
    public $service;
    
    /**
     * @var string 分类扩展仓库所对应的扩展信息类
     */
    protected $extensionInfo;
    
    protected $mustBeSetProps = ['extensionInfo'];
    
    private $_localConfiguration;
    
    /**
     * {@inheritdoc}
     */
    public function getLocalConfiguration(): array
    {
        if (null === $this->_localConfiguration) {
            $this->_localConfiguration = [];
            foreach ($this->service->getRepository()->getLocalConfiguration() as $app => $row) {
                foreach ($row as $uniqueName => $info) {
                    if (is_subclass_of($info, $this->extensionInfo)) {
                        $this->_localConfiguration[$app][$uniqueName] = $info;
                    }
                }
            }
        }
        
        return $this->_localConfiguration;
    }
    
    private $_dbConfiguration;
    
    /**
     * {@inheritdoc}
     */
    public function getDbConfiguration(): array
    {
        if (null == $this->_dbConfiguration) {
            $this->_dbConfiguration = $this->hasModel()
                ? ArrayHelper::index($this->getModel()->getAll(), null, ['app', 'unique_name'])
                : [];
        }
        
        return $this->_dbConfiguration;
    }
    
    private $_installedConfiguration;
    
    /**
     * {@inheritdoc}
     */
    public function getInstalledConfiguration(): array
    {
        if (null === $this->_installedConfiguration) {
            $this->_installedConfiguration = [];
            foreach ($this->getDbConfiguration() as $app => $row) {
                foreach ($row as $uniqueName => $v) {
                    if (isset($this->getLocalConfiguration()[$app][$uniqueName])) {
                        $this->_installedConfiguration[$app][$uniqueName] = $this->getLocalConfiguration()[$app][$uniqueName];
                    }
                }
            }
        }
        
        return $this->_installedConfiguration;
    }
    
    /**
     * 配置扩展信息类的属性，一般用于同步数据库里的数据到信息类里
     *
     * @param ExtensionInfo $info 扩展信息类
     * @param array         $config 数据库里的配置数据
     */
    abstract public function configureInfo($info, $config = []);
    
    /**
     * {@inheritdoc}
     */
    public function getConfigurationByApp($installed = false, $app = null)
    {
        $configuration = $installed ? $this->getInstalledConfiguration() : $this->getLocalConfiguration();
        $app = $app ?: Yii::$app->id;
        
        return $configuration[$app] ?? [];
    }
    
    /**
     * {@inheritdoc}
     */
    public function clearCache()
    {
        $this->_installedConfiguration = $this->_dbConfiguration = $this->_localConfiguration = null;
    }
    
    /**
     * 获取扩展模型
     *
     * @return RepositoryModelInterface|\EngineCore\db\ActiveRecord
     * @throws InvalidConfigException
     */
    abstract public function getModel();
    
    /**
     * 设置扩展模型
     *
     * @param null|string|array $config
     */
    abstract public function setModel($config = []);
    
    /**
     * 判断是否已经设置了扩展模型类
     *
     * @return bool
     */
    abstract public function hasModel(): bool;
    
    /**
     * 获取指定应用指定扩展名的扩展数据库模型数据
     *
     * @param string $uniqueName
     * @param string $app
     *
     * @return RepositoryModelInterface|null|\EngineCore\db\ActiveRecord
     */
    abstract public function findOne(string $uniqueName, $app = null);
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\configuration;

use yii\base\BaseObject;

/**
 * 扩展配置作者信息类
 *
 * @property string      $name
 * @property string|null $email
 * @property string|null $homepage
 * @property string|null $role
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Author extends BaseObject
{
    
    /**
     * Author constructor.
     *
     * @param string $name
     * @param null   $email
     * @param null   $homepage
     * @param null   $role
     * @param array  $config
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct($name, $email = null, $homepage = null, $role = null, $config = [])
    {
        $this->_name = $name;
        $this->_email = $email;
        $this->_homepage = $homepage;
        $this->_role = $role;
        
        parent::__construct($config);
    }
    
    /**
     * 作者名
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->_name;
    }
    
    /**
     * 作者邮箱
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->_email;
    }
    
    /**
     * 作者网站，作者主页的 URL 地址。
     *
     * @return string|null
     */
    public function getHomepage()
    {
        return $this->_homepage;
    }
    
    /**
     * 作者角色，该作者在此项目中担任的角色（例：开发人员 或 翻译）。
     *
     * @return string|null
     */
    public function getRole()
    {
        return $this->_role;
    }
    
    private
        $_name,
        $_email,
        $_homepage,
        $_role;
    
}
<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\extension\repository\configuration;

use yii\base\BaseObject;

/**
 * 扩展配置支持信息类
 *
 * @property string $email
 * @property string $issues
 * @property string $forum
 * @property string $wiki
 * @property string $source
 * @property string $irc
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class SupportInformation extends BaseObject
{
    
    /**
     * SupportInformation constructor.
     *
     * @param string $email
     * @param string $issues
     * @param string $forum
     * @param string $wiki
     * @param string $source
     * @param string $irc
     * @param array  $config
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct(
        $email = '',
        $issues = '',
        $forum = '',
        $wiki = '',
        $source = '',
        $irc = '',
        $config = []
    ) {
        $this->_email = $email;
        $this->_issues = $issues;
        $this->_forum = $forum;
        $this->_wiki = $wiki;
        $this->_source = $source;
        $this->_irc = $irc;
        
        parent::__construct($config);
    }
    
    /**
     * 获取支持电子邮件地址
     *
     * @return string|null
     */
    public function getEmail(): string
    {
        return $this->_email;
    }
    
    /**
     * 获取问题反馈地址
     *
     * @return string|null
     */
    public function getIssues(): string
    {
        return $this->_issues;
    }
    
    /**
     * 获取论坛地址
     *
     * @return string|null
     */
    public function getForum(): string
    {
        return $this->_forum;
    }
    
    /**
     * 获取wiki地址
     *
     * @return string|null
     */
    public function getWiki(): string
    {
        return $this->_wiki;
    }
    
    /**
     * 获取网址浏览或下载源
     *
     * @return string|null
     */
    public function getSource(): string
    {
        return $this->_source;
    }
    
    /**
     * 获取IRC聊天频道地址，类似于 irc://server/channel
     *
     * @return string|null
     */
    public function getIrc(): string
    {
        return $this->_irc;
    }
    
    private
        $_email,
        $_issues,
        $_forum,
        $_wiki,
        $_source,
        $_irc;
    
}
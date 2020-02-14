<?php

namespace EngineCore\services\extension\repository\configuration;

/**
 * 扩展配置支持信息格式类
 */
class SupportInformation
{
    /**
     * Construct a new support information configuration.
     *
     * @param string|null $email The support email address.
     * @param string|null $issues The URI of the issue tracker.
     * @param string|null $forum The URI of the forum.
     * @param string|null $wiki The URI of the wiki.
     * @param string|null $source The URI to the source code.
     */
    public function __construct(
        $email = null,
        $issues = null,
        $forum = null,
        $wiki = null,
        $source = null
    ) {
        $this->_email = $email;
        $this->_issues = $issues;
        $this->_forum = $forum;
        $this->_wiki = $wiki;
        $this->_source = $source;
    }
    
    /**
     * Get the support email address.
     *
     * @return string|null The support email address.
     */
    public function email()
    {
        return $this->_email;
    }
    
    /**
     * Get the URI of the issue tracker.
     *
     * @return string|null The URI of the issue tracker.
     */
    public function issues()
    {
        return $this->_issues;
    }
    
    /**
     * Get the URI of the forum.
     *
     * @return string|null The URI of the forum.
     */
    public function forum()
    {
        return $this->_forum;
    }
    
    /**
     * Get the URI of the wiki.
     *
     * @return string|null The URI of the wiki.
     */
    public function wiki()
    {
        return $this->_wiki;
    }
    
    /**
     * Get the URI to the source code.
     *
     * @return string|null The URI to the source code.
     */
    public function source()
    {
        return $this->_source;
    }
    
    private
        $_email,
        $_issues,
        $_forum,
        $_wiki,
        $_source;
    
}
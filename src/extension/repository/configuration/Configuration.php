<?php

namespace EngineCore\services\extension\repository\configuration;

use DateTime;

/**
 * 扩展配置格式类
 */
class Configuration
{
    /**
     * The separator used to divide the package name into vendor name and
     * project name.
     */
    const NAME_SEPARATOR = '/';
    
    public function __construct(
        $name = null,
        $description = null,
        $version = null,
        array $keywords = null,
        $homepage = null,
        array $authors = null,
        SupportInformation $support = null,
        array $dependencies = null,
        array $devDependencies = null,
        array $suggest = null,
        array $autoloadPsr4 = null,
        array $repositories = null
    ) {
        if (null === $keywords) {
            $keywords = [];
        }
        if (null === $authors) {
            $authors = [];
        }
        if (null === $support) {
            $support = new SupportInformation;
        }
        if (null === $dependencies) {
            $dependencies = [];
        }
        if (null === $devDependencies) {
            $devDependencies = [];
        }
        if (null === $suggest) {
            $suggest = [];
        }
        if (null === $autoloadPsr4) {
            $autoloadPsr4 = [];
        }
        if (null === $repositories) {
            $repositories = [];
        }
        
        $this->_name = $name;
        $this->_description = $description;
        $this->_version = $version;
        $this->_type = 'ec-extension';
        $this->_keywords = $keywords;
        $this->_homepage = $homepage;
        $this->_authors = $authors;
        $this->_support = $support;
        $this->_dependencies = $dependencies;
        $this->_devDependencies = $devDependencies;
        $this->_suggest = $suggest;
        $this->_autoloadPsr4 = $autoloadPsr4;
        $this->_repositories = $repositories;
    }
    
    /**
     * Get the package name, including vendor and project names.
     *
     * @return string|null The name.
     */
    public function name()
    {
        return $this->_name;
    }
    
    /**
     * Get the project name, without the vendor prefix.
     *
     * @return string|null The project name.
     */
    public function projectName()
    {
        $name = $this->_name();
        if (null === $name) {
            return null;
        }
        
        $atoms = explode(static::NAME_SEPARATOR, $name);
        
        return array_pop($atoms);
    }
    
    /**
     * Get the vendor name, without the project suffix.
     *
     * @return string|null The vendor name.
     */
    public function vendorName()
    {
        $name = $this->_name();
        if (null === $name) {
            return null;
        }
        
        $atoms = explode(static::NAME_SEPARATOR, $name);
        array_pop($atoms);
        if (count($atoms) < 1) {
            return null;
        }
        
        return implode(static::NAME_SEPARATOR, $atoms);
    }
    
    /**
     * Get the package description.
     *
     * @return string|null The description.
     */
    public function description()
    {
        return $this->_description;
    }
    
    /**
     * Get the package version.
     *
     * @return string|null The version.
     */
    public function version()
    {
        return $this->_version;
    }
    
    /**
     * Get the package type.
     *
     * @return string The type.
     */
    public function type()
    {
        return $this->_type;
    }
    
    /**
     * Get the package keywords.
     *
     * @return array<integer,string> The keywords.
     */
    public function keywords()
    {
        return $this->_keywords;
    }
    
    /**
     * Get the URI of the package's home page.
     *
     * @return string|null The home page.
     */
    public function homepage()
    {
        return $this->_homepage;
    }
    
    /**
     * Get the release date of this version.
     *
     * @return DateTime|null The release date.
     */
    public function time()
    {
        return $this->_time;
    }
    
    /**
     * Get the licences the package is released under.
     *
     * @return array<integer,string>|null The licences.
     */
    public function license()
    {
        return $this->_license;
    }
    
    /**
     * Get the authors of the package.
     *
     * @return array<integer,Author> The authors.
     */
    public function authors()
    {
        return $this->_authors;
    }
    
    /**
     * Get support information for the package.
     *
     * @return SupportInformation The support information.
     */
    public function support()
    {
        return $this->_support;
    }
    
    /**
     * Get the package's dependencies, excluding development dependencies.
     *
     * @return array<string,string> The dependencies.
     */
    public function dependencies()
    {
        return $this->_dependencies;
    }
    
    /**
     * Get the package's development dependencies.
     *
     * @return array<string,string> The development dependencies.
     */
    public function devDependencies()
    {
        return $this->_devDependencies;
    }
    
    /**
     * Get all of the package's dependencies, including development, and
     * non-development dependencies.
     *
     * @return array<string,string> All dependencies.
     */
    public function allDependencies()
    {
        return array_merge(
            $this->dependencies(),
            $this->devDependencies()
        );
    }
    
    /**
     * Get the packages that conflict with this version of the package.
     *
     * @return array<string,string> The conflicting packages.
     */
    public function conflict()
    {
        return $this->_conflict;
    }
    
    /**
     * Get the packages that are replaced by this package.
     *
     * @return array<string,string> The replaced packages.
     */
    public function replace()
    {
        return $this->_replace;
    }
    
    /**
     * Get the packages that are provided by this package.
     *
     * @return array<string,string> The provided packages.
     */
    public function provide()
    {
        return $this->_provide;
    }
    
    /**
     * Get suggested packages for use with this package.
     *
     * @return array<string,string> The suggested packages.
     */
    public function suggest()
    {
        return $this->_suggest;
    }
    
    /**
     * Get the PSR-4 autoloading configuration for the package.
     *
     * @return array<string,array<integer,string>> The PSR-4 autoloading configuration.
     */
    public function autoloadPsr4()
    {
        return $this->_autoloadPsr4;
    }
    
    /**
     * Get the PSR-0 autoloading configuration for the package.
     *
     * @return array<string,array<integer,string>> The PSR-0 autoloading configuration.
     */
    public function autoloadPsr0()
    {
        return $this->_autoloadPsr0;
    }
    
    /**
     * Get the class map autoloading configuration for the package.
     *
     * @return array<integer,string> The class map autoloading configuration.
     */
    public function autoloadClassmap()
    {
        return $this->_autoloadClassmap;
    }
    
    /**
     * Get the file autoloading configuration for the package.
     *
     * @return array<integer,string> The file autoloading configuration for the package.
     */
    public function autoloadFiles()
    {
        return $this->_autoloadFiles;
    }
    
    /**
     * Get the include path autoloading configuration for the package.
     *
     * @return array<integer,string> The include path autoloading configuration for the package.
     */
    public function includePath()
    {
        return $this->_includePath;
    }
    
    /**
     * Get an array of all source paths containing PSR-4 conformant code.
     *
     * @return array<integer,string> The PSR-4 source paths.
     */
    public function allPsr4SourcePaths()
    {
        $autoloadPsr4Paths = [];
        foreach ($this->autoloadPsr4() as $namespace => $paths) {
            $autoloadPsr4Paths = array_merge($autoloadPsr4Paths, $paths);
        }
        
        return $autoloadPsr4Paths;
    }
    
    /**
     * Get an array of all source paths containing PSR-0 conformant code.
     *
     * @return array<integer,string> The PSR-0 source paths.
     */
    public function allPsr0SourcePaths()
    {
        $autoloadPsr0Paths = [];
        foreach ($this->autoloadPsr0() as $namespace => $paths) {
            $autoloadPsr0Paths = array_merge($autoloadPsr0Paths, $paths);
        }
        
        return $autoloadPsr0Paths;
    }
    
    /**
     * Get an array of all source paths for this package.
     *
     * @return array<integer,string> All source paths.
     */
    public function allSourcePaths()
    {
        return array_merge(
            $this->allPsr4SourcePaths(),
            $this->allPsr0SourcePaths(),
            $this->autoloadClassmap(),
            $this->autoloadFiles(),
            $this->includePath()
        );
    }
    
    /**
     * Get the target directory for installation.
     *
     * @return string|null The target directory.
     */
    public function targetDir()
    {
        return $this->_targetDir;
    }
    
    /**
     * Get the minimum stability for packages.
     *
     * @return Stability The minimum stability.
     */
    public function minimumStability()
    {
        return $this->_minimumStability;
    }
    
    /**
     * Returns true if stable packages should take precedence.
     *
     * @return boolean True if stable packages should take precedence.
     */
    public function preferStable()
    {
        return $this->_preferStable;
    }
    
    /**
     * Get the custom repositories used by this package.
     *
     * @return array<integer,RepositoryInterface> The custom repositories.
     */
    public function repositories()
    {
        return $this->_repositories;
    }
    
    /**
     * Get the configuration options for the package that are specific to
     * project-type repositories.
     *
     * @return ProjectConfiguration The project configuration.
     */
    public function config()
    {
        return $this->_config;
    }
    
    /**
     * Get the hook scripts for the package.
     *
     * @return ScriptConfiguration The hook scripts.
     */
    public function scripts()
    {
        return $this->_scripts;
    }
    
    /**
     * Get the arbitrary extra data contained in the project's configuration.
     *
     * @return mixed The extra data.
     */
    public function extra()
    {
        return $this->_extra;
    }
    
    /**
     * Get the binary executable files provided by the package.
     *
     * @return array<integer,string> The executable files.
     */
    public function bin()
    {
        return $this->_bin;
    }
    
    /**
     * Get the archive configuration for the package.
     *
     * @return ArchiveConfiguration The archive configuration.
     */
    public function archive()
    {
        return $this->_archive;
    }
    
    /**
     * Get the raw configuration data.
     *
     * @return mixed The raw configuration data.
     */
    public function rawData()
    {
        return $this->_rawData;
    }
    
    private
        $_name,
        $_description,
        $_version,
        $_type,
        $_keywords,
        $_homepage,
        $_time,
        $_license,
        $_authors,
        $_support,
        $_dependencies,
        $_devDependencies,
        $_conflict,
        $_replace,
        $_provide,
        $_suggest,
        $_autoloadPsr4,
        $_autoloadPsr0,
        $_autoloadClassmap,
        $_autoloadFiles,
        $_includePath,
        $_targetDir,
        $_minimumStability,
        $_preferStable,
        $_repositories,
        $_config,
        $_scripts,
        $_extra,
        $_bin,
        $_archive,
        $_rawData;
}

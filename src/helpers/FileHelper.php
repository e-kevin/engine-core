<?php
/**
 * @link https://github.com/e-kevin/engine-core
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\helpers;

use Yii;
use yii\helpers\FileHelper as BaseFileHelper;

/**
 * 文件助手类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class FileHelper extends BaseFileHelper
{
    
    public static function isAbsolutePath($path)
    {
        return substr($path, 0, 1) === '/' || substr($path, 1, 1) === ':' || substr($path, 0, 2) === '\\\\';
    }
    
    public static function buildPath($paths, $withStart = false, $withEnd = false)
    {
        $res = '';
        
        foreach ($paths as $path) {
            $res .= $path . DIRECTORY_SEPARATOR;
        }
        if ($withStart) {
            $res = DIRECTORY_SEPARATOR . $res;
        }
        if (!$withEnd) {
            $res = rtrim($res, DIRECTORY_SEPARATOR);
        }
        
        return $res;
    }
    
    public static function isDir($path)
    {
        return is_dir($path);
    }
    
    public static function exist($path)
    {
        if (is_array($path)) {
            $path = self::buildPath($path);
        }
        $path = self::normalizePath($path);
        
        return file_exists($path);
    }
    
    public static function getFiles($path, $prefix = null)
    {
        if (is_array($path)) {
            $path = self::buildPath($path);
        }
        
        if (!is_dir($path)) {
            // var_dump($path);
            return [];
        }
        
        $files = scandir($path);
        if ($prefix == null) {
            return $files;
        }
        
        $res = [];
        foreach ($files as $file) {
            if (strpos($file, $prefix) === 0) {
                $res[] = $file;
            }
        }
        
        return $res;
    }
    
    public static function createFile($filePath, $content, $mode = 0777): bool
    {
        if (@file_put_contents($filePath, $content, LOCK_EX) !== false) {
            if ($mode !== null) {
                @chmod($filePath, $mode);
            }
            
            return @touch($filePath);
        } else {
            $error = error_get_last();
            Yii::warning("Unable to write file '{$filePath}': {$error['message']}", __METHOD__);
            
            return false;
        }
    }
    
    public static function removeFile($filePath)
    {
        return parent::unlink($filePath);
    }
    
    public static function readFile($filePath)
    {
        if (is_array($filePath)) {
            $filePath = self::buildPath($filePath);
        }
        
        return file_get_contents($filePath);
    }
    
    public static function writeFile($filePath, $content, $mode = 'w')
    {
        if (is_array($filePath)) {
            $filePath = self::buildPath($filePath);
        }
        
        $f = fopen($filePath, $mode);
        fwrite($f, $content);
        fclose($f);
    }
    
    public static function createDir($dirPath)
    {
        parent::createDirectory($dirPath);
    }
    
    public static function removeDir($dirPath)
    {
        parent::removeDirectory($dirPath);
    }
    
}
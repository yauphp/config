<?php

namespace Yauphp\Config\Impl;

/**
 * 从缓存创建配置实例
 * @author Administrator
 *
 */
class CacheConfiguration extends PhpConfiguration
{
    /**
     * 构造函数
     * @param string $cacheFile  缓存PHP文件
     * @param string $configFile 原始配置文件
     * @throws \Exception
     */
    public function __construct($cacheFile, $configFile){

        //文件是否存在
        if (!file_exists($cacheFile)) {
            throw new \Exception("Fail to load cache file!");
        }
        $this->m_configFile=$configFile;
        $this->m_configFiles=[$configFile];
        $this->m_configData = require $cacheFile;
    }
}


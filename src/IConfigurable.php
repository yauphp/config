<?php
namespace Yauphp\Config;

/**
 * 注入配置接口
 * @author Tomix
 *
 */
interface IConfigurable
{
    /**
     * 注入配置实例
     * @param IConfiguration $value
     */
    function setConfiguration(IConfiguration $value);
}


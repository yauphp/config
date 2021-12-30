<?php
namespace Yauphp\Config;

/**
 * 默认设置参数
 * @author Tomix
 *
 */
class DefaultSettings
{
    /**
     * 全局配置节点名
     */
    public const GLOBAL_SECTION_NAME="global";

    /**
     * 对象工厂配置节点名
     */
    public const OBJECT_FACTORY_SECTION_NAME="objectFactory";

    /**
     * 对象配置:应用目录占位符
     */
    public const BASE_DIR_PLACE_HOLDER="\${baseDir}";

    /**
     * 对象配置:用户目录占位符
     */
    public const USER_DIR_PLACE_HOLDER="\${userDir}";

    /**
     * 对象配置:配置根目录占位符
     */
    public const CONFIG_DIR_PLACE_HOLDER="\${configDir}";
}
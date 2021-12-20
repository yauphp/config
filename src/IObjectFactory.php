<?php
namespace Yauphp\Config;

/**
 * 对象工厂接口
 * @author Tomix
 *
 */
interface IObjectFactory
{
    /**
     * 根据对象ID创建实例
     * @param string $objectId  对象ID
     * @return object
     */
    function create($objectId);

    /**
     * 根据类型名创建实例
     * @param string $class             类型名称
     * @param array $constructorArgs    构造参数(对于已在配置中定义的类型,该参数被忽略)
     * @param bool $singleton           是否单例模式,默认为单例模式(对于已在配置中定义的类型,该参数被忽略)
     * @return object
     */
    function createByClass($class, $constructorArgs=[], $singleton=true);
}


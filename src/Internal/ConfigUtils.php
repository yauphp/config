<?php
namespace Yauphp\Config\Internal;

use Yauphp\Config\IConfiguration;
use Yauphp\Config\DefaultSettings;

/**
 * 配置内部工具类
 * @author Tomix
 *
 */
class ConfigUtils
{
    /**
     * 替换占位符值,如:${section.key}
     * @param string $value
     * @param IConfiguration $config
     */
    public static function replaceValueHolder($value, IConfiguration $config){

        if(!empty($value)){
            $matches=[];
            if(preg_match_all("/\\\$\{([\w\\\.]+)\}/U",$value,$matches)){
                $holders=$matches[0];
                $keys=$matches[1];
                for($i=0;$i<count($holders);$i++){
                    $holder=$holders[$i];
                    $keyString=$keys[$i];
                    $_value=self::getConfigValue($keyString, $config);
                    if(!empty($_value) && !is_array($_value) && !is_object($_value)){
                        $value=str_replace($holder, $_value, $value);
                    }
                }
            }

            $value=str_replace(DefaultSettings::BASE_DIR_PLACE_HOLDER, $config->getBaseDir(), $value);
            $value=str_replace(DefaultSettings::USER_DIR_PLACE_HOLDER, $config->getUserDir(), $value);
            $value=str_replace(DefaultSettings::CONFIG_DIR_PLACE_HOLDER, $config->getConfigDir(), $value);

        }
        return $value;
    }

    /**
     *
     * @param string $keyString
     * @param IConfiguration $config
     */
    private static function getConfigValue($keyString, IConfiguration $config){

        $keys=explode(".", $keyString);
        $section=$keys[0];
        $values=$config->getConfigValues($section);

        $current=$values;
        for($i=1;$i<count($keys);$i++){
            $key=$keys[$i];
            if(array_key_exists($key, $current)){
                $current=$current[$key];
            }else{
                break;
            }
        }
        return $current;
    }
}


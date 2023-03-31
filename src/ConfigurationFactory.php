<?php
namespace Yauphp\Config;

use Yauphp\Config\Impl\PhpConfiguration;
use Yauphp\Config\Impl\XmlConfiguration;
use Yauphp\Config\Impl\YmlConfiguration;
use Yauphp\Config\Impl\CacheConfiguration;

/**
 * 配置工厂
 * @author Tomix
 *
 */
class ConfigurationFactory
{
    /**
     * 配置类静态实例数组
     * 键为配置文件，值为配置对象
     */
    private static $m_configs = [];

    /**
     * 持久化配置文件时的键
     * @var string
     */
    private static $m_configFilesKey = "_xml-source-config-files";

    /**
     * 调试模式开关(只用于配置工厂的调试模式)
     * @var string
     */
    private static $m_debug = false;

    /**
     * 默认缓存目录(默认为: ${configDir}/../var/cache)
     * @var string
     */
    private static $m_cacheDir = "\${configDir}/../var/cache";

    /**
     * 调试模式开关(只用于配置工厂的调试模式)
     * @param bool $value
     */
    public static function setDebug($value){

        self::$m_debug = $value;
    }

    /**
     * 设置缓存目录
     * @param string $value
     */
    public static function setCacheDir($value){

        self::$m_cacheDir = $value;
    }

    /**
     * 创建配置实例
     * @param string $configFile 入口配置文件名
     * @param string $baseDir    应用根目录
     * @param string $userDir    用户根目录
     * @param array  $extConfigs 附加扩展的配置(section,name,value形式的数组)
     * @return IConfiguration
     */
    public static function create($configFile, $baseDir="", $userDir="", $extConfigs=[]) : IConfiguration{

        //配置文件
        $pathInfo = pathinfo($configFile);
        $configDir = $pathInfo["dirname"];
        //$baseFile = $pathInfo["dirname"]."/".$pathInfo["filename"];

        //如果缓存存在,则直接返回实例
        if(array_key_exists($configFile, self::$m_configs)){
            return self::returnConfig(self::$m_configs[$configFile],$baseDir,$userDir,$extConfigs);
        }

        //配置文件类型为PHP文件时,从PHP数组读入
        $ext = strtolower($pathInfo["extension"]);
        if($ext == "php"){
            if(!file_exists($configFile)){
                throw new \Exception("Fail to load configuration file '".$configFile."'!");
            }
            //从php读取配置
            $config=new PhpConfiguration($configFile);
            self::$m_configs[$configFile]=$config;
            return self::returnConfig($config,$baseDir,$userDir,$extConfigs);
        }

        //其它类型(xml,yaml)会读取后缓存为php数组文件
        $cacheKey=md5($configFile);
        $cacheDir=self::$m_cacheDir;
        $cacheDir=str_replace(DefaultSettings::BASE_DIR_PLACE_HOLDER, $baseDir, $cacheDir);
        $cacheDir=str_replace(DefaultSettings::USER_DIR_PLACE_HOLDER, $userDir, $cacheDir);
        $cacheDir=str_replace(DefaultSettings::CONFIG_DIR_PLACE_HOLDER, $configDir, $cacheDir);
        $cacheFile=$cacheDir."/".$cacheKey;

        //从缓存(php数组)读取配置: 如果缓存文件存在且修改时间大于所有的xml配置文件
        if(file_exists($cacheFile)){
            //从缓存文件读取配置
            $config=new CacheConfiguration($cacheFile,$configFile);

            //如果原始配置文件不存在,则直接从php配置直接返回
            if(!file_exists($configFile)){
                self::$m_configs[$configFile]=$config;
                return self::returnConfig($config,$baseDir,$userDir,$extConfigs);
            }

            //如果原始配置文件存在,则要检查文件最后修改时间
            $phpTime=filemtime($cacheFile);
            $overrided=false;
            foreach ($config->getConfigValues(self::$m_configFilesKey) as $f){
                if(file_exists($f) && filemtime($f) > $phpTime){
                    $overrided=true;
                    break;
                }
            }

            //php文件较新且非调试模式下
            if(!$overrided && !self::$m_debug){
                self::$m_configs[$configFile]=$config;
                return self::returnConfig($config,$baseDir,$userDir,$extConfigs);
            }
        }

        //从非php文件读取配置,并缓存到php
        if(!file_exists($configFile)){
            throw new \Exception("Fail to load configuration file '".$configFile."'!");
        }
        $config=null;
        if($ext=="xml"){
            $config=new XmlConfiguration($configFile);
        }else if($ext=="yml" || $ext=="yaml"){
            $config=new YmlConfiguration($configFile);
        }else{
            throw new \Exception("Not supported configuration file '".$configFile."'!");
        }
        self::$m_configs[$configFile]=$config;
        self::dump2PhpFile($config, $cacheFile);

        //返回实例
        return self::returnConfig(self::$m_configs[$configFile],$baseDir,$userDir,$extConfigs);
    }

    /**
     * 返回的配置实例
     * @param IConfiguration $config
     * @param string $baseDir
     * @param string $userDir
     * @param array $extConfigs
     * @return IConfiguration
     */
    private static function returnConfig(IConfiguration $config,$baseDir="",$userDir="",$extConfigs=[])
    {
        $config->setBaseDir($baseDir);
        $config->setUserDir($userDir);
        if(!empty($extConfigs)){
            foreach ($extConfigs as $ext){
                $config->addConfigValue($ext["section"], $ext["name"], $ext["value"]);
            }
        }
        return $config;
    }

    /**
     * 把配置内容持久化成php文件
     * @param IConfiguration $config
     * @param string $file
     */
    private static function dump2PhpFile(IConfiguration $config,$file){

        $dir=dirname($file);
        if(!is_dir($dir)){
            mkdir($dir,0777,true);
        }

        $values=$config->getAllValues();
        $values[self::$m_configFilesKey]=$config->getConfigFiles();
        $content=var_export($values, TRUE);
        $content="<?php\r\nreturn\r\n".$content.";";
        file_put_contents($file, $content);
    }

}
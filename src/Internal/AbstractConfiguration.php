<?php
namespace Yauphp\Config\Internal;

use Yauphp\Config\IConfiguration;
use Yauphp\Config\IObjectFactory;
use Yauphp\Config\IConfigurable;
use Yauphp\Config\DefaultSettings;

/**
 * 配置抽象基类
 * @author Tomix
 *
 */
abstract class AbstractConfiguration implements IConfiguration
{
    /**
     * 应用根目录
     * @var string
     */
    protected $m_baseDir="";

    /**
     * 用户根目录
     * @var string
     */
    protected $m_userDir="";

    /**
     * 所有配置数据
     * @var array
     */
    protected $m_configData=[];

    /**
     * 配置入口文件
     * @var string
     */
    protected $m_configFile;

    /**
     * 所有参与配置的文件
     * @var array
     */
    protected $m_configFiles=[];

    /**
     * 对象工厂实例
     * @var IObjectFactory
     */
    protected $m_objectFactory=null;

    /**
     * 获取全局配置值
     * @param string $name 配置键
     */
    function getGlobalValue($name){

        return $this->getConfigValue(DefaultSettings::GLOBAL_SECTION_NAME, $name);
    }

    /**
     * 获取配置值
     * @param string $section 配置节点
     * @param string $name 配置键名
     */
    public function getConfigValue($section, $name){

        $values=$this->getConfigValues($section);
        if(array_key_exists($name, $values)){
            return $values[$name];
        }
        return null;
    }

    /**
     * 获取配置节点的所有值
     * @param string $section 配置节点
     */
    public function getConfigValues($section) : array{

        if(array_key_exists($section, $this->m_configData)){
            return $this->m_configData[$section];
        }
        return [];
    }

    /**
     * 获取所有的配置
     */
    public function getAllValues() : array{

        return $this->m_configData;
    }

    /**
     * 当前入口配置文件
     */
    public function getConfigFile(){

        return $this->m_configFile;
    }

    /**
     * 获取所有参与配置的文件
     */
    public function getConfigFiles() : array{

        return $this->m_configFiles;
    }

    /**
     * 当前入口配置文件所在目录
     */
    public function getConfigDir(){

        return dirname($this->m_configFile);
    }

    /**
     * 添加配置到当前实例
     * @param string $section   配置节点
     * @param string $name      配置键名
     * @param string $value     配置值
     */
    public function addConfigValue($section, $name, $value){

        if(!array_key_exists($section, $this->m_configData)){
            $this->m_configData[$section]=[];
        }
        $this->m_configData[$section][$name]=$value;
    }

    /**
     *获取当前应用根目录(若未设置应用根目录,则返回配置入口文件所在目录)
     */
    public function getBaseDir(){

        if(!empty($this->m_baseDir)){
            return $this->m_baseDir;
        }
        return $this->getConfigDir();
    }

    /**
     * 设置当前应用根目录
     */
    public function setBaseDir($value){

        $this->m_baseDir=$value;
    }


    /**
     * 获取用户目录(未设置时应该返回应用根目录)
     */
    public function getUserDir(){

        if(!empty($this->m_userDir)){
            return $this->m_userDir;
        }
        return $this->getBaseDir();
    }

    /**
     * 设置用户目录
     * @param string $value
     */
    public function setUserDir($value){

        $this->m_userDir=$value;
    }

    /**
     * 获取对象工厂实例
     * @return IObjectFactory
     */
    public function getObjectFactory() :IObjectFactory{

        if(is_null($this->m_objectFactory)){

            //工厂配置
            $cfgValues = $this->getConfigValues(DefaultSettings::OBJECT_FACTORY_SECTION_NAME);

            //工厂类
            $class="";
            if(array_key_exists("class", $cfgValues)){
                $class=$cfgValues["class"];
            }else {
                $class=ObjectFactory::class;
            }
            if(!class_exists($class)){
                throw new \Exception("Class '".$class."' not found");
            }
            $obj=new $class();
            if($obj instanceof IConfigurable){
                $obj->setConfiguration($this);
            }

            //注入参数(全局参数与配置参数;只能为值类型)
            $params=$this->getConfigValues(DefaultSettings::GLOBAL_SECTION_NAME);
            foreach ($cfgValues as $name=>$value){
                if($name!="class"){
                    $params[$name]=$value;
                }
            }
            foreach ($params as $name => $value){
                $setter="set".ucfirst($name);
                if(method_exists($obj, $setter)){
                    $value=ConfigUtils::replaceValueHolder($value, $this);
                    $obj->$setter($value);
                }
            }
            $this->m_objectFactory=$obj;
        }
        return $this->m_objectFactory;
    }
}


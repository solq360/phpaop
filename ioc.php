<?php
	/**
	 * @author solq
	 * @deprecated blog http://solq.cnblogs.com
	 */

include_once "config.php";
/**
扫描BEAN
*/
function scan_bean(){
	global $_suffix;
	global $_beans;
	global $_ioc;	

	for($i=0;$i<count($_beans);$i++){ 
		$name = $_beans[$i];
		$file = $name.$_suffix;
		include_once $file;
		register_bean($name,new $name);
	}
}

/**注册BEAN*/
function register_bean($name,$bean){
	global  $_ioc;	
	$_ioc[$name]=$bean;
}

/**获取BEAN*/
function get_bean($name){
	global  $_ioc;
	return  $_ioc[$name];
}

/**容器注册后期阶段*/
function postConstruct_bean(){
	global  $_ioc;
	foreach($_ioc as $bean){	
		if (is_subclass_of($bean, 'Ioc')) {
			$bean->{"setIoc"}($_ioc);
			$bean->{"postConstruct"}();
		}  
	}
}
/**容器销毁阶段*/
function preDestroy_bean(){
	global  $_ioc;
	foreach($_ioc as $bean){
 		if (is_subclass_of($bean, 'Ioc')) {
			$bean->{"preDestroy"}();
		}  
	}
}


interface  Ioc{
	public function postConstruct();
	public function preDestroy();
	public function setIoc($_ioc);
}

abstract class AbstractIoc implements Ioc{
	public function postConstruct(){}
	public function preDestroy(){}
	public function setIoc($_ioc){}
}

?>
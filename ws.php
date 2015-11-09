<?php
	/**
	 * @author solq
	 * @deprecated blog http://solq.cnblogs.com
	 */

include_once "ioc.php";
scan_bean();

$page=$_SERVER['REQUEST_URI'];
$segments=explode('/',trim($page,'/'));

global $_app_path_index;
//应用
$app = $segments[$_app_path_index];
//服务
$service = $segments[$_app_path_index+1];

$method=$service;
$get_params = $_GET;
$post_params = $_POST;

$bean = get_bean($app);

if($bean ==null){
	throw new Exception("bean  [".$app."] not find"); 
}

postConstruct_bean();
___call($bean,$method,$get_params,$post_params);
preDestroy_bean();

/**
获取请求方式
*/
function get_request_method(){
	return strtolower($_SERVER['REQUEST_METHOD']);
}

/**
动态映射处理
*/
function ___call($bean,$method, $get_params = array(), $post_params = array()){ 

	$method = get_request_method().'_'.$method;	
	$reflection = new ReflectionMethod($bean, $method);
	$pass = array(); 
	if(strpos($method,"post_")){
		$args = $post_params;
	}else{
		$args = $get_params;			
	}
	
	foreach($reflection->getParameters() as $param) {		
		//数据类型注入分解
		$value = $args[$param->getName()];
		if($value==null && !$param->isDefaultValueAvailable()){
			throw new Exception("method [".$method."] param is not :".$param->getName()); 
		}
		$pass[] = $value; 
	}
	return $reflection->invokeArgs($bean, $pass); 
} 
?>
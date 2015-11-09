三个核心文件

1.公开访问web service配置 config.php

2.管理BEAN，扫描，注册，初始化等流程 ioc.php

3.管理 rest 拦载处理 ws.php

config.php
============

```
<?php
	/**
	 * @author solq
	 * @deprecated blog http://solq.cnblogs.com
	 */
	$_suffix = ".php";	
	$_beans=array(
		'TestRef',
	); 
	/**容器注册类*/
	$_ioc= array();
	$_app_path_index=1;
?>
```

ioc.php
============
 
```
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
```

 

 

ws.php
============

```
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
```

TestRef.php
============

```

<?php
include_once "ioc.php";
class TestRef extends AbstractIoc
{
    public $one = 'aaaaaaaa';
    
     public function __construct(){
     }
   
	/**
		书写约定
		[请求方式]_[服务]
	*/
     public function get_test1($a,$b,$c=null){
        echo $this->one."\n";
        echo $b."\n";
        echo $c."\n";
    }
	
	public function preDestroy(){
		echo "<br/>postConstruct_bean";
	}
}
?>
```

```
测试url : http://127.0.0.1/ws.php/TestRef/test1/?a=121212&b=1212
aaaaaaaa 1212 
postConstruct_bean
```
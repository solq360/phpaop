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
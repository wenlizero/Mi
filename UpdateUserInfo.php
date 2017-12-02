<?php
	include './api/Medoo.php';
	header("content-type:text/html;charset=utf-8");
	$config=array(
		'database_type' => 'mysql',
		'database_name' => 'thinkshop',
		'server' => '127.0.0.1',
		'username' => 'root',
		'password' => '123', 
		'charset' => 'utf8',
		'port' => 3306,
	    'prefix' => 'ts_'
	);
	$m = new Medoo($config);

	function msg($data,$url){
		echo "<script>alert('$data');window.location.href='$url'</script>";
	}
	$uid = $_GET['uid'];
	if($uid==""){
		msg("无法修改！","./usercenter.html");
	}else{
		array_pop($_GET);
		$row =$m->update("userinfo",$_GET,['uid'=>$uid]);
		if($row>=1){
			msg("修改成功！","./usercenter.html");
		}else{
			msg("修改失败！","./usercenter.html");

		}
	}


?>
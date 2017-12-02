<?php
	header("content-type:text/html;charset=utf-8");
	$pdo = new PDO("mysql:host=localhost;dbname=thinkshop",'root','123');
	$pdo->exec("set names utf8");

	if($_GET['pid']){
		extract($_GET);
		//rovince=北京市&city=北京市市辖区&area=东城区&addr=&size=XXL&color=%23A52A2A&pid=10&uid=2
		$color = str_replace("%23","#",$color);
		$sql ="insert into ts_cart(color,size,num,user_id,pid) values('$color','$size',1,$uid,$pid)";
		$lastid=$pdo->exec($sql);
		if($lastid>=1){
		
			msg("添加成功","./index.html");
		}else{

			msg("添加失败","./index.html");
			
		}

	}else{
		
			msg("无法添加产品","./index.html");
		
	}

	function msg($data,$url){
		echo "<script>alert('$data');window.location.href='$url'</script>";
	}
?>
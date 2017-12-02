<?php
include './Medoo.php';

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
/*
	查出该类的所有子类，子子类
*/
function getChildCate($pc_id)
{
	global $m;
	static $str_id = "";
	if($pc_id)
	{
		$sql = "select pc_id from ts_product_cate where pc_fid =".$pc_id;
		$result = $m->pdo->query($sql)->fetchAll(2);
		if($result !=false)
		{	
			foreach($result as $v)
			{
				$str_id .=$v['pc_id'].',';
				getChildCate($v['pc_id']);	
			}
		}
	}
	return $str_id;
}

//////
///1、 所有的接口请求全部使用get
///2、 已经做到了请求的信息全部使用函数来处理，就要求你的请求必须告诉接口文件该使用哪一个函数   url:"./data.php"  data:"m=xxxx"
///3、 我的接口提供的全是json数据，所以你的ajax 必须 dataType：json


//判断是否具有请求方法
if(!$_GET['m']){
	$data=array(
		'status'=>0,
		'info'=>'方法不存在 function not found'
	);
	echo json_encode($data);
}else{
	$func=$_GET['m'];
	if(function_exists($func)){
		$data=$func();
		echo json_encode($data);
	}else{
		$data=array(
				'status'=>0,
				'info'=>'方法不存在 cxxxxxx'
			);
		echo json_encode($data);
	}
}


//验证用户名是否已经存在

function checkname(){
		global $m;
		$username=trim($_GET['username']);
		if($username==""){
			$data=array(
				'status'=>0,
				'info'=>'用户名为空！kongkongkong'
			);
			return $data;
		}else{
				$result = $m->select("user", "*", array("username" =>"$username"));
			if(count($result)>=1){
				$data =array(
					'status'=>0,
					'info'=>'用户名已经被注册1111111'
				);
				return $data;
			}else{

					$data =array(
					'status'=>1,
					'info'=>'用户名可以注册0000000'
					);
				return $data;
			}
		}
}

//注册
	function regist(){
		global $m;
		$username=trim($_GET['username']);
		$password=trim($_GET['password']);
		if($username==""||$password==""){
			$data=array(
				'status'=>0,
				'info'=>'用户名或者密码为空！'
			);
			return $data;
		}else{
			$password=md5($password);
			$result = $m->select("user", "*", array("username" =>"$username"));
			if(count($result)>=1){
				$data =array(
					'status'=>0,
					'info'=>'用户名已经被注册'
				);
				return $data;
			}else{
				$last_id=$m->insert("user",array(
						'username'=>"$username",
						'password'=>"$password"
					));
				if($last_id>=1){
					$data = array(
						'status'=>1,
						'info'=>'注册成功'
					);
					return $data;
				}else{
					$data = array(
						'status'=>0,
						'info'=>'注册失败'
					);
					return $data;
				}
			}
		}
	}



//登录
	function login(){
		global $m;
		$username=trim($_GET['username']);
		$password=trim($_GET['password']);
		if($username ==""||$password==""){
			$data=array(
				'status'=>0,
				'info'=>'用户名或者密码为空！'
			);
			return $data;
		}else{
			$password=md5($password);
			$result = $m->select("user", "*", array(
				    "username" =>"$username",
				    'password'=>"$password"
				));

			if(count($result)<1){
				$data =array(
					'status'=>0,
					'info'=>'用户名或密码错误'
				);
				return $data;
			}else{
				$data =array(
					'status'=>1,
					'info'=>"登录成功",
					'data'=>$result[0]
				);
				//判断是否记住用户名密码
				if($_GET['isCk']=="yes"){
					setCookie("uname",$_GET['username'],time()+60*60*24*7,"/Mi");
					setCookie("pwd",$_GET['password'],time()+60*60*24*7,"/Mi");
					setcookie("uid",$result[0]['uid'],time()+60*60*24*7,"/Mi");
				}
				return $data;
			}
		}
	}




//获取产品分类
	function getcates(){
		global $m;
		$result=$m->select("product_cate",['pc_id','pc_name','pc_ifshow']);
		$data=array(
			'status'=>1,
			'info'=>"查询成功",
			'data'=>$result
		);
		return $data;
	}


/*
// $res = $m->select('users','*');
// $data = [
// 	'countpage'=>10,
// 	'curpage'=>1,
// 	'data'=>$res
// ];
*/


//获取产品
	function product(){
		global $m;
		//获取分页
		if(!$_GET['page']){
			$page=1;
		}else{
			$page=$_GET['page'];
		}
		//获取分类信息
		if(!$_GET['pc_id']){
			$pc_id=1;
		}else{
			$pc_id=$_GET['pc_id'];
		}
		//查询分类信息是否存在
		$result=$m->select("product_cate",'pc_id');
		if(!in_array($pc_id,$result)){
			$data = array(
				'status'=>0,
				'info'=>"无法查询到该分类"
			);
			return $data;
		}
		//查找该分类的所有子分类
		$pc_ids1=getChildCate($pc_id);
		$pc_ids2=substr($pc_ids1,0,-1);
		$pc_ids=explode(",",$pc_ids2);
		
		//总记录数
		$totalrows=$m->count('product',"*",['p_c_id'=>$pc_ids]);
		//每页4条
		$eachpage=4;
		//总页码数
		$totalpages=ceil($totalrows/$eachpage);
		//判断页码是否合格
		if($page<=0){
			$page=1;
		}elseif($page>$totalpages){
			$page=$totalpages;
		}
		//limit参数1
		$start = ($page-1)*$eachpage;
		$data= $m->pdo->query("select * from ts_product where p_c_id in(".$pc_ids2.") limit $start,$eachpage")->fetchAll(2);
		

		array_walk($data,function(&$v,$k){

			$pimgsfull = explode(',',$v['pimgs']);

			foreach($pimgsfull as $ko=>$vo){
				$pimgsfull[$ko] = 'http:127.0.0.1:80/admin/'.$vo;
		 	}

		 	$v['pimgsfull']=$pimgsfull;
		});

		$data = array(
				'status'=>1,
				'info'=>"查询成功",
				'countpage'=>$totalpages,
				'curpage'=>$page,
				'data'=>$data
			);
		return $data;
	}



//获取产品详情
	function p_details(){
		global $m;
		if(!$_GET['pid']){
			$data=array(
				'status'=>0,
				'info'=>'产品编号输入错误！'
			);
			return $data;
		}
		$pid=$_GET['pid'];

		$list=$m->select("product","*",['pid'=>$pid]);
		if(count($list)<1){
			$data=array(
				'status'=>1,
				'info'=>'产品以下架',
			);
			return $data;
		}
		$data=array(
			'status'=>0,
			'info'=>'查询成功',
			'data'=>$list
		);
		return $data;
	}

//获取个人信息

	function userinfo(){
		global $m;
		if(!$_GET['uid']){
			$data=array(
				'status'=>0,
				'ifno'=>"用户编号错误"
			);
			return $data;
		}
		$uid=$_GET['uid'];
		$list=$m->select("user","*",['uid'=>$uid]);
		if(count($list)<1){
			$data=array(
				'status'=>0,
				'info'=>"该用户已被禁用"
			);
		}else{
			$data=array(
				'status'=>1,
				'info'=>"查询成功",
				'data'=>$list
			);
		}
		return $data;
	}
//购物车信息
	function GetShopCart(){
		global $m;
		$uid= $_COOKIE['uid'];

		if(!$uid){
			$data=array(
				'status'=>0,
				'info'=>"用户信息获取失败"
			);
			return $data;
		}
		
		$sql ="select cart_id,p.pid,pname,price,c.color,c.size,num from ts_cart c inner join ts_product p on c.pid=p.pid where user_id=$uid";
		$arr=$m->query($sql)->fetchAll(2);

		if(count($arr)>=1){
			$data=array(
				'status'=>1,
				'info'=>"购物车查询成功",
				"data"=>$arr
			);
			return $data;
		}else{
			$data=array(
				'status'=>0,
				'info'=>"购物车为空",
			);
			return $data;
		}
	}
	//购物车表删除接口
	//
	function DelShopCart(){
		global $m;
		$uid= $_COOKIE['uid'];

		if(!$uid){
			$data=array(
				'status'=>0,
				'info'=>"用户信息获取失败"
			);
			return $data;
		}
		if($_GET['cart_id']==""){
			$data=array(
				'status'=>0,
				'info'=>"无法删除未知数据"
			);
			return $data;
		}else{
			$cart_id = $_GET['cart_id'];
		}
		
		$rows =$m->delete("cart",['cart_id'=>$cart_id]);

		if($rows>=1){
			$data=array(
				'status'=>1,
				'info'=>"删除成功",
			);
			return $data;
		}else{
			$data=array(
				'status'=>0,
				'info'=>"删除失败",
			);
			return $data;
		}
	}


	//获取用户信息

function GetUserInfo(){
	global $m;
	$uid = $_COOKIE['uid'];
	if($uid==""){
		$data = [
			'info'=>"用户信息失效，请重新登录",
			'status'=>0
		];
	}else{
		$list=$m->select("userinfo","*",['uid'=>$uid]);
		if(count($list)<1){
			$data = [
				'status'=>1,
				'info'=>"用户信息为空,请及时补全信息，提高账户安全",
			];
		}else{
			$data = [
				'status'=>2,
				'info'=>"用户信息查询成功",
				'data'=>$list
			];
		}

	}
	return $data;
}




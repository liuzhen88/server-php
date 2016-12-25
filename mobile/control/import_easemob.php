
<?php
/**
 * 用户数据导入环信
 * @authors solon.ring2011@gmail.com
 * @date    2015-10-23 14:03:02
 * @version V1.0
 */
use Tpl;
defined('emall') or exit('Access Invalid!');
class import_easemobControl extends mobileHomeControl {
	public function indexOp(){
		$model = Model('member');
		$page = isset($_GET['page'])?$_GET['page']:1;
		$offset = 20;
		$easemob = new Easemob();
		//删除
		if($_GET['del'] == 'all'){echo $easemob->batchDeleteUser();exit();};
		$count = $model->query("select count(*) as c from agg_member where  member_name REGEXP '^[0-9a-dA-Z._-]+$' order by member_id asc");
		//$rt = $model->field('member_name,member_truename,member_passwd')->order('member_id desc')->page(2, 10)->select();
		$total = ceil($count[0]['c']/$offset);
		echo "total".$total."<br>";
		for ($j=0;$j<$total;$j++) {//每秒20个
			echo "page ".$j."<br>";
			$a = $j*$offset;
			$rt = $model->query("select member_name,member_truename,member_passwd from agg_member where  member_name REGEXP '^[0-9a-dA-Z._-]+$' order by member_id asc limit {$a},{$offset}");
			foreach ($rt as $key => $value) {
				//$arr[$key]['username'] = $value['member_name'];
				//$arr[$key]['nickname'] = $value['member_truename'];
				//$arr[$key]['password'] = $value['member_passwd'];
				$easemob->registerToken($value['member_name'],$value['member_passwd'],$value['member_truename']);
			}
			sleep(1);
		}
		echo "finish";
		//print_r($arr);
		// $a = registerToken('11111b','111111a','111111a'); //注册
		// if($a != 200 && $a != 400 && $a != 401){
		// 	echo "000";
		// }else{
		// 	print_r($a);
		// }
		//print_r(changePwdToken('234234','123123')); //更改密码
		//print_r(accreditRegister($arr)); //批量注册
   		//output_error('参数有误', array(), 80000);
   }

   public function set_redisOp(){
   	$client = new QueueClient;
   	$value = array('member_id'=>'72953','member_name'=>'TT');
    $client->push('addPoint', $value);
   }
}
/*----------------------moblie/control/easemob.php-------------------------*/
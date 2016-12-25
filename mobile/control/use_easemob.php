<?php
/**
 * 环信服务器端处理
 * @authors solon.ring2011@gmail.com
 * @date    2016-01-18 14:03:02
 * @version V1.0
 */
use Tpl;
defined('emall') or exit('Access Invalid!');
class use_easemobControl extends mobileMemberControl {

	private $easemob;

	public function __construct(){
         parent::__construct();
        $this->easemob = new Easemob(array());
    }

	public function indexOp(){
		output_error('无效方法', array());
   }

   /**
    * 好友详情
    * @return [type] [description]
    */
   public function get_user_infoOp(){
      $model  = Model('member');
      $member_id  = intval($_REQUEST['member_id']);
      $friend_id  = intval($_REQUEST['friend_id']);
      $to_view    = intval($_REQUEST['to_view']);
      $rt = array();
      $rt = $model->getMemberFriendInfo(array('member_friend.member_id'=>$member_id,'member_friend.friend_id'=>$friend_id),
                                          'member.member_name,member.member_avatar,member.member_truename,member.invitation,member_friend.note_name,member_friend.hint,member.member_sex');
      if(count($rt)<1){
        $rt = $model->getMemberInfo(array('member_id'=>$friend_id),
                                          'member_name,member_avatar,member_truename,invitation,member_sex');
      }

      if($to_view === 1){
        $where  = array('member_id'=>$member_id,'friend_id'=>$friend_id);
        $model->updateMemberFriend($where,array('to_view'=>1));
      }

      $rt_new = array();
      $rt_new['member_name']  = $rt['member_name'];
      $rt_new['member_avatar']  = getMemberAvatar($rt['member_avatar']);
      $rt_new['member_truename']  = $rt['member_truename'];
      $rt_new['invitation']  = $rt['invitation'];
      $rt_new['note_name']  = isset($rt['note_name'])?$rt['note_name']:'';
      $rt_new['is_friend']  = isset($rt['note_name'])?1:0;
      $rt_new['hint']       = isset($rt['hint'])?$rt['hint']:'';
      $rt_new['member_sex']     = $rt['member_sex'];
      switch ($rt['member_sex']) {
        case 2:
          $rt_new['member_sex_ch'] = '女';
          break;
        case 1:
          $rt_new['member_sex_ch'] = '男';
          break; 
        default:
          $rt_new['member_sex_ch'] = '保密';
          break;
      }
      output_data($rt_new);
   }

  /**
   * 设置名称备注
   * @param string $username 邀请人
   * @param string $friend_name 被邀请人
   * @param string $note_name 备注名称
   */
  public function set_note_nameOp(){
    $username     = $_REQUEST['username'];
    $friend_name  = $_REQUEST['friend_name'];
    $note_name    = $_REQUEST['note_name'];
    $this->_exists_param(array($username,$friend_name,$note_name));
    $model  = Model('member');
    $where = array('username'=>$username,'friend_name'=>$friend_name);
    if(count($model->findMemberFriend($where))<1){
      output_error('设置失败', array());
    }

    $update_rt  = $model->updateMemberFriend($where,array('note_name'=>$note_name));
    if($update_rt){
      output_data('设置成功',array());
    }else{
      output_error('设置失败', array());
    }
  }

  /**
   * 好友申请列表
   * @param int $member_id [<description>]
   */
  public function apply_listOp(){
    $model  = Model('member');
    $member_id  = $_REQUEST['member_id'];
    $where  = 'member_friend.state = 0 and member_friend.is_apply=0';
    $idstr  = 'state = 0 and is_apply=0 and member_id ='.$member_id.'';
    $member_id  ||  output_error('参数缺省', array());
    $rt = $model->appleList($where,$idstr,$member_id);
    foreach ($rt as $key => $value) {
      $rt[$key]['member_avatar'] = getMemberAvatar($value['member_avatar']);
    }
    output_data($rt);  
  }

  /**
   * 清空申请好友列表
   * @return [type] [description]
   */
  public function delete_all_applyOp(){
    $username = $_REQUEST['username'];
    $friend_name  = $_REQUEST['friend_names'];
    $model = Model('member');
    $friend_array  = explode(',',$friend_name);
    if( empty($friend_array[0])){
      output_error('参数缺省', array());
    }
    foreach ($friend_array as $key => $value) {
        $this->easemob->deleteFriend($username,$value);
    }
    $where  = 'state != 1 and username='.$username;
    if($model->deleteMemberFriend($where)){
      output_data('清空成功。', array());
    }else{
      output_error('清空失败，稍后再试。', array());
    }
  }

   /**
    * 搜索会员用户
    * @param string $keyword 关键值
    * @return [type] [description]
    */
   public function search_userOp(){
      $model  = Model('member');
      $keyword  = $_REQUEST['keyword'];
      $member_id = $this->member_info['member_id'];
      $keyword  ||  output_error('参数缺省', array());
      $rt = $model->searchUser($keyword,$member_id);
      foreach ($rt as $key => $value) {
        $rt[$key]['member_avatar'] = getMemberAvatar($value['member_avatar']);
      }
      output_data($rt);
   }

   /**
    * 好友中搜索/默认显示全部
    * @param string $keyword 关键值
    * @return [type] [description]
    */
   public function search_friendOp(){
      $model  = Model('member');
      $keyword  = $_REQUEST['keyword'];
      $member_id  = $_REQUEST['member_id'];
      //$keyword  ||  output_error('参数缺省', array());
      $rt = $model->searchFriend($keyword,$member_id);
      foreach ($rt as $key => $value) {
        $rt[$key]['member_avatar'] = getMemberAvatar($value['member_avatar']);
      }
      output_data($rt);
   }

   /**
    * 与手机号码匹配当前好友是否注册过
    * @param  $all_phone 所有联系人号码中间用,隔开
    * @return [type] [description]
    */
   public function all_friendOp(){
   		//echo $this->easemob->getToken();
   		$model = Model('member');
      $new_array  = array();
      $member_name  = $this->member_info['member_name'];
      $ids  = $_REQUEST['all_phone'];
   		$all_phone_array	=	explode(',',$ids);
      $get_member = $model->getMember(array('member_name'=>array('in',$ids),'member_state'=>1),'member_name');
      //$friends  = $model->findFriend(array('username'=>array('in',$ids),'friend_name'=>array('neq',$member_name),'state'=>array('not in',2)),'username');
      $i  = 0;
      $all_phone_array = array_unique($all_phone_array);
      foreach ($all_phone_array as $key => $value) {
          if( ! in_array($get_member[$key]['username'],$all_phone_array)){
              $all_phone_array_new[$i] = $value;
              $i++; 
          }
          
      }

   		if( empty($all_phone_array[0])){
   			output_error('参数缺省', array());
   		}

   		foreach ($all_phone_array_new as $key => $value) {
   				$retrun_array[$key]	=	$model->getMemberInfo(array('member_name'=>$value),'member_id,member_name,member_truename,invitation');
   				$retrun_array[$key]['member_name']	=	$value;
   		}

   		foreach ($retrun_array as $key => $value) {

   			if( ! empty($value['member_id'])){
          $v  = $model->findMemberFriend(array('username'=>$member_name,'friend_name'=>$value['member_name']));
          if(count($v)<1 && $value['member_name'] != $member_name)
   			    $new_array['y'][]	=	$value;
   			}else{
   				$new_array['n'][]	=	$value;
   			}
   		}

   		output_data($new_array);
   }

   /**
    * @param string $username 当前用户名称
    * @param string $friend_name 被邀请人名称
    * 增加好友功能
    */
  public function add_friendOp(){
    $username     = $_REQUEST['username'];
    $friend_name  = $_REQUEST['friend_name'];
    $hint         = $_REQUEST['hint'];
    $this->_exists_param(array($username,$friend_name));

    $hx_return  =  $this->easemob->addFriend($username,$friend_name);
    if(isset($hx_return['error'])){
        output_error('申请好友错误', array());
    }
    $model = Model('member');
    //print_r($hx_return);
    $return = $model->insertMemberFriend(array('username'=>$username,'friend_name'=>$friend_name,'hint'=>$hint));

    if($return == 2){
        output_data('申请好友成功', array());
    }elseif($return){
        output_data('申请好友成功', array()); 
    }else{
        output_error('申请好友错误', array()); 
    }

  }

  /**
   * 删除朋友
   * @return [type] [description]
   */
  public function del_friendOp(){
    $username     = $_REQUEST['username'];
    $friend_name  = $_REQUEST['friend_name'];
    $this->_exists_param(array($username,$friend_name));
    $hx_return  =  $this->easemob->deleteFriend($username,$friend_name);
    if(isset($hx_return['error'])){
        output_error('申请好友错误', array());
    }
    $model = Model('member');
    $model->deleteMemberFriend(array('username'=>$friend_name,'friend_name'=>$username));
    $model->deleteMemberFriend(array('username'=>$username,'friend_name'=>$friend_name));
    output_data('删除好友成功', array());
  }

  /**
   * 设置状态值/同意1，拒绝2
   * @param string $username [<description>]
   * @param string $friend_name [<description>]
   * @param state $state 默认1,2两个值
   */
  public function set_stateOp(){
    $username     = $_REQUEST['username'];
    $friend_name  = $_REQUEST['friend_name'];
    $state        = isset($_REQUEST['state'])?intval($_REQUEST['state']):1;
    if( ! in_array($state,array(1,2))){
        output_error('参数缺省', array());
    }
    $this->_exists_param(array($username,$friend_name));
    $model  = Model('member');
    $model->updateMemberFriend(array('username'=>$friend_name,'friend_name'=>$username,'state'=>array('neq',2)),array('state' => $state));
    $rt = $model->updateMemberFriend(array('username'=>$username,'friend_name'=>$friend_name,'state'=>array('neq',2)),array('state' => $state));
    if($rt){
        output_data('操作成功', array()); 
    }else{
        output_error('参数缺省', array());
        }
  }

  /**
   * 验证用户信息
   * @param  array $data    [description]
   * @return [type]              [description]
   */
  private function _exists_param($data){
    foreach ($data as $key => $value) {
      if( empty($value) || ! isset($value)){
          output_error('参数缺省', array());
        }
    }
        
  }
}
/*----------------------moblie/control/use_easemob.php-------------------------*/
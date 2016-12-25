<?php

/**
 * 会员模型
 *
 *

 */
defined('emall') or exit('Access Invalid!');

class memberModel extends Model {

    public function __construct() {
        parent::__construct('member');
    }

    /**
     * 申请好友
     * @param  string $username 邀请人
     * @param  string $friend_name 被邀请人
     * @return [type]        [description]
     */
    public function insertMemberFriend($param){
        $member_info    =   array();
        $member_info_t  =   array();
        $where['username']      =   $param['username'];
        $where['friend_name']   =   $param['friend_name'];
        $where['state']         =   array('not in',2);
        $member_name    =   $param['username'].','.$param['friend_name'];
        $exist          =   $this->table('member_friend')->where($where)->find();
        $info           =   $this->table('member')->where(array('member_name' => $param['username']))->field('member_id, member_name,invitation,member_truename')->limit(1)->select();
        $infos          =   $this->table('member')->where(array('member_name' => $param['friend_name']))->field('member_id, member_name,invitation,member_truename')->limit(1)->select();
        $member_info    =   array(
                                    array(
                                            'member_id' => $info[0]['member_id'],
                                            'friend_id' => $infos[0]['member_id'],
                                            'username'  => $param['username'],
                                            'friend_name'=> $param['friend_name'],
                                            'create_time'=>  TIMESTAMP,
                                            'is_apply'    =>  1,
                                            'hint'         =>   '',
                                          ),
                                    array(
                                            'member_id' => $infos[0]['member_id'],
                                            'friend_id' => $info[0]['member_id'],
                                            'username'  => $param['friend_name'],
                                            'friend_name'=> $param['username'],
                                            'create_time'=>  TIMESTAMP,
                                            'is_apply'    =>  0,
                                            'hint'    =>  isset($param['hint'])?$param['hint']:$param['username'],
                                        )
                                );
        //$member_info['friend_invitation'] = $info[0]['invitation'];
        //$member_info['friend_truename'] = $info[0]['member_truename'];
        if(count($exist)>0){
            return 2;
        }else{
            return $this->table('member_friend')->insertAll($member_info);
             //$this->table('member_friend')->insert($member_info_t);
        }
    }

    public function findMemberFriend($param){
       return $this->table('member_friend')->where(array('username' =>$param['username'],'friend_name'=>$param['friend_name']))->find();
    }

    public function findFriend($where,$field='*'){
        return $this->field($field)->table('member_friend')->where($where)->distinct(true)->select();
    }
    public function getMember($condition, $field = '*') {
        return $this->table('member')->field($field)->where($condition)->distinct(true)->select();
    }
    public function appleList($where,$idstr,$member_id){
        $rt = $this->table('member_friend')->field('friend_id')->where($idstr)->select();
        if(count($rt)<1)    return array();
        foreach ($rt as $key => $value) {
            $ids .= $value['friend_id'].',';
        }
        $where  = $where.' AND member.member_id in('.rtrim($ids,',').') AND member_friend.member_id='.$member_id;
        $on =   'member.member_id=member_friend.friend_id';
        $select =   $this->table('member_friend,member')
                        ->join('left')
                        ->on($on)
                        ->field('member.member_name,member.invitation,member.member_truename,member.member_avatar,member.member_id,member_friend.to_view,member_friend.create_time,member_friend.hint')
                        ->where($where)
                        ->select();
                        //echo $this->table('member_friend,member')->_sql();
        return $select;
        //return $this->table('member_friend')->where($where)->select();
    }
     /**
     * 删除朋友
     * @param unknown $data
     * @param unknown $condition
     * @return Ambigous <mixed, boolean, number, unknown, resource>
     */
    public function deleteMemberFriend($condition) {
        return $this->table('member_friend')->where($condition)->delete();
    }

    public function updateMemberFriend($condition, $data) {
        $update = $this->table('member_friend')->where($condition)->update($data);
        return $update;
    }

    public function searchUser($condition,$id){
        $wh['member_name|invitation|member_truename']  =   array('like','%'.$condition.'%');
        $wh['member_id'] = array('not in',$id);
        $select =   $this->table('member')->field('member_id,member_name,invitation,member_truename,member_avatar')->where($wh)->order('member_name+0 desc,member_truename+0 desc,invitation+0 desc')->limit(20)->select();
        $i = 0;
        foreach ($select as $key => $value) {
            $where['member_id'] = $value['member_id'];
            $where['friend_id'] =   $id;
            $where['state'] =   array('not in','2');
            $friend_select = $this->table('member_friend')->field('friend_id')->where($where)->find();
            if(count($friend_select)<1){
              $selects[$i]['member_name'] = $value['member_name'];
              $selects[$i]['invitation']  = $value['invitation'];
              $selects[$i]['member_truename']  = $value['member_truename'];
              $selects[$i]['member_avatar']  = $value['member_avatar'];
              $i++;
            }
        }
        return $selects;
    }

    public function searchFriend($condition,$member_id){
        $wh['member.member_name|member.invitation|member.member_truename']  =   array('like','%'.$condition.'%');
        $wh['member_friend.state']  =   1;
        //$wh['member_friend.is_apply']  =   0;
        $wh['member_friend.member_id'] = $member_id;
        $on =   'member.member_id=member_friend.friend_id';
        $select =   $this->table('member_friend,member')
                        ->join('left')
                        ->on($on)
                        ->field('member.member_name,member.invitation,member.member_truename,member.member_avatar,member.member_id,member_friend.to_view,member_friend.hint,member_friend.note_name')
                        ->where($wh)
                        ->select();
                        //echo $this->table('member_friend,member')->_sql();
        return $select;  
    }

    /**
     * 会员详细信息（查库）
     * @param array     $condition
     * @param string    $field
     * @param boolean   $master
     * @param boolean   $lock
     * @return array
     */
    public function getMemberInfo($condition, $field = '*', $master = false, $lock = false) {
        return $this->table('member')->field($field)->where($condition)->master($master)->lock($lock)->find();
    }

    public function getMemberFriendInfo($condition, $field = '*') {
        return $this->table('member_friend,member')->join('left')->on('member.member_id=member_friend.friend_id')->field($field)->where($condition)->find();
    }
    /**
     * 取得会员详细信息（优先查询缓存）
     * 如果未找到，则缓存所有字段
     * @param int $member_id
     * @param string $field 需要取得的缓存键值, 例如：'*','member_name,member_sex'
     * @return array
     */
    public function getMemberInfoByID($member_id, $fields = '*') {
        $member_info = rcache($member_id, 'member', $fields);
        if (empty($member_info)) {
            $member_info = $this->getMemberInfo(array('member_id' => $member_id),'*', true);
            wcache($member_id, $member_info, 'member');
        }
        $result = array();
        if($fields!='*'){
           $field = explode(',', $fields);
           foreach($field as $key=>$row){
               $row = trim($row);
               if(array_key_exists($row,$member_info)){
                   if($member_info[$row]==null) {
                       $result[$row] = '';
                   }else{
                       $result[$row] = $member_info[$row];
                   }
               }
           }
        }else{
           $result = $member_info;
        }
        return $result;
    }

    /**
     * 会员列表
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     */
    public function getMemberList($condition = array(), $field = '*', $page = 0, $order = 'member_id desc', $limit = '') {
        return $this->table('member')->field($field)->where($condition)->page($page)->order($order)->limit($limit)->select();
    }

    /*     * chenyifei
     * 获取会员id与用户名对应数组
     * @param $member_id 数组
     */

    public function getMemberNames($member_id) {
        $data = $this->table('member')->where(array('member_id' => array('IN', $member_id)))->field('member_id, member_name')->select();
        $result = array();
        foreach ($data as $key => $row) {
            $result[$row['member_id']] = $row['member_name'];
        }
        return $result;
    }

    /**
     * lifengli 20150721 当前最后一位会员ID，保证会员邀请码唯一性
     */
    public function getMaxmember() {
        return $this->table('member')->order('member_id desc')->limit(1)->select();
    }

    /**
     * 会员数量
     * @param array $condition
     * @return int
     */
    public function getMemberCount($condition) {
        return $this->table('member')->where($condition)->count();
    }

    /**
     * 编辑会员
     * @param array $condition
     * @param array $data
     */
    public function editMember($condition, $data) {
        $update = $this->table('member')->where($condition)->update($data);
        if ($update && $condition['member_id']) {
            dcache($condition['member_id'], 'member');
        }
        return $update;
    }

    /**
     * 登录时创建会话SESSION
     *
     * @param array $member_info 会员信息
     */
    public function createSession($member_info = array(), $reg = false) {
        if (empty($member_info) || !is_array($member_info))
            return;

        $_SESSION['is_login'] = '1';
        $_SESSION['member_id'] = $member_info['member_id'];
        $_SESSION['member_name'] = $member_info['member_name'];
        $_SESSION['member_email'] = $member_info['member_email'];
        $_SESSION['is_buy'] = isset($member_info['is_buy']) ? $member_info['is_buy'] : 1;
        $_SESSION['avatar'] = $member_info['member_avatar'];
        $_SESSION['member_salt'] = $member_info['member_salt'];
        $seller_info = Model('seller')->getSellerInfo(array('member_id' => $_SESSION['member_id']));
        $_SESSION['store_id'] = $seller_info['store_id'];

        if (trim($member_info['member_qqopenid'])) {
            $_SESSION['openid'] = $member_info['member_qqopenid'];
        }
        if (trim($member_info['member_sinaopenid'])) {
            $_SESSION['slast_key']['uid'] = $member_info['member_sinaopenid'];
        }

        if (!$reg) {
            //添加会员积分
            $this->addPoint($member_info);
            //添加会员经验值
            $this->addExppoint($member_info);
        }

        if (!empty($member_info['member_login_time'])) {
            $update_info = array(
                'member_login_num' => ($member_info['member_login_num'] + 1),
                'member_login_time' => TIMESTAMP,
                'member_old_login_time' => $member_info['member_login_time'],
                'member_login_ip' => getIp(),
                'member_old_login_ip' => $member_info['member_login_ip']
            );
            $this->editMember(array('member_id' => $member_info['member_id']), $update_info);
        }
        setNcCookie('cart_goods_num', '', -3600);
    }

    /**
     * 初始化邀请码数据
     */
    public function inviter_init() {
        $i = 100000;
        $j=$i;
        while($i<1000000) {
            for (; $i < ($j+50000); $i++) {
                $tmp = array();
                $tmp['invitation'] = $i;
                $insert_arr[] = $tmp;
            }
            $result = Db::insertAll('invitation_pool_init', $insert_arr);
            $tmp = null;
            $insert_arr=null;
            echo $i."\n";
            $j=$i;
            var_dump($result);
        }
        return;
    }

    /**
     * 从邀请码数据池中取出一条
     */
    public function getinviter() {
        $rand_nu = rand(0,100);
        $invitation_pool = Model('invitation_pool');
        $has_array = $invitation_pool->limit($rand_nu.',1')->order('id')->select();
        $where = array();
        $where['invitation'] = $has_array[0]['invitation'];
        $invitation_pool->where($where)->delete();
        $member_list = $this->getMemberList($where);
        if (!empty($member_list)) {
            return $this->getinviter();
        } else {
            return $has_array[0]['invitation'];
        }
    }

    /**
     * 获取会员信息
     *
     * @param	array $param 会员条件
     * @param	string $field 显示字段
     * @return	array 数组格式的返回结果
     */
    public function infoMember($param, $field = '*') {
        if (empty($param))
            return false;

        //得到条件语句
        $condition_str = $this->getCondition($param);
        $param = array();
        $param['table'] = 'member';
        $param['where'] = $condition_str;
        $param['field'] = $field;
        $param['limit'] = 1;
        $member_list = Db::select($param);
        $member_info = $member_list[0];
        if (intval($member_info['store_id']) > 0) {
            $param = array();
            $param['table'] = 'store';
            $param['field'] = 'store_id';
            $param['value'] = $member_info['store_id'];
            $field = 'store_id,store_name,grade_id';
            $store_info = Db::getRow($param, $field);
            if (!empty($store_info) && is_array($store_info)) {
                $member_info['store_name'] = $store_info['store_name'];
                $member_info['grade_id'] = $store_info['grade_id'];
            }
        }
        return $member_info;
    }

    /**
     * 注册
     */
    public function register($register_info) {
        // 注册验证
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
            array("input" => $register_info["username"], "require" => "true", "message" => '用户名不能为空'),
            array("input" => $register_info["password"], "require" => "true", "message" => '密码不能为空'),
            array("input" => $register_info["password_confirm"], "require" => "true", "validator" => "Compare", "operator" => "==", "to" => $register_info["password"], "message" => '密码与确认密码不相同'),
            array("input" => $register_info["email"], "require" => "true", "validator" => "email", "message" => '电子邮件格式不正确'),
        );
        $error = $obj_validate->validate();
        if ($error != '') {
            return array('error' => $error);
        }

        // 验证用户名是否重复
        $check_member_name = $this->getMemberInfo(array('member_name' => $register_info['username']));
        if (is_array($check_member_name) and count($check_member_name) > 0) {
            return array('error' => '用户名已存在');
        }

        // 验证邮箱是否重复
        $check_member_email = $this->getMemberInfo(array('member_email' => $register_info['email']));
        if (is_array($check_member_email) and count($check_member_email) > 0) {
            return array('error' => '邮箱已存在');
        }
        // 会员添加
        $member_info = array();
        $member_info['member_name'] = $register_info['username'];
        $member_info['member_passwd'] = $register_info['password'];
        $member_info['member_email'] = $register_info['email'];
        $member_info['firest_inviter'] = $register_info['firest_inviter'];
        $member_info['second_inviter'] = $register_info['second_inviter'];
        //lifengli edit 20150721 add
        $maxmember = $this->getinviter();
        $member_info['max_member'] = $maxmember;
        //lifengli edit 20150721 end
        $insert_id = $this->addMember($member_info);
        if ($insert_id) {
            //添加会员积分
            if (C('points_isuse')) {
                Model('points')->savePointsLog('regist', array('pl_memberid' => $insert_id, 'pl_membername' => $register_info['username']), false);
            }

            // 添加默认相册
            $insert['ac_name'] = '买家秀';
            $insert['member_id'] = $insert_id;
            $insert['ac_des'] = '买家秀默认相册';
            $insert['ac_sort'] = 1;
            $insert['is_default'] = 1;
            $insert['upload_time'] = TIMESTAMP;
            $this->table('sns_albumclass')->insert($insert);

            $member_info['member_id'] = $insert_id;
            $member_info['is_buy'] = 1;

            return $member_info;
        } else {
            return array('error' => '注册失败');
        }
    }

    /**
     * 手机注册新用户
     * 
     * @author lijunhua
     * @since  2015-08-06
     */
    public function register_mobile($register_info) {
        // 注册验证
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(
            array("input" => $register_info["mobile"], "require" => "true", "validator" => "mobile", "message" => '手机格式不正确'),
            array("input" => $register_info["password"], "require" => "true", "message" => '密码不能为空'),
            array("input" => $register_info["password_confirm"], "require" => "true", "validator" => "Compare", "operator" => "==", "to" => $register_info["password"], "message" => '密码与确认密码不相同'),
        );

        $error = $obj_validate->validate();
        if ($error != '') {
            return array('error' => $error);
        }

        // 会员添加
        $member_info = array();
        $member_info['member_id'] = null;
        $member_info['member_name'] = $register_info['mobile'];
        $member_info['member_passwd'] = $register_info['password'];
        $member_info['member_truename'] = $register_info['nickname'];
        $member_info['member_mobile'] = $register_info['mobile'];
        $member_info['member_sex'] = $register_info['member_sex'];
        $member_info['member_mobile_bind'] = 1;
        $member_info['member_email'] = '';
        $member_info['max_member'] = $this->getinviter();
        $inv_info = $this->getInviterByInvitation($register_info['code']);
        $member_info['firest_inviter'] = $inv_info['firest_inviter'];
        $member_info['second_inviter'] = $inv_info['second_inviter'];

        $member_info['register_from']   =   $register_info['register_from'];

        $insert_id = $this->addMember($member_info);
        if (!$insert_id) {
            return array('error' => '注册失败');
        }

        //添加会员积分
        if (C('points_isuse')) {
            Model('points')->savePointsLog('regist', array('pl_memberid' => $insert_id, 'pl_membername' => $register_info['mobile']), false);
        }

        // 添加默认相册
        $insert['ac_name'] = '买家秀';
        $insert['member_id'] = $insert_id;
        $insert['ac_des'] = '买家秀默认相册';
        $insert['ac_sort'] = 1;
        $insert['is_default'] = 1;
        $insert['upload_time'] = TIMESTAMP;
        $this->table('sns_albumclass')->insert($insert);

        $member_info['member_id'] = $insert_id;
        $member_info['is_buy'] = 1;

        return $member_info;
    }

    /**
     * 注册商城会员
     *
     * @param	array $param 会员信息
     * @return	array 数组格式的返回结果
     */
    public function addMember($param) {
        if (empty($param)) {
            return false;
        }
        try {
            $this->beginTransaction();
            
            // 验证用户名是否重复
            $check_member_name = $this->getMemberInfo(array('member_name' =>  $param['member_name']));
            if (is_array($check_member_name) and count($check_member_name) > 0) {
                $this->rollback();
                return array('error' => '手机号码已存在');
            }

            // 验证手机号是否重复
            $check_member_email = $this->getMemberInfo(array('member_mobile' => $param['member_mobile']));
            if (is_array($check_member_email) and count($check_member_email) > 0) {
                $this->rollback();
                return array('error' => '手机号码已存在');
            }

            $member_info = array();
            $member_info['member_id'] = $param['member_id'];
            $member_info['member_name'] = $param['member_name'];
            $member_info['member_email'] = $param['member_email'];
            $member_info['member_time'] = TIMESTAMP;
            $member_info['member_login_time'] = TIMESTAMP;
            $member_info['member_old_login_time'] = TIMESTAMP;
            $member_info['member_login_ip'] = getIp();
            $member_info['member_old_login_ip'] = $member_info['member_login_ip'];

            // 密码处理
            $salt = random_str(8, FALSE);
            $member_info['member_salt'] = $salt;
            $member_info['member_passwd'] = md6(trim($param['member_passwd']), $salt);

            // 手机号码入库处理 兼容
            if (isset($param['member_mobile']) && !empty($param['member_mobile'])) {
                $member_info['member_mobile'] = $param['member_mobile'];
                $member_info['member_mobile_bind'] = $param['member_mobile_bind'];
            } else {
                if (preg_match('/^1\d{10}$/', $param['member_name'])) {
                    $member_info['member_mobile'] = $param['member_name'];
                    $member_info['member_mobile_bind'] = 1;
                }
            }

            $member_info['member_truename'] = $param['member_truename'];
            $member_info['member_qq'] = $param['member_qq'];
            $member_info['member_sex'] = $param['member_sex'];
            $member_info['member_avatar'] = $param['member_avatar'];
            $member_info['member_qqopenid'] = $param['member_qqopenid'];
            $member_info['member_qqinfo'] = $param['member_qqinfo'];
            $member_info['member_sinaopenid'] = $param['member_sinaopenid'];
            $member_info['member_sinainfo'] = $param['member_sinainfo'];
            //lifengli edit 20150721  start

            $member_info['invitation'] = $param['max_member']; //会员邀请码
            $member_info['firest_inviter'] = $param['firest_inviter'];
            $member_info['second_inviter'] = $param['second_inviter'];
            if(isset($param['register_from']) && !empty($param['register_from'])) {
                $member_info['register_from']   =   $param['register_from'];
            }
            //lifengli edit 20150721 end
            $insert_id = $this->table('member')->insert($member_info);
            if (!$insert_id) {
                throw new Exception();
            }

            //注册环信 solon.ring2011@gmail.com----
            $easemob = new Easemob();
            $rt = $easemob->registerToken($member_info['member_name'],$member_info['member_passwd'],$member_info['member_truename']); 
            if( ! isset($rt['entities'])){
                $hx_status = 1;
            }else{
                $hx_status = 0;
            }
            //----end

            $insert = $this->addMemberCommon(array('member_id' => $insert_id,'member_hx_status' => $hx_status));
            if (!$insert) {
                throw new Exception();
            }
            $this->commit();
            return $insert_id;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * 会员登录检查
     *
     */
    public function checkloginMember() {
        if ($_SESSION['is_login'] == '1') {
            @header("Location: index.php");
            exit();
        }
    }

    /**
     * 检查会员是否允许举报商品
     *
     */
    public function isMemberAllowInform($member_id) {
        $condition = array();
        $condition['member_id'] = $member_id;
        $member_info = $this->getMemberInfo($condition, 'inform_allow');
        if (intval($member_info['inform_allow']) === 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 插入用户密保问题
     * @param unknown $data
     * @return Ambigous <mixed, boolean, number, unknown, resource>
     */
    public function addMemberSecurityQuestion($data) {
        return $this->table('member_security')->insert($data);
    }

    /**
     * 修改用户密保问题
     * @param unknown $data
     * @return Ambigous <mixed, boolean, number, unknown, resource>
     */
    public function editMemberSecurityQuestion($condition = array(),$data) {
        return $this->table('member_security')->where($condition)->update($data);
    }

    /**
     * 取用户密保问题
     * @param unknown $condition
     * @param string $fields
     */
    public function getMemberSecurityQuestion($condition = array(), $fields = '*') {
        return $this->table('member_security')->where($condition)->field($fields)->find();
    }

    /**
     * 取单条信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getMemberCommonInfo($condition = array(), $fields = '*') {
        return $this->table('member_common')->where($condition)->field($fields)->find();
    }

    /**
     * 插入扩展表信息
     * @param unknown $data
     * @return Ambigous <mixed, boolean, number, unknown, resource>
     */
    public function addMemberCommon($data) {
        return $this->table('member_common')->insert($data);
    }

    /**
     * 编辑会员扩展表
     * @param unknown $data
     * @param unknown $condition
     * @return Ambigous <mixed, boolean, number, unknown, resource>
     */
    public function editMemberCommon($data, $condition) {
        return $this->table('member_common')->where($condition)->update($data);
    }

    /**
     * 添加会员积分
     * @param unknown $member_info
     */
    public function addPoint($member_info) {
        if (!C('points_isuse') || empty($member_info))
            return;

        //一天内只有第一次登录赠送积分
        if (trim(@date('Y-m-d', $member_info['member_login_time'])) == trim(date('Y-m-d')))
            return;

        //加入队列
        $queue_content = array();
        $queue_content['member_id'] = $member_info['member_id'];
        $queue_content['member_name'] = $member_info['member_name'];
        QueueClient::push('addPoint', $queue_content);
    }

    /**
     * 添加会员经验值
     * @param unknown $member_info
     */
    public function addExppoint($member_info) {
        if (empty($member_info))
            return;

        //一天内只有第一次登录赠送经验值
        if (trim(@date('Y-m-d', $member_info['member_login_time'])) == trim(date('Y-m-d')))
            return;

        //加入队列
        $queue_content = array();
        $queue_content['member_id'] = $member_info['member_id'];
        $queue_content['member_name'] = $member_info['member_name'];
        QueueClient::push('addExppoint', $queue_content);
    }

    /**
     * 取得会员安全级别
     * @param unknown $member_info
     */
    public function getMemberSecurityLevel($member_info = array()) {
        $tmp_level = 0;
        if ($member_info['member_email_bind'] == '1') {
            $tmp_level += 1;
        }
        if ($member_info['member_mobile_bind'] == '1') {
            $tmp_level += 1;
        }
        if ($member_info['member_paypwd'] != '') {
            $tmp_level += 1;
        }
        return $tmp_level;
    }

    /**
     * 获得会员等级
     * @param bool $show_progress 是否计算其当前等级进度
     * @param int $exppoints  会员经验值
     * @param array $cur_level 会员当前等级
     */
    public function getMemberGradeArr($show_progress = false, $exppoints = 0, $cur_level = '') {
        $member_grade = C('member_grade') ? unserialize(C('member_grade')) : array();
        //处理会员等级进度
        if ($member_grade && $show_progress) {
            $is_max = false;
            if ($cur_level === '') {
                $cur_gradearr = $this->getOneMemberGrade($exppoints, false, $member_grade);
                $cur_level = $cur_gradearr['level'];
            }
            foreach ($member_grade as $k => $v) {
                if ($cur_level == $v['level']) {
                    $v['is_cur'] = true;
                }
                $member_grade[$k] = $v;
            }
        }
        return $member_grade;
    }

    /**
     * 将条件数组组合为SQL语句的条件部分
     *
     * @param	array $conditon_array
     * @return	string
     */
    private function getCondition($conditon_array) {
        $condition_sql = '';
        if ($conditon_array['member_id'] != '') {
            $condition_sql .= " and member_id= '" . intval($conditon_array['member_id']) . "'";
        }
        if ($conditon_array['member_name'] != '') {
            $condition_sql .= " and member_name='" . $conditon_array['member_name'] . "'";
        }
        ///lifengli 20150723 add
        if ($conditon_array['invitation'] != '') {
            $condition_sql .= " and invitation='" . $conditon_array['invitation'] . "'";
        }
        ///lifengli 20150723 end
        if ($conditon_array['member_passwd'] != '') {
            $condition_sql .= " and member_passwd='" . $conditon_array['member_passwd'] . "'";
        }
        //是否允许举报
        if ($conditon_array['inform_allow'] != '') {
            $condition_sql .= " and inform_allow='{$conditon_array['inform_allow']}'";
        }
        //是否允许购买
        if ($conditon_array['is_buy'] != '') {
            $condition_sql .= " and is_buy='{$conditon_array['is_buy']}'";
        }
        //是否允许发言
        if ($conditon_array['is_allowtalk'] != '') {
            $condition_sql .= " and is_allowtalk='{$conditon_array['is_allowtalk']}'";
        }
        //是否允许登录
        if ($conditon_array['member_state'] != '') {
            $condition_sql .= " and member_state='{$conditon_array['member_state']}'";
        }
        if ($conditon_array['friend_list'] != '') {
            $condition_sql .= " and member_name IN (" . $conditon_array['friend_list'] . ")";
        }
        if ($conditon_array['member_email'] != '') {
            $condition_sql .= " and member_email='" . $conditon_array['member_email'] . "'";
        }
        if ($conditon_array['no_member_id'] != '') {
            $condition_sql .= " and member_id != '" . $conditon_array['no_member_id'] . "'";
        }
        if ($conditon_array['like_member_name'] != '') {
            $condition_sql .= " and member_name like '%" . $conditon_array['like_member_name'] . "%'";
        }
        if ($conditon_array['like_member_email'] != '') {
            $condition_sql .= " and member_email like '%" . $conditon_array['like_member_email'] . "%'";
        }
        if ($conditon_array['like_member_truename'] != '') {
            $condition_sql .= " and member_truename like '%" . $conditon_array['like_member_truename'] . "%'";
        }
        if ($conditon_array['in_member_id'] != '') {
            $condition_sql .= " and member_id IN (" . $conditon_array['in_member_id'] . ")";
        }
        if ($conditon_array['in_member_name'] != '') {
            $condition_sql .= " and member_name IN (" . $conditon_array['in_member_name'] . ")";
        }
        if ($conditon_array['member_qqopenid'] != '') {
            $condition_sql .= " and member_qqopenid = '{$conditon_array['member_qqopenid']}'";
        }
        if ($conditon_array['member_sinaopenid'] != '') {
            $condition_sql .= " and member_sinaopenid = '{$conditon_array['member_sinaopenid']}'";
        }

        return $condition_sql;
    }

    /**
     * 获得某一会员等级
     * @param int $exppoints
     * @param bool $show_progress 是否计算其当前等级进度
     * @param array $member_grade 会员等级
     */
    public function getOneMemberGrade($exppoints, $show_progress = false, $member_grade = array()) {
        if (!$member_grade) {
            $member_grade = C('member_grade') ? unserialize(C('member_grade')) : array();
        }
        if (empty($member_grade)) {//如果会员等级设置为空
            $grade_arr['level'] = -1;
            $grade_arr['level_name'] = '暂无等级';
            return $grade_arr;
        }

        $exppoints = intval($exppoints);

        $grade_arr = array();
        if ($member_grade) {
            foreach ($member_grade as $k => $v) {
                if ($exppoints >= $v['exppoints']) {
                    $grade_arr = $v;
                }
            }
        }
        //计算提升进度
        if ($show_progress == true) {
            if (intval($grade_arr['level']) >= (count($member_grade) - 1)) {//如果已达到顶级会员
                $grade_arr['downgrade'] = $grade_arr['level'] - 1; //下一级会员等级
                $grade_arr['downgrade_name'] = $member_grade[$grade_arr['downgrade']]['level_name'];
                $grade_arr['downgrade_exppoints'] = $member_grade[$grade_arr['downgrade']]['exppoints'];
                $grade_arr['upgrade'] = $grade_arr['level']; //上一级会员等级
                $grade_arr['upgrade_name'] = $member_grade[$grade_arr['upgrade']]['level_name'];
                $grade_arr['upgrade_exppoints'] = $member_grade[$grade_arr['upgrade']]['exppoints'];
                $grade_arr['less_exppoints'] = 0;
                $grade_arr['exppoints_rate'] = 100;
            } else {
                $grade_arr['downgrade'] = $grade_arr['level']; //下一级会员等级
                $grade_arr['downgrade_name'] = $member_grade[$grade_arr['downgrade']]['level_name'];
                $grade_arr['downgrade_exppoints'] = $member_grade[$grade_arr['downgrade']]['exppoints'];
                $grade_arr['upgrade'] = $member_grade[$grade_arr['level'] + 1]['level']; //上一级会员等级
                $grade_arr['upgrade_name'] = $member_grade[$grade_arr['upgrade']]['level_name'];
                $grade_arr['upgrade_exppoints'] = $member_grade[$grade_arr['upgrade']]['exppoints'];
                $grade_arr['less_exppoints'] = $grade_arr['upgrade_exppoints'] - $exppoints;
                $grade_arr['exppoints_rate'] = round(($exppoints - $member_grade[$grade_arr['level']]['exppoints']) / ($grade_arr['upgrade_exppoints'] - $member_grade[$grade_arr['level']]['exppoints']) * 100, 2);
            }
        }
        return $grade_arr;
    }

    /**
     * 按照邀请码获取1级邀请人、2级邀请人
     * 
     * @author lijunhua
     * @since  2015-08-06
     */
    public function getInviterByInvitation($invitation = '') {
        $rs['firest_inviter'] = 0;
        $rs['second_inviter'] = 0;
        if (!$invitation || $invitation == '00000000') {
            return $rs;
        }

        $member_info = $this->getMemberInfo(array('invitation' => $invitation));
        if (!$member_info) {
            return $rs;
        }
        // 一级邀请人
        $rs['firest_inviter'] = $member_info['member_id'];
        //二级邀请人
        $rs['second_inviter'] = $member_info['firest_inviter'];

        return $rs;
    }

    /**
     * 登录时，设置registration_id
     * 
     * @param int $member_id
     * @param string $registration_id
     * @return boolean
     */
    public function set_registration_id($member_id, $registration_id) {
        $data = array('registration_id' => $registration_id, 'member_id' => array('neq', $member_id));
        $rs = $this->table('member_common')->where($data)->find();

        if (!empty($rs)) {
            $this->table('member_common')->where(array('member_id' => (int) $rs['member_id']))->update(array('registration_id' => ''));
        }
        $rs = $this->table('member_common')->where(array('member_id' => $member_id))->update($data);
        return $rs;
    }
    
    /**
     * /获取是否实名
     * @param  [type] $where [description]
     * @return [type]        [description]
     */
    public function get_member_common($where){
        $rs = $this->table('member_common')->where($where)->find();
        return $rs; 
    }

    /**
     * 限制交易密码次数 
     * 
     * @param array $member_info
     * @param int   $password
     */
    public function limit_input_paypwd_count($member_info, $password){
        if (C('cache_open')) {
            $data = rcache($member_info['member_id'], 'member_paypwd_count', $fields = '*');
        } else {
           $model_paypwd_log = Model()->table('member_paypwd_log');
           $data = $model_paypwd_log->where(array('member_id' => $member_info['member_id']))->find();
           if (!empty($data)) {
                if ($data['create_time'] + PAYPWD_LIMIT_TIME < time()) { //过期删除
                    $model_paypwd_log->where(array('member_id' => $member_info['member_id']))->delete();
                    $data = array();
                }
           }
        }
        
        $limit_time = PAYPWD_LIMIT_TIME/60; //缓存是按照分钟来的

         ++$data['count']; //自增一次

        // 超过限制次数
        if ( $data['count'] > PAYPWD_LIMIT_COUNT) {
            $extend_data = array('count' => PAYPWD_LIMIT_COUNT, 'expiration_time' => $data['cache_expiration_time']?$data['cache_expiration_time']:$data['create_time']);
            $msg         = '交易密码错误次数已超过' . PAYPWD_LIMIT_COUNT . '次,您将在' . PAYPWD_LIMIT_TIME_TIP . '之内不能使用积分支付';
            return array('state' => false, 'msg' => $msg,  'data' => $extend_data);
        }

        // 交易密码输入正确 滤掉
        if ($member_info['member_paypwd'] == md6($password, $member_info['member_salt'])) {    
            if (!empty($data)) {
                if (C('cache_open')) {
                    dcache($member_info['member_id'], 'member_paypwd_count');
                } else {
                    $model_paypwd_log->where(array('member_id' => $member_info['member_id']))->delete();
                }
            }
            return array('state' => true, 'msg' => 'OK');  
        }

        // 单位时间内, 首次错误,记录缓存
        if ($data['count'] == 1) {
            if (C('cache_open')) {
                wcache($member_info['member_id'], array('count' => 1), 'member_paypwd_count', $limit_time);
            } else {
                $model_paypwd_log->insert(array(
                    'member_id'   => $member_info['member_id'],
                    'count'       => 1,
                    'create_time' => time(),
                ));
            }
             $less_num = PAYPWD_LIMIT_COUNT - 1;
             $msg      = '交易密码错误, 还有' . $less_num   . '次机会';
             return array('state' => false, 'msg' => $msg,  'data' => array('count' => 1));
        }

        // 显示次数范围内
        if ($data['count'] <= PAYPWD_LIMIT_COUNT) {
            if (C('cache_open')) {
                wcache($member_info['member_id'], array('count' => $data['count']), 'member_paypwd_count', $limit_time);
            } else {
                $up_log = array('count' => $data['count'], 'create_time' => time());
                $model_paypwd_log->where(array('member_id' => $member_info['member_id']))->update($up_log);
            }
            $less_num = PAYPWD_LIMIT_COUNT - $data['count'];
            $msg      = '交易密码错误, 还有' . $less_num . '次机会';
            return array('state' => false, 'msg' => $msg,  'data' => array('count' => $data['count']));
        }
       
    }

    /**
     * 随机获取一条会员信息
     * @return array
     */
    public function getRandomMemberInfo($condition=array()){
        return $this->table('member')->where($condition)->order('rand()')->find();
;
    }

}

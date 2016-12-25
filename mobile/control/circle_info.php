
<?php
//use Tpl;

defined('emall') or exit('Access Invalid!');
class circle_infoControl extends mobileMemberControl
{

    public function __construct()
    {

        parent::__construct();

    }

    /*获取到 自己发过那些帖子
    2015年9月21日14:52:27
    xuping
    */
    public function  getuserThemeOP(){
        $user_id=$this->member_info['member_id'];
        if(empty($user_id)){
            echo json_encode(array('code'=>ERROR_CODE_OPERATE , 'message'=>'参数为空'  ));
            exit;
        }
        $model=Model();
        $info=$model->table('circle_theme')->where(array('member_id'=>$user_id,'is_closed'=>0))->order('theme_addtime  DESC')->select();
        foreach ($info as $key => $value) {
            $info[$key]['theme_pic']=getMemberAvatar($value['theme_pic']);
        }
        if(is_array($info) && !empty($info)){
            output_data($info);
        }else{
            output_data(array());
        }
    }

    /** * 点赞接口
    2015年9月10日10:50:11
    xuping
     */
    public function likeOP(){
        $theme_id=$_REQUEST['theme_id'];
        $member_id=$this->member_info['member_id'];
        if( empty($theme_id) || empty($member_id)){
            output_error('对不起， 请指定对应的帖子');
        }
        $like_info = Model()->table('circle_like')->where(array('theme_id'=>$theme_id, 'member_id'=>$member_id))->find();


        /*没有点赞过*/
        if(empty($like_info)){
            // 插入话题赞表
            Model()->table('circle_like')->insert(array('theme_id'=>$theme_id ,'member_id'=>$member_id ,'addtime'=>time() ));
            // 更新赞数量
            $res=Model()->table('circle_like')->where(array('theme_id'=>$theme_id))->select();
            $count=(string)count($res);
            foreach ($res as $key => $value) {
                $arr[]=Model('member')->field('member_id , member_avatar')->where(array('member_id'=>$value['member_id']))->find();
            }
            foreach ($arr as $k => $v) {
                $arr[$k]['avatar']=getMemberAvatar($v['member_avatar']);
                $arr[$k]['member_id']=$v['member_id'];
            }
            $arr=array_reverse($arr);
            Model()->table('circle_theme')->update(array('theme_id'=>$theme_id, 'theme_likecount'=>array('exp', 'theme_likecount+1')));
            output_data(array('status'=>'1','like'=>$arr,'count'=>$count,'theme_id'=>$theme_id));
        }else{
            // 删除话题赞表信息 $model->where(array('link_id'=>2))->setDec('link_sort',3)
            Model()->table('circle_like')->where(array('theme_id'=>$theme_id, 'member_id'=>$member_id))->delete();
            // 更新赞数量
            Model()->table('circle_theme')->where(array('theme_id'=>$theme_id))->setDec('theme_likecount',1);
            $res=Model()->table('circle_like')->where(array('theme_id'=>$theme_id))->select();
            $count=(string)count($res);

            if(empty($res)){
                output_data(array('status'=>'0','like'=>array(),'count'=>"0",'theme_id'=>$theme_id));
            }
            foreach ($res as $key => $value) {
                $arr[]=Model('member')->field('member_id , member_avatar')->where(array('member_id'=>$value['member_id']))->find();
            }
            foreach ($arr as $k => $v) {
                $arr[$k]['avatar']=getMemberAvatar($v['member_avatar']);
                $arr[$k]['member_id']=$v['member_id'];
            }
            $arr=array_reverse($arr);

            $count_like=Model()->table('circle_theme')->where(array('theme_id'=>$theme_id))->find();
            if($count_like['theme_likecount'] <= 0){
                output_error('数据有误');
            }
            //Model()->table('circle_theme')->update(array('theme_id'=>$theme_id, 'theme_likecount'=>array('exp', 'theme_likecount-1')));

            output_data(array('status'=>'0','like'=>$arr,'count'=>$count,'theme_id'=>$theme_id));
        }
    }

    /**
     * xuping
     * 2015年9月10日10:30:22
     * 加关注
     */
    public function FollowMemberOp() {
        $friend_id    = intval($_REQUEST['friend_id']);    //即将关注人的id
        $user_id      = $this->member_info['member_id'];      //自己的id

        if($friend_id <=0 || $user_id <=0  ){
            output_error('对不起，系统参数有误');
        }
        if($friend_id == $user_id ){
            output_error('对不起，自己不能关注自己');
        }
        //验证会员信息
        $member_model = Model('member');
        $friend_count = Model()->table('sns_friend')->where(array('friend_frommid'=>$user_id,'friend_tomid'=>$friend_id))->find();
        /*如果已经关注过 对方， 则此时直接删除关注状态 */
        if(!empty($friend_count) ){
            //取消关注
            Model()->table('sns_friend')->where(array('friend_frommid'=>$user_id,'friend_tomid'=>$friend_id))->delete();
            //更新对方的关注状态
            $res = Model()->table('sns_friend')->where(array('friend_frommid'=>$friend_id,'friend_tomid'=>$user_id))->find();
            if(!empty($res)){
                $data=array('friend_followstate'=> '1' );
                Model()->table('sns_friend')->where(array('friend_id' =>$res['friend_id'] ))->update($data);
            }
            output_data(array('status'=>'0','message'=>'取消关注成功'));
        }
        //查询对方是否已经关注我，从而判断关注状态
        $friend_info =Model()->table('sns_friend')->where(array('friend_frommid'=>$friend_id,'friend_tomid'=>$user_id))->find();
        $insert_arr = array();
        $insert_arr['friend_frommid']   = $user_id;
        $insert_arr['friend_tomid']     = $friend_id;

        $insert_arr['friend_addtime']   = time();
        if(!empty($friend_info)){
            $insert_arr['friend_followstate'] = '2';  //单方关注
        }else{
            $insert_arr['friend_followstate'] = '1';  //双方关注
        }

        $result = Model()->table('sns_friend')->insert($insert_arr);
        if($result){
            //更新对方关注状态 //如果之前对方已经是关注过我，此时应该是 双向关注 、 否则 单项关注
            $update_arr['friend_followstate'] = '2';
            Model()->table('sns_friend')->where(array('friend_frommid'=>$friend_id,'friend_tomid'=>$user_id))->update($update_arr);

            if(!empty($friend_info)){
                output_data(array('status'=>'2','message'=>'关注成功'));
            }
            output_data(array('status'=>'1','message'=>'关注成功'));
        }else{
            output_error('关注失败');
        }
    }

    /*获取关注的帖子   即： 关注的人 发的帖子和自己发的帖子
   author；xuping
   time:2015年7月24日15:41:27
   tool：sublime
    */
    public function getAllThemeOP(){
        $user_id=$this->member_info['member_id'];
        $user=array();
        $result=Model()->table('sns_friend')->where(array('friend_frommid'=>$user_id))->field('friend_tomid')->select();
        foreach ($result as $key => $value) {
            $user[]=$value['friend_tomid'];
        }
        $user[]=$user_id; //自己也添加进去 (array('brand_id' => array('in', $brandid_array)));
        $all_theme=Model('circle_theme')->where(array('member_id'=>array('in',$user), 'is_closed'=>0 ) )->order('theme_addtime DESC')->select();
        if(!empty($all_theme)){  //获取评论和 评论人的信息
            $size=10;
            $page=isset($_REQUEST['curpage'])?  $_REQUEST['curpage'] : '1';
            $res=(intval($page)-1) * $size;
            $theme_content=Model()->table('circle_theme')->where(array('member_id'=>array('in',$user) , 'is_closed'=>0 ))->order('theme_addtime DESC')->limit(" $res ,$size" )->select();
            $circle_threply=Model('circle_threply');
            $circle_tag=Model('circle_tag');
            $circle_like=Model('circle_like');
            $member=Model('member');
            foreach ($theme_content as $key => $value) {
                /*处理增加时间 显示发布时间的格式化*/
                //$avatar=$member->where(array('member_id'=>$value['member_id']))->find();
                $avatar=$member->getMemberInfoByID($value['member_id'],'member_avatar');
                $theme_content[$key]['member_avatar']=getMemberAvatar($avatar['member_avatar']);
                unset($avatar);
                $time=time()-$value['theme_addtime'];
                $theme_content[$key]['theme_addtime']=$value['theme_addtime'];
                $res=$circle_threply->where(array('theme_id'=>$value['theme_id']))->order('reply_addtime DESC ')->limit(10)->select();
                foreach ($res as $k => $v) {
                    //$avatar=$member->where(array('member_id'=>$v['member_id']))->field('member_avatar')->find();
                    $avatar=$member->getMemberInfoByID($v['member_id'],'member_avatar');
                    $res[$k]['avatar']=getMemberAvatar($avatar['member_avatar']);
                    //$rtime=time()-$v['reply_addtime'];
                    $res[$k]['reply_addtime']=$v['reply_addtime'];
                }
                $fag=0;
                $res_like=$circle_like->where(array('theme_id'=>$value['theme_id']))->select();
                if(!empty($res_like)){
                    foreach ($res_like as $kkk => $vvv) {
                        $like_avatar=$member->where(array('member_id'=>$vvv['member_id']))->field('member_avatar')->find();
                        $res_like[$kkk]['avatar']=getMemberAvatar($like_avatar['member_avatar']);
                        if($vvv['member_id']==$this->member_info['member_id']){
                            $fag=1;
                            break;
                        }else{
                            $fag=0;
                        }
                    }
                    $res_like=array_reverse($res_like);
                }

                if($fag==1){
                    $theme_content[$key]['member_islike']=1;
                }else{
                    $theme_content[$key]['member_islike']=0;
                }


                $theme_content[$key]['member_isfriend']=1;
                if($user_id==$value['member_id']){
                    $theme_content[$key]['is_my']=1;
                    $theme_content[$key]['member_isfriend']=0;
                }else{
                    $theme_content[$key]['is_my']=0;
                }

                $tag=$circle_tag->where(array('theme_id'=>$value['theme_id']))->select();
                $theme_content[$key]['tag']=$tag;
                $theme_content[$key]['theme_pic']=getMemberAvatar($value['theme_pic']);
                $theme_content[$key]['theme_reply']=$res;
                $theme_content[$key]['like']=$res_like;
                $theme_content[$key]['url']=WAP_SITE_URL.'/share_details.html?theme_id='.$value['theme_id'];

                /*关注状态*/
                // $theme_content[$key]['member_isfriend']=1； wap/share_details.html?theme_id=
                /*修改用户名称  === 用户昵称  public function getMemberInfoByID($member_id, $fields = '*') { */
                // $theme_content[$key]['member_name']=Model('member')->getMemberInfoByID($condition[$key]['member_id'],'member_truename');
                $member_name=Model('member')->getMemberInfoByID($value['member_id'],'member_truename');
                $theme_content[$key]['member_name']=$member_name['member_truename'];
            }
            if(!empty($theme_content)){
                output_data($theme_content);
            }else{
                output_data(array());
            }

        }else{
            output_data(array());
        }
    }

    private function _get_ext($field)
    {
        $tmp_ext = explode(".", $_FILES[$field]['name']);
        $tmp_ext = $tmp_ext[count($tmp_ext) - 1];
        return  strtolower($tmp_ext);
    }

    /*发帖子author；xuping
    time:2015年7月24日9:30:27
    tool：sublime
     */
    public function addThemeOP(){
        $insert = array();
        $model=Model();
        $insert['theme_content']= $_REQUEST['theme_content'];
        $insert['member_id']    = $this->member_info['member_id'];
        $insert['member_name']  = $this->member_info['member_truename'];
        $insert['is_identity']  = isset($_REQUEST['is_identity']) ? $_REQUEST['is_identity'] : 3;
        $insert['theme_addtime'] = time();
        $insert['lastspeak_time']= time();
        $insert['theme_readperm']= 0;
        $insert['theme_special']= isset($_REQUEST['sp']) ? $_REQUEST['sp'] : 0 ;
        //$insert['good_id']      =isset($_REQUEST['good_id']) ? $_REQUEST['good_id'] : '';
        //上传图片
        $time=time();
        $upload = new UploadFile();
        $dir = ATTACH_AVATAR . DS;
        $upload->set('default_dir', $dir);
        $field='theme_pic';
        $upload->set('file_name', 'theme_' . $this->member_info['member_id']. '_' . $time  . '.' . $this->_get_ext($field));
        $upload->set('thumb_width', 120);
        $upload->set('thumb_height', 120);
        $upload->set('thumb_ext', '_120x120');
        $upload->set('ifremove', true);

        $result_file = $upload->upfile('theme_pic');
        if($result_file) {
            $image = $upload->thumb_image;
        }else{
            output_error($upload->error);
        }
        $insert['circle_id'] =1;
        $insert['circle_name']=' ';
        $insert['theme_pic']=$image;
        $themeid = $model->table('circle_theme')->insert($insert);
        if($themeid){
            if(!empty($_REQUEST['tag'])){
                $tag=json_decode(htmlspecialchars_decode($_REQUEST['tag']), true);
                foreach ($tag as $key => $value) {
                    $condition['theme_id']  =$themeid;
                    $condition['tag_type']  =$value['tag_type'];
                    $condition['link_id']  =$value['link_id'];
                    $condition['tag_content']  =$value['tag_content'];
                    $condition['tag_x']     =$value['tag_x'];
                    $condition['tag_y']     =$value['tag_y'];
                    $condition['addtime']   =time();
                    $model->table('circle_tag')->insert($condition);
                }
            }
            $arr=array('url'=> WAP_SITE_URL.'/share_details.html?theme_id='.$themeid);
            output_data($arr);
        }else{
            output_error('发布失败');
        }
    }

    /*评论话题
    time:2015年9月10日20:01:36
    author:xuping
    tool:sublime
    */
    public function commentReplyOP(){
        if( empty($_REQUEST['theme_id']) || !isset($_REQUEST['replay_contents']) ){
            output_error('对不起，评论内容不能为空');
        }
        $theme_id=$_REQUEST['theme_id'];
        $insert['reply_content']    =addslashes($_REQUEST['replay_contents']);
        $insert['member_id']        =$this->member_info['member_id'];
        // $replay_id      =$_POST['replay_id'];
        $insert['theme_id']         =$theme_id;
        $insert['reply_addtime']    =(string)time();
        $res=Model()->table('member')->where(array('member_id'=>$insert['member_id']))->find();
        $insert['member_name']     =$res['member_truename'];

        // 验证是否 是直接评论帖子 还是回复 评论   回复评论验证
        if(!empty($_REQUEST['answer_id'])){
            $reply_info = Model()->table('circle_threply')->where(array('theme_id'=>$_REQUEST['theme_id'] , 'reply_id'=>intval($_REQUEST['answer_id']) ))->find();
            if(!empty($reply_info)) {
                $insert['reply_replyid']    = $reply_info['reply_id'];
                $insert['reply_replyname']  = $reply_info['member_name'];
                $insert['reply_content']    = $insert['reply_content'];
            }else{
                $insert['reply_replyid']    = '';
            }
        }
        // 评论之后，更新改帖子的评论数量
        Model()->table('circle_theme')->update(array('theme_id'=>$theme_id, 'theme_commentcount'=>array('exp', 'theme_commentcount+1')));
        $reply_id = Model()->table('circle_threply')->insert($insert);
        if($reply_id){
            $insert['reply_id']=(string)$reply_id;
            $insert['member_avatar']     =getMemberAvatar($res['member_avatar']);
            output_data($insert);
        }else{
            output_error('评论失败');
        }
    }

    /*
    删除帖子
    2015年9月11日13:44:40
    xuping
     */
    public function delThemeOP(){
        $member_id=$this->member_info['member_id'];
        $theme_id =$_REQUEST['theme_id'];
        if(empty($theme_id)){
            output_error('对不起，请指定删除帖子的id');
        }
        $result=Model()->table('circle_theme')->where(array('theme_id'=>$theme_id,'member_id'=>$member_id))->delete();
        Model()->table('circle_tag')->where(array('theme_id'=>$theme_id))->delete();
        if($result){
            output_data('删除成功');
        }else{
            output_error('删除失败');
        }
    }

    /*
    举报帖子
    2015年9月11日13:46:21
    xuping
    callback=jsonp2

     */
    public function reportThemeOP(){
        $theme_id=intval($_REQUEST['theme_id']);  //帖子id
        $comment=$_REQUEST['comment'];
        if(empty($theme_id) || empty($comment)){
            output_error('对不起，举报帖子的内容不能为空');
        }
        /*验证改帖子是否 存在*/
        $result=Model()->table('circle_theme')->where(array('theme_id'=>$theme_id,'is_shut'=>0))->find();
        if(empty($result)){
            output_error('对不起，举报信息有误');
        }
        $data['theme_id']=$theme_id;
        $data['report_member_id']=$this->member_info['member_id'];
        $data['comment']=$comment;
        $data['addtime']=time();
        $data['status']=0;          //默认是0  管理员还未审核 1 审核通过
        $res=Model()->table('circle_report')->insert($data);
        if($res){
            //output_data('举报成功');
            echo json_encode(array('code'=>200,'message'=>'举报成功','data'=>array('举报成功')));
            exit;
        }else{
            output_error('举报失败');
        }
    }

    /*
    删除评论api
    2015年9月15日13:49:00
    xuping
    http://120.25.240.53/agg/mobile/index.php?act=circle_info&op=delreplay&key=6114a68f9b75d6df3428ed287f91e165&theme_id=73&client_type=android&reply_id=1
     */
    public function delreplayOP(){
        $reply_id=$_REQUEST['reply_id'];
        $theme_id=$_REQUEST['theme_id'];
        if(empty($reply_id) || empty($theme_id)){
            output_error('对不起， 参数不正确');
        }
        $member_id=$this->member_info['member_id'];
        $result=Model()->table('circle_threply')->where(array('reply_id'=>$reply_id , 'theme_id'=>$theme_id,'member_id'=>$this->member_info['member_id']))->delete();

        Model()->table('circle_theme')->update(array('theme_id'=>$theme_id, 'theme_commentcount'=>array('exp', 'theme_commentcount-1')));
        if($result){
            output_data('删除成功');
        }else{
            output_error('操作失败');
        }
    }

    public function FolloweeOP(){
        //获取fans 信息
        $size=10;
        $page=isset($_REQUEST['curpage']) ? intval($_REQUEST['curpage']) :1 ;
        $resf=($page-1)*$size;
        $result=Model()->table('sns_friend')->where(array('friend_frommid'=>$this->member_info['member_id']))->field('friend_tomid , friend_followstate')->limit("$resf , $size")->select();
        foreach ($result as $key => $value) {
            $field='member_truename,member_sex,member_avatar,member_name';
            $res=Model('member')->getMemberInfoByID($value['friend_tomid'],$field);
            $newres[$key]['friend_followstate']=$value['friend_followstate']; //关注状态
            $newres[$key]['member_name']       =$res['member_truename'];
            $newres[$key]['username']          =$res['member_name'];
            $newres[$key]['member_sex']        =$res['member_sex'];
            $newres[$key]['member_avatar']     =getMemberAvatar($res['member_avatar']);
            $newres[$key]['member_id']          =$value['friend_tomid'];
        }
        if(!empty($newres)){
            $newres=array_reverse($newres);
            output_data($newres);
        }else{
            output_data(array());
        }
    }

    /*
     获取我关注人的列表 FolloweeOP
     2015年9月18日13:41:56
     xuping
      */
    public function getFansmemberinfOP(){
        $size=10;
        $page=isset($_REQUEST['curpage']) ? intval($_REQUEST['curpage']) :1 ;
        $resf=($page-1)*$size;
        $result=Model()->table('sns_friend')->where(array('friend_tomid'=>$this->member_info['member_id']))->field('friend_frommid ,friend_followstate')->limit("$resf , $size")->select();
        if($result){
            foreach ($result as $key => $value) {
                $member_info=Model('member')->where(array('member_id'=>$value['friend_frommid']))->field('member_id , member_truename,member_avatar,member_sex,member_name')->find();
                $result[$key]['member_name']=$member_info['member_truename'];
                $result[$key]['username']   =$member_info['member_name'];
                $result[$key]['member_avatar']=getMemberAvatar($member_info['member_avatar']);
                $result[$key]['member_sex']=$member_info['member_sex'];
                $result[$key]['friend_followstate']=($value['friend_followstate']==2) ? 2 : 0 ;
                $result[$key]['member_id']=$value['friend_frommid'];
            }
            $result=array_reverse($result);
            output_data($result);
        }else{
            output_data(array());
        }
    }

    /*与我相关的评论api
      2015年9月19日11:32:24
      xuping
      获取评论的帖子的图片 和   评论人头像，评论人昵称有误 "reply_replyname" = "<null>"，
    */
    public function getMyconmentsOP(){
        $size=20;
        $page=isset($_REQUEST['curpage']) ? intval($_REQUEST['curpage']) :1 ;
        $resf=($page-1)*$size;

        $member_id=$this->member_info['member_id']; //当前的用户id
        //先获取当前用户帖子
        $condition['member_id']=$member_id;
        $field='theme_pic , theme_id';
        $result=Model('circle_theme')->where($condition)->field($field)->select();  //当前用户发过所有的帖子



        if(!empty($result)){
            $threply=Model('circle_threply');
            //获取所有点赞人的信息

            foreach ($result as $key => $value) {
                $temp_id[] =$value['theme_id'];
            }

            $str=implode(',',$temp_id);
            $likeinfo=$threply->where(array('theme_id'=>array('in',$str),'is_closed'=>0))->order('reply_addtime DESC')->limit("$resf,$size")->field('member_id ,reply_id,reply_replyid,theme_id,reply_content, reply_addtime')->select();

            /*更改状态， 表示这个点赞信息已经被读过*/
            $data=array('is_read'=>1);
            $threply->where(array('theme_id'=>array('in',$str),'is_closed'=>0))->update($data);

            $circle_theme=Model('circle_theme');
            foreach ($likeinfo as $kk => $vv) {
                //获取帖子的图片
                $pic=$circle_theme->where(array('theme_id'=>$vv['theme_id']))->field('theme_pic')->find();
                $res[$kk]['theme_pic']=getMemberAvatar($pic['theme_pic']);
                $condition['member_id']=$vv['member_id'];
                $member_info=Model('member')->getMemberInfo($condition , 'member_avatar , member_truename');
                $res[$kk]['member_avatar']=getMemberAvatar($member_info['member_avatar']);
                $res[$kk]['member_truename']=$member_info['member_truename'];
                $res[$kk]['reply_addtime']=$vv['reply_addtime'];
                $res[$kk]['reply_content']=$vv['reply_content'];
                $res[$kk]['theme_id']=$vv['theme_id'];
                $res[$kk]['reply_id']=$vv['reply_id'];
                $res[$kk]['member_id']=$vv['member_id'];
                $res[$kk]['reply_replyid']=empty($vv['reply_replyid']) ? '0' : $vv['reply_replyid'];
                $arr[]=$vv['reply_addtime'];
            }
            if($arr){
                array_multisort($arr, SORT_DESC, $res);
            }
            output_data($res);
        }else{
            output_data(array());
        }
    }

    /*删除与我相关的评论， 不影响记录 `theme_id`,`reply_id` */
    public function delMyconmentsOP(){
        $reply_id=$_REQUEST['reply_id'];
        $theme_id=$_REQUEST['theme_id'];
        if(empty($reply_id) || empty($theme_id)){
            output_error('对不起， 参数不正确');
        }
        $condition['reply_id']=$reply_id;
        $condition['theme_id']=$theme_id;

        $data=array('is_closed'=>1); //隐藏这个评论
        $result=Model('circle_threply')->where($condition)->update($data);

        if($result){
            output_data('删除成功');
        }else{
            output_error('操作失败');
        }
    }

    // /*
    // 获取给我的帖子点过赞的人
    // 2015年9月22日11:03:44
    // xuping
    //  */
    public function getMylikeOP(){
        $size=20;
        $page=isset($_REQUEST['curpage']) ? intval($_REQUEST['curpage']) :1 ;
        $resf=($page-1)*$size;
        $member_id=$this->member_info['member_id']; //当前的用户id
        //先获取当前用户帖子
        $condition['member_id']=$member_id;
        $field='theme_pic , theme_id';
        $result=Model('circle_theme')->where($condition)->field($field)->select();  //当前用户发过所有的帖子

        if(!empty($result)){
            $like=Model('circle_like');
            //获取所有点赞人的信息
            foreach ($result as $key => $value) {
                $temp_id[] =$value['theme_id'];
            }
            $str=implode(',',$temp_id);

            $likeinfo=$like->where(array('theme_id'=>array('in',$str),'status'=>0))->field('member_id,id,theme_id, addtime')->limit("$resf , $size")->order('addtime DESC')->select();
            /*更改状态， 表示这个点赞信息已经被读过*/
            $data=array('is_read'=>1);
            //$like->where(array('theme_id'=>$value['theme_id'],'status'=>0))->update($data);
            $like->where(array('theme_id'=>array('in',$str),'status'=>0))->update($data);

            $circle_theme=Model('circle_theme');
            $member      =Model('member');
            foreach ($likeinfo as $kk => $vv) {
                //获取帖子的图片
                $pic=$circle_theme->where(array('theme_id'=>$vv['theme_id']))->field('theme_pic')->find();
                $res[$kk]['theme_pic']=getMemberAvatar($pic['theme_pic']);
                $condition['member_id']=$vv['member_id'];
                $member_info=$member->getMemberInfo($condition , 'member_avatar , member_truename');
                $res[$kk]['member_avatar']=getMemberAvatar($member_info['member_avatar']);
                $res[$kk]['member_truename']=$member_info['member_truename'];
                $res[$kk]['id']=$vv['id'];
                $res[$kk]['theme_id']=$vv['theme_id'];
                $res[$kk]['member_id']=$vv['member_id'];
                $res[$kk]['addtime']=$vv['addtime'];
                $arr[]=$vv['addtime'];
            }
            //$res=array_reverse($res);
            if($arr){
                array_multisort($arr, SORT_DESC, $res);
            }
            output_data($res);
        }else{
            output_data(array());
        }
    }

    /*删除与我相关的评论*/
    public function delMylikeOP(){
        $theme_id=$_REQUEST['theme_id'];
        $id=$_REQUEST['like_id'];
        if(empty($theme_id) || empty($id)){
            output_error('对不起， 参数不正确');
        }
        $condition['id']=$id;
        $condition['theme_id'] =$theme_id;
        $data   =array('status'=>1);//隐藏与我相关的点赞信息
        $result=Model('circle_like')->where($condition)->update($data);

        if($result){
            output_data('删除成功');
        }else{
            output_error('操作失败');
        }

    }

    /*查询是否 有未读的信息 2015年10月13日14:43:03 xuping*/

    public function getTipsOP(){
        $member_id=$this->member_info['member_id'];
        $condition['member_id']=$member_id;
        $field='theme_pic , theme_id';
        $result=Model('circle_theme')->getmemberAlltheme($condition,$field);

        $like   =Model('circle_like');
        $threply=Model('circle_threply');
        if($result){
            foreach ($result as $key => $value) {
                $likeinfo[]  =$threply->where(array('theme_id'=>$value['theme_id'],'is_closed'=>0,'is_read'=>0))->field('is_read')->select();
                $reply_info[]=$like->where(array('theme_id'=>$value['theme_id'],'status'=>0,'is_read'=>0))->field('is_read')->select();
            }
            //所有未读点赞的信息
            $result_like=$this->_twoarray($likeinfo);
            //所有未读评论信息
            $result_reply=$this->_twoarray($reply_info);
        }
        if(!empty($result_like) || !empty($result_reply)){
            output_data(array('message_count'=>1));
        }
        else{
            output_data(array('message_count'=>0));
        }
    }
    //三维数组变成二位数组
    private function _twoarray($array){
        foreach ($array as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $res[]=$v1;
            }
        }
        return $res;
    }

    /*私有方法 格式化时间问题 xuping*/
    private function _formatTime($time){
        if($time < 60){                                                    //模板中 以秒为单位显示
            $result=$time.'秒前';
        }elseif($time > 60  && $time < 3600){                              //模板中 以分钟为单位显示
            $result=(int)($time/(60)).'分钟前';
        }elseif($time > 3600 && $time < 3600*24 ){                         //模板中 显示以小时为单位
            $result=(int)($time/(3600)).'小时前';
        }elseif($time > 3600*24 ){
            $result=(int)($time/(3600*24)).'天前';//模板中 显示以天为单位
        }
        return $result;
    }
}
<?php
/**
 * 会员银行卡中心
 *
 * @author lijunhua 
 * @since 2015-08-10
 */

use Tpl;

defined('emall') or exit('Access Invalid!');

class member_bankcardControl extends mobileMemberControl {

    public function __construct() {
            parent::__construct();
    }

    /**
     * 获取会员绑定的银行卡信息
     * 
     * @param string $param['key']     登录后唯一校验码
     */
    public function detailOp() {

        $member_info = $this->member_info;
        if ($member_info['bank_card_bind'] == 0) {
            output_error('您尚未绑定银行卡');
        }
        $bankcard_info = Model()->table('member_bank_card')->where(array('member_id' => $member_info['member_id']))->order('is_default desc')->find();
        if (empty($bankcard_info)) {
            output_error('系统未找到任何银行卡信息', array(), ERROR_CODE_DATABASE); 
        }
        // $bankcard_info['pdc_bank_no']   = preg_replace('/\d+(\d{4})$/', '**** **** **** $1', $bankcard_info['pdc_bank_no']);
        $bank_icon = $this->_get_bank_icon($bankcard_info['pdc_bank_name']);
        if(empty($bankcard_info['cardtype'])) {
            $bankcard_info['cardtype'] = '银行卡';
        }
        $bankcard_info['icon'] = $bank_icon;
        output_data($bankcard_info);
    }
    
    /**
     * 绑定银行卡
     * 
     * @param string $param['bank_no']                         卡号
     * @param stirng $param['bank_name']                       银行名称
     * @param string $param['bank_user']                       持卡人姓名
     * @param string $param['open_branch']                     开户网点
     * @param string $param['member_mobile']|$param['mobile']  手机号（是当前绑定手机号，并不是开户行手机号）
     */
    public function bindOp() {
        
        // 兼容mobile参数
        if (isset($_REQUEST['mobile'])) {
             $_REQUEST['member_mobile'] = $_REQUEST['mobile'];
        }
   
        
        $this->_valid_bank_card();
        $member_info = $this->member_info;
     
        $member_common = Model('member_common')->getMemberCommonInfo(array('member_id' => $member_info['member_id']));
        if (empty($member_common['member_realname'])) {
            output_error('请先实名认证', array(), ERROR_CODE_OPERATE);
        }
        if ($member_common['member_realname'] != $_REQUEST['bank_user']) {
            output_error('持卡人姓名不正确', array(), ERROR_CODE_OPERATE);
        }
        
        $model_bank_card =  Model('member_bank_card');
        $bank_card = array();
        $bank_card['pdc_bank_name'] = $_REQUEST['bank_name'];
        $bank_card['pdc_bank_no']   = $_REQUEST['bank_no'];
        $bank_card['pdc_bank_user'] = $_REQUEST['bank_user'];
        $bank_card['open_branch']   = $_REQUEST['open_branch'];
        $bank_card['pdc_mobile']    = $_REQUEST['member_mobile'];
        $bank_card['pdc_add_time']  = TIMESTAMP;
        
        $detail = $model_bank_card->getMemberBankCardInfo(array('member_id' => $member_info['member_id']));
        
        try {
            $model_bank_card->beginTransaction();
            if (!empty($detail)) {
                 // 编辑
                 $result = $model_bank_card->editMemberBankCard(array('member_id' => $member_info['member_id']), $bank_card);
            } else {
                // 新增
                $bank_card['member_id'] = $member_info['member_id'];
                $result = $model_bank_card->save($bank_card);
            }    

            if ($result) {
                $result = Model('member')->editMember(array('member_id' => $member_info['member_id']), array('bank_card_bind' => 1));
                $model_bank_card->commit();
            }
            
        } catch (Exception $e) {
           $model_bank_card->rollback();
           ouput_error('操作失败', array(), ERROR_CODE_DATABASE);
        }
        

        if (!$result) {
            output_error('绑定失败', array(), ERROR_CODE_OPERATE);
        }
        
        // 操作成功
        output_data(array());
    }
    /**
     * 2.1.5
     * 添加银行卡
     * 大亮
     */
    public function add_bank_cardOp() {

        if (!isset($_REQUEST['bank_no']) || empty($_REQUEST['bank_no'])) {
            output_error('请输入卡号', array(), ERROR_CODE_ARG);
        }
        if (!preg_match('/^(\d{19}|\d{16}|\d{17}|\d{18})$/', $_REQUEST['bank_no'])) {
            output_error('请输入正确银行卡号', array(), ERROR_CODE_ARG);
        }
        $member_info = $this->member_info;
        $member_common = Model('member_common')->getMemberCommonInfo(array('member_id' => $member_info['member_id']));
        if (empty($member_common['member_realname'])) {
            output_error('请先实名认证', array('type'=>0), ERROR_CODE_OPERATE);
        }
        $model_bank_card =  Model('member_bank_card');
        //验证银行卡是否存在
        $bank_result = $model_bank_card->getMemberBankCardInfo(array('pdc_bank_no'=>$_REQUEST['bank_no'],'member_id' => $member_info['member_id']));
        if(!empty($bank_result)) {
            output_error('已添加过该银行卡', array('type'=>0), ERROR_CODE_OPERATE);
        }
        $bank_card = array();
        if(!empty($_REQUEST['bank_name'])) {
            $bank_card['pdc_bank_name'] = $_REQUEST['bank_name'];
        }
        else {
            //第三方识别银行卡
            $ch = curl_init();
            $url = 'http://apis.baidu.com/datatiny/cardinfo/cardinfo?cardnum='.$_REQUEST['bank_no'];
            $header = array(
                'apikey:805af2bbd64756bb913432f53219bdaa',
            );
            // 添加apikey到header
            curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // 执行HTTP请求
            curl_setopt($ch , CURLOPT_URL , $url);
            $res = curl_exec($ch);
           $result_json = json_decode($res,true);
            if(!empty($result_json['data']['bankname'])) {
                $bank_card['pdc_bank_name'] = $result_json['data']['bankname'];
                if(!empty($result_json['data']['cardtype'])) {
                    $bank_card['cardtype'] = $result_json['data']['cardtype'];
                }
            }
            else {
                output_error('银行卡未识别，请手动输入银行名称', array('type'=>1), ERROR_CODE_OPERATE);
            }
        }
        $bank_card['pdc_bank_no']   = $_REQUEST['bank_no'];
        $bank_card['pdc_bank_user'] = $member_common['member_realname'];
        $bank_card['pdc_add_time']  = TIMESTAMP;
        $bank_card['member_id'] = $member_info['member_id'];
        $detail = $model_bank_card->getMemberBankCardInfo(array('member_id' => $member_info['member_id']));

        try {
            $model_bank_card->beginTransaction();
            if (!empty($detail)) {
                $bank_card['is_default'] = 0;
            } else {
                $bank_card['is_default'] = 1;
            }
            $result = $model_bank_card->save($bank_card);
            if ($result && empty($detail)) {
                $result = Model('member')->editMember(array('member_id' => $member_info['member_id']), array('bank_card_bind' => 1));
            }
            $model_bank_card->commit();

        } catch (Exception $e) {
            $model_bank_card->rollback();
            ouput_error('添加失败', array(), ERROR_CODE_DATABASE);
        }
        if (!$result) {
            output_error('添加失败', array(), ERROR_CODE_DATABASE);
        }

        // 操作成功
        output_data(array());
    }

    private function _valid_bank_card() {
        if (!isset($_REQUEST['bank_no']) || empty($_REQUEST['bank_no'])) {
            output_error('请输入卡号', array(), ERROR_CODE_ARG);
        }
       
        if (!preg_match('/^(\d{19}|\d{16}|\d{17}|\d{18})$/', $_REQUEST['bank_no'])) {
            output_error('请输入正确银行卡号', array(), ERROR_CODE_ARG);
        }
        
        if (!isset($_REQUEST['bank_name']) || empty($_REQUEST['bank_name'])) {
            output_error('请输入银行名称', array(), ERROR_CODE_ARG);
        }
        
        if (!isset($_REQUEST['bank_user']) || empty($_REQUEST['bank_user'])) {
            output_error('请输入持卡人姓名', array(), ERROR_CODE_ARG);
        }
        
        if (!isset($_REQUEST['open_branch']) || empty($_REQUEST['open_branch'])) {
            output_error('请输入开户网点', array(), ERROR_CODE_ARG);
        }
        
        if (!isset($_REQUEST['member_mobile']) || empty($_REQUEST['member_mobile'])) {
            output_error('请输入手机号', array(), ERROR_CODE_ARG);
        }
       
        if (!preg_match('/^\d{11}$/', $_REQUEST['member_mobile'])) {
            output_error('请输入正确手机号', array(), ERROR_CODE_ARG);
        }
  
    }
    /**
     *  根据银行卡获取图片logo
     */
    private function _get_bank_icon($bank_name) {
        $bank_list = array(array('bank_name'=>'中国银行','icon'=>'http://pica.datatiny.com/banklogo/icbc.pn'),array('bank_name'=>'农业银行','icon'=>'http://pica.datatiny.com/banklogo/abc.pn'));
        foreach($bank_list as $item) {
            if($item['bank_name']==$bank_name) {
                return $item['icon'];
            }
        }
        return "http://pica.datatiny.com/banklogo/default.pn";
    }
    /**
     *  根据记录id删除银行卡信息
     */
    public function del_bank_cardOp() {
        if (!isset($_REQUEST['bank_id']) || empty($_REQUEST['bank_id'])) {
            output_error('提交信息有误，请重试', array(), ERROR_CODE_ARG);
        }
        $member_info = $this->member_info;
        $bankcard_model = Model('member_bank_card');
        $condition = array();
        $condition['member_id'] = $member_info['member_id'];
        $condition['id'] = $_REQUEST['bank_id'];
        $result = $bankcard_model->delBankCard($condition);
        if($result) {
            //重新返回银行卡数据
            $condition = array();
            $condition['member_id'] = $member_info['member_id'];
            $card_list = Model('member_bank_card')->where($condition)->field('id,pdc_bank_name,pdc_bank_no,cardtype,pdc_add_time,open_branch,is_default')->order('is_default desc')->select();
            if(empty($card_list)) {
                $result = Model('member')->editMember(array('member_id' => $member_info['member_id']), array('bank_card_bind' => 0));
            }
            else {
                for($i=0;$i<count($card_list);$i++) {
                    $bank_icon = $this->_get_bank_icon($card_list[$i]['pdc_bank_name']);
                    if(empty($card_list[$i]['cardtype'])) {
                        $card_list[$i]['cardtype'] = '银行卡';
                    }
                    $card_list[$i]['icon'] = $bank_icon;
                }
                if($card_list[0]['is_default']==0) {
                    //无默认银行卡
                    $condition = array();
                    $condition['member_id'] = $member_info['member_id'];
                    $condition['id'] = $card_list[0]['id'];
                    $result = $bankcard_model->editMemberBankCard($condition,array('is_default'=>1));
                    $card_list[0]['is_default'] = 1;
                }
            }
            output_data($card_list);
        }
        else {
            output_error('删除失败，请重试', array(), ERROR_CODE_DATABASE);
        }
    }
    /**
     *  根据记录id设置银行卡默认
     */
    public function set_bank_card_defaultOp() {
        if (!isset($_REQUEST['bank_id']) || empty($_REQUEST['bank_id'])) {
            output_error('提交信息有误，请重试', array(), ERROR_CODE_ARG);
        }
        $member_info = $this->member_info;
        $bankcard_model = Model('member_bank_card');
        $condition = array();
        $condition['member_id'] = $member_info['member_id'];
        $condition['id'] = $_REQUEST['bank_id'];
        $result = $bankcard_model->editMemberBankCard(array('member_id'=>$member_info['member_id']),array('is_default'=>0));
        $result = $bankcard_model->editMemberBankCard($condition,array('is_default'=>1));
        if($result) {
            //重新返回银行卡数据
            $condition = array();
            $condition['member_id'] = $member_info['member_id'];
            $card_list = Model('member_bank_card')->where($condition)->field('id,pdc_bank_name,pdc_bank_no,cardtype,pdc_add_time,open_branch,is_default')->order('is_default desc')->select();
            for($i=0;$i<count($card_list);$i++) {
                $bank_icon = $this->_get_bank_icon($card_list[$i]['pdc_bank_name']);
                if(empty($card_list[$i]['cardtype'])) {
                    $card_list[$i]['cardtype'] = '银行卡';
                }
                $card_list[$i]['icon'] = $bank_icon;
            }
            output_data($card_list);
        }
        else {
            output_error('设置失败，请重试', array(), ERROR_CODE_DATABASE);
        }
    }
    /**
     *  获取用户所有银行卡信息
     */
    public function get_all_bank_cardOp() {
        require(BASE_DATA_PATH.'/area/area.php');
        $member_info = $this->member_info;
        $condition = array();
        $condition['member_id'] = $member_info['member_id'];
        $card_list = Model('member_bank_card')->where($condition)->field('id,pdc_bank_name,pdc_bank_no,cardtype,pdc_add_time,open_branch,is_default')->order('is_default desc')->select();
        for($i=0;$i<count($card_list);$i++) {
            $bank_icon = $this->_get_bank_icon($card_list[$i]['pdc_bank_name']);
            if(empty($card_list[$i]['cardtype'])) {
                $card_list[$i]['cardtype'] = '银行卡';
            }
            $card_list[$i]['icon'] = $bank_icon;
        }
        output_data($card_list);
    }
}    

 

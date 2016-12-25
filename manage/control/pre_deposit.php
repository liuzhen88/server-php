<?php
defined('emall') or exit('Access Invalid!');
class pre_depositControl extends SystemControl{
    const EXPORT_SIZE = 1000;
    public function __construct(){
        parent::__construct();
    }
    //设置预充值活动
    public function settingOp(){
        if(isset($_POST['data'])) {
            $data=$_POST['data'];
            $data['begin_time']=strtotime($_POST['data']['begin_time']);
            $data['end_time']=strtotime($_POST['data']['end_time']);
            $data['amount']=floatval($_POST['data']['amount']);
            if($data['begin_time']>=$data['end_time']){
                showMessage(' 开始时间不能大于结束时间');
            }
            $res=Model('store_pre_deposit_set')->select();
            if($res){
                $res=Model('store_pre_deposit_set')->update($data,array('where'=>'id>0'));
            }else{
                Model('store_pre_deposit_set')->insert($data);
            }
            showMessage('设置成功');
        }
        $setting=Model('store_pre_deposit_set')->find();
        Tpl::output('setting',$setting);
        Tpl::showpage('pre_deposit.setting');
    }

    //充值记录列表
    public function logOp(){
        $option=array();
        if(isset($_GET['store_name'])){
            $option['where']['store_pre_deposit.store_name']=array('like','%'.trim($_GET['store_name']).'%');
            Tpl::output('store_name',trim($_GET['store_name']));
        }
        if(isset($_GET['pre_sn'])){
            $option['where']['store_pre_deposit.pre_sn']=array('like','%'.trim($_GET['pre_sn']).'%');
        }
        $start_time=$end_time=null;
        if(isset($_GET['begin_time']) && strtotime($_GET['begin_time'])){
            $start_time=strtotime($_GET['begin_time']);
            Tpl::output('query_start_time',$_GET['begin_time']);
//            $option['where']['create_time']=array('egt',strtotime($_POST['begin_time']));//>=
        }
        if(isset($_GET['end_time']) && strtotime($_GET['end_time'])){
            $end_time=strtotime($_GET['end_time']);
            Tpl::output('query_end_time',$_GET['end_time']);
//            $option['where']['create_time']=array('elt',strtotime($_POST['end_time']));//<=
        }
        $option['where']['store_pre_deposit.create_time']=array('time',array($start_time,$end_time));
        $option['order']="debt desc";
        $option['group']="store_pre_deposit.member_id";
        //$list=Model('store_pre_deposit')->page(20)->select($option);

         $list = Model()->table('store_pre_deposit,member')
            ->join('left')
            ->field('store_pre_deposit.*,member.member_name,SUM(store_pre_deposit.amount) as sum_amount,case when SUM(store_pre_deposit.amount)-member.available_predeposit>0 then SUM(store_pre_deposit.amount)-member.available_predeposit else 0 end as  debt')
            ->on('store_pre_deposit.member_id=member.member_id')
            ->page(20)
            ->select($option);
        $list_count = Model()->table('store_pre_deposit,member')
            ->join('left')
            ->field('store_pre_deposit.*,SUM(store_pre_deposit.amount) as sum_amount,case when SUM(store_pre_deposit.amount)-member.available_predeposit>0 then SUM(store_pre_deposit.amount)-member.available_predeposit else 0 end as  debt')
            ->on('store_pre_deposit.member_id=member.member_id')
            ->count('DISTINCT(member.member_id)');
        //查询区域
        for($i=0;$i<count($list);$i++){
            $area = Model()->table('store')->field('area_info')->where(array('store_id'=>$list[$i]['store_id']))->find();
            $list[$i]['area_info'] = $area['area_info'];
        }
        pagecmd('settotalnum',$list_count);
        pagecmd('seteachnum',20);
        $show_page= pagecmd('show',null);
        Tpl::output('show_page',$show_page);
        Tpl::output('list',$list);
        Tpl::showpage('pre_deposit.log');
    }
    //导出预充值记录列表
    public function export_debt_listOp(){
        $option=array();
        if(isset($_GET['store_name'])){
            $option['where']['store_pre_deposit.store_name']=array('like','%'.trim($_GET['store_name']).'%');
            Tpl::output('store_name',trim($_GET['store_name']));
        }
        if(isset($_GET['pre_sn'])){
            $option['where']['store_pre_deposit.pre_sn']=array('like','%'.trim($_GET['pre_sn']).'%');
        }
        $start_time=$end_time=null;
        if(isset($_GET['begin_time']) && strtotime($_GET['begin_time'])){
            $start_time=strtotime($_GET['begin_time']);
            Tpl::output('query_start_time',$_GET['begin_time']);
//            $option['where']['create_time']=array('egt',strtotime($_POST['begin_time']));//>=
        }
        if(isset($_GET['end_time']) && strtotime($_GET['end_time'])){
            $end_time=strtotime($_GET['end_time']);
            Tpl::output('query_end_time',$_GET['end_time']);
//            $option['where']['create_time']=array('elt',strtotime($_POST['end_time']));//<=
        }
        $option['where']['store_pre_deposit.create_time']=array('time',array($start_time,$end_time));
        $option['order']="debt desc";
        $option['group']="store_pre_deposit.member_id";

        $list_count = Model()->table('store_pre_deposit,member')
            ->join('left')
            ->field('store_pre_deposit.*,SUM(store_pre_deposit.amount) as sum_amount,case when SUM(store_pre_deposit.amount)-member.available_predeposit>0 then SUM(store_pre_deposit.amount)-member.available_predeposit else 0 end as  debt')
            ->on('store_pre_deposit.member_id=member.member_id')
            ->count('DISTINCT(member.member_id)');

        if (!is_numeric($_GET['curpage']) && !is_numeric($_GET['cursection'])){
            $array = array();
            if ($list_count > self::EXPORT_SIZE ){	//显示下载链接
                $page = ceil($list_count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $list_count ? $list_count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=pre_deposit&op=log');
                Tpl::showpage('export.excel');
            }else{	//如果数量小，直接下载
                $data = Model()->table('store_pre_deposit,member')
                    ->join('left')
                    ->field('store_pre_deposit.*,member.member_name,SUM(store_pre_deposit.amount) as sum_amount,case when SUM(store_pre_deposit.amount)-member.available_predeposit>0 then SUM(store_pre_deposit.amount)-member.available_predeposit else 0 end as  debt')
                    ->on('store_pre_deposit.member_id=member.member_id')
                    ->limit(self::EXPORT_SIZE)
                    ->order('joinin_state asc,id desc')
                    ->select($option);
                //查询区域
                for($i=0;$i<count($data);$i++){
                    $area = Model()->table('store')->field('area_info')->where(array('store_id'=>$data[$i]['store_id']))->find();
                    $data[$i]['area_info'] = $area['area_info'];
                }
                $this->createDebtExcel($data);
            }
        }elseif(is_numeric($_GET['cursection'])) {	//分段下载
            $limit1 = ($_GET['cursection']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = Model()->table('store_pre_deposit,member')
                ->join('left')
                ->field('store_pre_deposit.*,member.member_name,SUM(store_pre_deposit.amount) as sum_amount,case when SUM(store_pre_deposit.amount)-member.available_predeposit>0 then SUM(store_pre_deposit.amount)-member.available_predeposit else 0 end as  debt')
                ->on('store_pre_deposit.member_id=member.member_id')
                ->limit("$limit1,$limit2")
                ->order('joinin_state asc,id desc')
                ->select($option);
            //查询区域
            for($i=0;$i<count($data);$i++){
                $area = Model()->table('store')->field('area_info')->where(array('store_id'=>$data[$i]['store_id']))->find();
                $data[$i]['area_info'] = $area['area_info'];
            }
            $this->createDebtExcel($data);
        }else{	//分页下载
            $limit1 = ($_GET['curpage']-1) * 20;
            $limit2 = 20;
            $data = Model()->table('store_pre_deposit,member')
                ->join('left')
                ->field('store_pre_deposit.*,member.member_name,SUM(store_pre_deposit.amount) as sum_amount,case when SUM(store_pre_deposit.amount)-member.available_predeposit>0 then SUM(store_pre_deposit.amount)-member.available_predeposit else 0 end as  debt')
                ->on('store_pre_deposit.member_id=member.member_id')
                ->limit("$limit1,$limit2")
                ->order('joinin_state asc,id desc')
                ->select($option);
            //查询区域
            for($i=0;$i<count($data);$i++){
                $area = Model()->table('store')->field('area_info')->where(array('store_id'=>$data[$i]['store_id']))->find();
                $data[$i]['area_info'] = $area['area_info'];
            }
            $this->createDebtExcel($data);
        }
    }
    /**
     * 生成预充值记录excel
     * @param array $data
     */
    private function createDebtExcel($data = array()){
        Language::read('export');
        import('libraries.excel');
        $excel_obj = new Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
        //header
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'店铺名称');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'店铺账号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'所属区域');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'预充值金额');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'当前欠款');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'操作人');
        foreach ((array)$data as $k=>$v){
            $tmp = array();
            $tmp[] = array('data'=>$v['store_name']);
            $tmp[] = array('data'=>$v['member_name']);
            $tmp[] = array('data'=>$v['area_info']);
            $tmp[] = array('data'=>$v['sum_amount']);
            $tmp[] = array('data'=>$v['debt']);
            $tmp[] = array('data'=>$v['create_name']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data,CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset('商户预充值',CHARSET));
        $excel_obj->generateXML($excel_obj->charset('商户预充值',CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
    }
    //充值功能(列表)
    public function chargeOp(){
        $option=array();
        if(isset($_POST['store_name'])){
            $option['where']['store.store_name']=array('like','%'.trim($_POST['store_name']).'%');
        }
        if(isset($_POST['seller_name'])){
            $option['where']['store.seller_name']=array('like','%'.trim($_POST['seller_name']).'%');
        }
        $list=Model()->table('store,member')->join('inner')->on('member.member_id = store.member_id')->page(20)->select($option);
        Tpl::output('show_page',Model()->showpage());
        Tpl::output('list',$list);
        Tpl::showpage('pre_deposit.charge');
    }

    //充值弹框
    public function dialogOp(){
        Tpl::showpage('pre_deposit.dialog','null_layout');
    }

    //充值，保存数据(更新member表，和两个充值记录表)
    public function charge_saveOp(){

        $member_id=intval($_POST['member_id']);
        if($member_id<=0) showMessage('系统异常');
        $money=floatval($_POST['money']);
        if($money<=0) showMessage('金额不合法');
        $member=Model('member')->find($member_id);
        if(!$member) showMessage('此商户不存在');
        $store=Model('store')->where(array('member_id'=>$member_id))->find();
        if(!$store) showMessage('此商户不存在');

        try {
            Model::beginTransaction();
            $data = array('member_id' => $member_id, 'available_predeposit' => $member['available_predeposit'] + $money);
            Model('member')->update($data);
            $store_pre_deposit['store_id'] = $store['store_id'];
            $store_pre_deposit['store_name'] = $store['store_name'];
            $store_pre_deposit['member_id'] = $member_id;
            $store_pre_deposit['amount'] = $money;
            $store_pre_deposit['type'] = 1;
            $store_pre_deposit['create_time'] = time();
            $store_pre_deposit['create_name'] = $this->admin_info['name'];
            $store_pre_deposit['pre_sn'] = Model('store_pre_deposit')->makeSn($store['store_id']);
            Model('store_pre_deposit')->insert($store_pre_deposit);
            $pd_log['lg_member_id'] = $member_id;
            $pd_log['lg_member_name'] = $member['member_name'];
            $pd_log['lg_admin_name'] = $this->admin_info['name'];
            $pd_log['lg_type'] = 'recharge';
            $pd_log['lg_av_amount'] = $money;
            $pd_log['lg_add_time'] = time();
            $pd_log['lg_desc'] = '预存款充值';
            Model('pd_log')->insert($pd_log);
            Model::commit();
        }catch (Exception $e){
            Model::rollback();
            showMessage('预充值失败');
        }
        showMessage('预充值成功');
    }


}
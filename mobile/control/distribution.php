
<?php
//use Tpl;

defined('emall') or exit('Access Invalid!');
class distributionControl extends mobileMemberControl
{

    public function __construct()
    {

        parent::__construct();

    }

    /*
     个人中心  获取折线图
    2015年11月13日11:01:23
    xuping
    */
    public function sale_statisOp(){
        $time_type = isset($_REQUEST['time_type']) ?  $_REQUEST['time_type']: 'week' ;
        //$time_type='week';
        $arr =  Array();
        $tag=isset($_REQUEST['tag']) ? $_REQUEST['tag'] :'two';
        //销售业绩  日 月 年 统计
        switch ($time_type) {
            case 'week':
                $temp=array_fill(0, 7, '0.00');
                $field = ' di_totals as money ,di_other_totals,di_self_totals';
                $where = ' di_day>='.date("Ymd",strtotime('now -6 day')).' AND di_day<='.date("Ymd",strtotime('now'));
                $group = ' di_day ';
                $start_day=date("m月d号",strtotime('d -6 day'));
                $end_day=date('m月d号');
                break;
            case 'month':
                $temp=array_fill(0, 30, '0.00');
                $field = ' di_totals as money ,di_other_totals,di_self_totals ,di_day ';
                $where = ' di_day>='.date("Ymd",strtotime('now -29 day')).' AND di_day<='.date("Ymd",strtotime('now')) ;
                $group = ' di_day ';
                $start_day=date("m月d号",strtotime('d -29 day'));
                $end_day=date('m月d号');
                break;
            case 'year':
                $temp= array_fill(0, 6, '0.00');
                $field = ' di_totals as money ,di_other_totals,di_self_totals ';
                $where = '  di_day>='.date("Ymd",strtotime('now -5 month')).' AND di_day<='.date("Ymd",strtotime('now'));
                $group = '  di_month ';
                $start_day=date("Y年m月",strtotime('now -5 month'));
                $end_day=date('Y年m月');
                break;
        }

        $member_id = $this->member_info['member_id'];
        $whereAdd = " and member_id =$member_id";
        $order = ' di_day';
        $data_list=Model('distribution_statis')->field($field)->where($where . $whereAdd)->group($group)->order($order)->select();

        //二位数组编程一维数组
        $money=0;
        foreach ($data_list as $key => $value) {
            $temp[$key]=$value['money'];
            $money += $value['money'];
        }

        //对月的数据进行处理
        if($time_type=='month'){
            for ($i=29; $i>=0; $i--) {
                $result[]  = date('Ymd', strtotime("-{$i} day"));  //过去30天的日期
            }
            foreach($result as $date){
                foreach($data_list as $k=>$v){
                    if($date==$v['di_day']){
                        $array[$date]=(string)$v['di_self_totals'];
                        break;
                    }else{
                        $array[$date]=(string)'0.00';
                    }
                }
            }
            if(!empty($array)){
                foreach ($array as $k => $v) {
                    if(($k+1)%2){
                        $arr[]=$v;
                    }
                }
                $temp=array_reverse($arr);
            }else{
                $temp=array_fill(0, 15, '0.00');
            }
        }

        $data['start_day']=$start_day;
        $data['end_day']=$end_day;
        $data['total_money']=$money;
        $data['per_money']= array_reverse($temp);
        if($data){
            output_data($data);
        }else{
            output_data(array());
        }
    }


    /**
     * 我的分销 分销的总佣金和分销明细
     * functionname   : get_mydistributeOP
     * author         : xuping
     */
    public function get_mydistributeOP(){
        $member_id=$this->member_info['member_id'];
        $where['dis_member_id']=$member_id;

        $field='id,goods_name,distribution_money,add_time,goods_id,goods_price,goods_num';
        $model_distribute=Model('distribution_records');
        $result          =$model_distribute->getRecords($where,$field,$page=10);
        $goods=Model('goods');
        foreach($result['list'] as $key=>$value){
            $goods_info=$goods->getGoodsInfo(array('goods_id'=>$value['goods_id']),'goods_image,store_id');
            $result['list'][$key]['goods_image']=thumb($goods_info);
        }
        if(!empty($result)){
            output_data_msg($result);
        }else{
            output_data(array());
        }
    }


    /**
     *分销明细， 上面估计的分销商品
     * functionname   : getdistribute_infoOP
     * author         : xuping
     */
//    public function getdistribute_infoOP(){
//        $distribute_id=$_REQUEST['distribute_id'];
//        $model_distribute=Model('distribution_records');
//        $where['id']=$distribute_id;
//        $field='id,goods_name,distribution_money,add_time,goods_id,goods_price,goods_num';
//        $list=$model_distribute->getRecordsInfo($where,$field);
//        if(!empty($list)){
//            output_data_msg($list);
//        }else{
//            output_data(array());
//        }
//    }

    public function distribute_listOP(){
        $goods_id=$_REQUEST['goods_id'];
        $model_distribute=Model('distribution_records');
        $member_id=$this->member_info['member_id'];
        $where['distribution_records.goods_id']=$goods_id;
        $where['distribution_records.dis_member_id']=$member_id;
        $field='distribution_records.id,distribution_records.order_id,distribution_records.add_time,distribution_records.dis_member_id
        ,order_goods.buyer_id,order_goods.goods_pay_price,order_goods.goods_num';
        $list=$model_distribute->getRecords_list($where,$field);
        if(!empty($list)){
                $member_model=Model('member');
                $count=0;
                $good_count=0;
                foreach($list as $key=>$value){
                        $result=$member_model->getMemberInfo(array('member_id'=>$value['buyer_id']),'member_name');
                        $list[$key]['member_name']=$result['member_name'];
                        $list[$key]['rebate_money']=REBATE_BUY_USER*$value['goods_pay_price']/100;
                        $count=$count+REBATE_BUY_USER*$value['goods_pay_price']/100;
                        $good_count=$good_count+$value['good_count'];
                }
            unset($result);
            /*上方商品信息*/
            $array['goods_id']=$goods_id;
            $array['dis_member_id']=$member_id;
            $res=$model_distribute->getRecord($array,'*');

            $goods_info['goods_name'] =$res['goods_name'];
            $goods_info['goods_price']=$res['goods_price'];
            $goods_info['goods_count']=$res['goods_name'];
            $goods_info['rebate']     =$count;

            $result['distribute_info']=$list;
            $result['info']           =$goods_info;
            output_data_msg($result);
        }else{
            output_data(array());
        }
    }


}
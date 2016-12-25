   <?php
    defined('emall') or exit('Access Invalid!');
    /**
     * 随机主持人
     */
    class meetingControl extends mobileHomeControl
    {

        public function __construct()
        {
            parent::__construct();
        }
        /**
         * 获取随机主持人
         */
        public function getRandomCandiOP()
        {
            $list = Model()->table('meeting')->field('*')->page(1,1)->select();
            //今日已选出
            $result = array();
            $result["suzhou"] = "於洁";
            $result["hefei"] = "戴文源";
            if($list[0]["day"] == date('d')) {
                $option['where']['today']=1;
                $result_list = Model()->table('meeting') ->field('city,name')->page(2)->select($option);
                if (!empty($result_list[0])) {
                    if ($result_list[0]["city"] == 0) {
                        $result["suzhou"] = $result_list[0]["name"];
                    }
                    else {
                        $result["hefei"] = $result_list[0]["name"];
                    }
                }
                if (!empty($result_list[1])) {
                    if ($result_list[1]["city"] == 0) {
                        $result["suzhou"] = $result_list[1]["name"];
                    }
                    else {
                        $result["hefei"] = $result_list[1]["name"];
                    }
                }
            }
            //今日未选出
            else {
                if(date('N')!=6&&date('N')!=7){
                    //更新当前日期
                    $data = array('day' => date('d'));
                    Model()->table('meeting')->where("1=1")->update($data);
                    //苏州
                    $option['where']['city']=0;
                    $option['where']['flag']=0;
                    $result_list = Model()->table('meeting') ->field('*')->select($option);
                    if( count($result_list) >0 ) {
                        //有候选  随机挑选
                        $random = rand(1,count($result_list));
                        //上个候选去掉
                        $data = array('today' => 0);
                        Model()->table('meeting')->where("today = 1 and city = 0")->update($data);
                        $result["suzhou"] = $result_list[$random-1]["name"];
                        //更新当前候选人
                        $new_data = array('today' => 1,'flag' => 1);
                        Model()->table('meeting')->where('id = '.$result_list[$random-1]["id"])->update($new_data);
                    }
                    else {
                        //无候选人 重置后随机
                        $data = array('today' => 0,'flag' =>0);
                        Model()->table('meeting')->where("city = 0")->update($data);
                        $result_list = Model()->table('meeting') ->field('*')->select($option);
                        $random = rand(1,count($result_list));
                        $result["suzhou"] = $result_list[$random-1]["name"];
                        //更新当前候选人
                        $new_data = array('today' => 1,'flag' => 1);
                        Model()->table('meeting')->where('id = '.$result_list[$random-1]["id"])->update($new_data);
                    }
                    //合肥
                    $option['where']['city']=1;
                    $option['where']['flag']=0;
                    $result_list = Model()->table('meeting') ->field('*')->select($option);
                    if( count($result_list) >0 ) {
                        //有候选  随机挑选
                        $random = rand(1,count($result_list));
                        //上个候选去掉
                        $data = array('today' => 0);
                        Model()->table('meeting')->where("today = 1 and city = 1")->update($data);
                        $result["hefei"] = $result_list[$random-1]["name"];
                        //更新当前候选人
                        $new_data = array('today' => 1,'flag' => 1);
                        Model()->table('meeting')->where('id = '.$result_list[$random-1]["id"])->update($new_data);
                    }
                    else {
                        //无候选人 重置后随机
                        $data = array('today' => 0,'flag' =>0);
                        Model()->table('meeting')->where("city = 1")->update($data);
                        $result_list = Model()->table('meeting') ->field('*')->select($option);
                        $random = rand(1,count($result_list));
                        $result["hefei"] = $result_list[$random-1]["name"];
                        //更新当前候选人
                        $new_data = array('today' => 1,'flag' => 1);
                        Model()->table('meeting')->where('id = '.$result_list[$random-1]["id"])->update($new_data);
                    }
                }
                else {
                    $option['where']['today']=1;
                    $result_list = Model()->table('meeting') ->field('city,name')->page(2)->select($option);
                    if (!empty($result_list[0])) {
                        if ($result_list[0]["city"] == 0) {
                            $result["suzhou"] = $result_list[0]["name"];
                        }
                        else {
                            $result["hefei"] = $result_list[0]["name"];
                        }
                    }
                    if (!empty($result_list[1])) {
                        if ($result_list[1]["city"] == 0) {
                            $result["suzhou"] = $result_list[1]["name"];
                        }
                        else {
                            $result["hefei"] = $result_list[1]["name"];
                        }
                    }
                }
            }

           output_data($result);

        }
}
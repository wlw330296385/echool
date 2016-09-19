<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function index()
    {
        $DEVICE_NO = 'kdt1079970';
        $key = 'a0666';
        $content = "^N1^F1\n";
        $content .= "^B2 Eschool\n";
        $content .= "名称　　　　　 单价  数量 金额\n";
        $content .= "--------------------------------\n";
        $content .= "百岁山　　　　  1.0    1   1.0\n";
        $content .= "备注：加辣\n";
        $content .= "--------------------------------\n";
        $content .= "^H2合计：xx.0元\n";
        $content .= "^H2送货地点：广西南宁市\n";
        $content .= "^H2联系电话：13888888888888\n";
        $content .= "^H2订餐时间：2014-08-08 08:08:08\n";
        $qrlength=chr(strlen('http://www.nntzd.com'));
        $content .= "^Q".$qrlength."http://www.nntzd.com\n";
        // $result = $this->sendSelfFormatOrderInfo($DEVICE_NO, $key, 1,$content);
        // var_dump($result);
        
        $this->display();
    }
    function sendSelfFormatOrderInfo($device_no,$key,$times,$orderInfo){ // $times打印次数
        $selfMessage = array(
            'deviceNo'=>$device_no,  
            'printContent'=>$orderInfo,
            'key'=>$key,
            'times'=>$times
        );              
        $url = "http://open.printcenter.cn:8080/addOrder";
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded ",
                'method'  => 'POST',
                'content' => http_build_query($selfMessage),
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        return $result;
    }
    public function help()
    {
        $this->display();
    }

    public function about()
    {
        $temp = M('Shop_set')->find();
        $this->assign('shop', $temp);
        $this->display();
    }
}
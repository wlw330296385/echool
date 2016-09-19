<?php

namespace Util;

class Tx
{
    public function trimString($value)
    {
        $ret = null;
        if (null != $value) {
            $ret = $value;
            if (strlen($ret) == 0) {
                $ret = null;
            }
        }

        return $ret;
    }

    /**
     *  作用：产生随机字符串，不长于32位.
     */
    public function createNoncestr($length = 32)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $str = '';
        for ($i = 0; $i < $length; ++$i) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $str;
    }

    /**
     *  作用：格式化参数，签名过程需要使用.
     */
    public function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = '';
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k.'='.$v.'&';
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }

        return $reqPar;
    }

    /**
     *  作用：生成签名.
     */
    public function getSign($Obj, $key)
    {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $String = $String.'&key='.$key;
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }

    /**
     *  作用：array转xml.
     */
    public function arrayToXml($arr)
    {
        $xml = '<xml>';
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= '<'.$key.'>'.$val.'</'.$key.'>';
            } else {
                $xml .= '<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
            }
        }
        $xml .= '</xml>';

        return $xml;
    }

    /**
     *  作用：将xml转为array.
     */
    public function xmlToArray($xml)
    {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        return $array_data;
    }

    /**
     *  作用：以post方式提交xml到对应的接口url.
     */
    public function postXmlCurl($xml, $url, $second = 30)
    {
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOP_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        curl_close($ch);
        //返回结果
        if ($data) {
            curl_close($ch);

            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error".'<br>';
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);

            return false;
        }
    }

    /**
     *  作用：使用证书，以post方式提交xml到对应的接口url.
     */
    public function postXmlSSLCurl($xml, $url, $cert, $key, $second = 30)
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, $cert);
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, $key);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);

            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error".'<br>';
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);

            return false;
        }
    }

    /**
     *  作用：打印数组.
     */
    public function printErr($wording = '', $err = '')
    {
        print_r('<pre>');
        echo $wording.'</br>';
        var_dump($err);
        print_r('</pre>');
    }

    public function sendPrintPP($order){
        $items = unserialize($order['items']);
        $msg .= '<FB>###############################</FB>\r\n\r\n';
        $msg .= '<center>'.$_SESSION['SHOP']['set']['name'].'</center>\r\n';
        $msg .= '<table>';
        $msg .= '<tr><td>名称</td><td>数量</td><td>单价</td></tr>';
        foreach ($items as $key => $value) {
            if($value['skuattr']){
                $value['name'].='['.$value['skuattr'].']';
            }
            $msg .= '<tr><td>'.$value['name'].'</td><td>'.$value['num'].'</td><td>'.$value['price'].'</td></tr>';
        }
        $msg .= '</table>\r\n\r\n';
        $msg .= '订单编号：'.$order['oid'].'\r\n';
        $msg .= '总金额：'.$order['totalprice'].' 元\r\n';
        if($order['msg']){
            $msg .= '备注：'.$order['msg'].'\r\n';
        }
        if($order['sendtime'] != 25){
            $msg .= '送货时间：'.$order['sendtime'].' 点\r\n';
        }
        
        $msg .= '收货人：'.$order['vipname'].'\r\n';
        $msg .= '电话：'.$order['vipmobile'].'\r\n';
        $msg .= '地址：'.$order['vipaddress'].'\r\n\r\n';
        $msg .= '<QR>http://weixin.qq.com/r/STt_ZpTEy8m0rdAF925b</QR>';
        $msg .= '<center>微信关注公众号 购物更便利</center>';
        $this->printPP($msg);
        //如果printPP在common/function里面,则printPP($msg)即可调用;
    }
    function http_post($url,$data){ // 模拟提交数据函数      
    $curl = curl_init(); // 启动一个CURL会话      
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址                  
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检测    
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在      
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:')); //解决数据包大不能提交     
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转      
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer      
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求      
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包      
    curl_setopt($curl, CURLOPT_COOKIEFILE, $GLOBALS['cookie_file']); // 读取上面所储存的Cookie信息      
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循     
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容      
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回 
           
    $tmpInfo = curl_exec($curl); // 执行操作      
    if (curl_errno($curl)) {      
       echo 'Errno'.curl_error($curl);      
    }      
    curl_close($curl); // 关键CURL会话      
    return $tmpInfo; // 返回数据      
}    
//打印机
function printPP($msg)
{
    $apiKey       = '3265273d3eb1226f9cc627698b79065ebcbcf8b6';//apiKey
    $mKey         = 'yin2mrdvn6er';//秘钥
    $partner      = '4711';//用户id
    $machine_code = '4004509032';//打印机终端号
    $ti = time();
    $params = array(
            'partner'=>$partner,
            'machine_code'=>$machine_code,
            'time'=>$ti
    );
    $sign = $this->generateSign($params,$apiKey,$mKey);
    // $msg .= '<FB>###############################</FB>\r\n\r\n';
    // $msg .= '<center>Eschool校园服务平台</center>\r\n';
    // $msg .= '<table>';
    // $msg .= '<tr><td> 名称 </td><td> 数量 </td><td> 金额 </td></tr>';
    // $msg .= '<tr><td> 农夫山泉 </td><td> 1 </td><td> 2 </td></tr>';
    // $msg .= '<tr><td> 辣条 </td><td> 5 </td><td> 200 </td></tr>';
    // $msg .= '<tr><td> 越南特产 </td><td> 1 </td><td> 7 </td></tr>';
    // $msg .= '<tr><td> 小茗同学 </td><td> 1 </td><td> 800 </td></tr>';
    // $msg .= '</table>\r\n\r\n';
    // $msg .= '总金额：132 元\r\n';
    // $msg .= '备注：我跟你们说啊\r\n';
    // $msg .= '送货时间：17点\r\n';
    // $msg .= '收货人：王健\r\n';
    // $msg .= '收货电话：15296476891\r\n';
    // $msg .= '地址：财院21栋801\r\n\r\n';
    // $msg .= '<QR>http://weixin.qq.com/r/t3UlPWrEezcErS5e9yCl</QR>';
    // $msg .= '<center>微信扫二维码 校内购物通道</center>';
    $params['sign'] = $sign;
    $params['content'] = $msg;

    $url = 'open.10ss.net:8888';//接口端点

    $p = '';
    foreach ($params as $k => $v) {
        $p .= $k.'='.$v.'&';
    }
    $data = rtrim($p, '&');
    return $this->http_post($url,$data);
}
function generateSign($params, $apiKey, $msign)
{
    //所有请求参数按照字母先后顺序排
    ksort($params);
    //定义字符串开始所包括的字符串
    $stringToBeSigned = $apiKey;
    //把所有参数名和参数值串在一起
    foreach ($params as $k => $v)
    {
        $stringToBeSigned .= urldecode($k.$v);
    }
    unset($k, $v);
    //定义字符串结尾所包括的字符串
    $stringToBeSigned .= $msign;
    //使用MD5进行加密，再转化成大写
    return strtoupper(md5($stringToBeSigned));
}
}

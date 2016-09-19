<?php
// 本类由系统自动生成，仅供测试用途
namespace App\Controller;

class FxController extends BaseController
{
    public static $_set; //缓存全局配置
    public static $_wx; //缓存微信对象
    public function _initialize()
    {
        //你可以在此覆盖父类方法
        parent::_initialize();
        self::$_set = M('Set')->find();
        $options['appid'] = self::$_set['wxappid'];
        $options['appsecret'] = self::$_set['wxappsecret'];
        self::$_wx = new \Util\Wx\Wechat($options);
    }

    public function index()
    {
        $data = self::$WAP['vip'];
        if ($data['isfxgd']) {
            $data['fxname'] = '超级VIP';
        } else {
            if ($data['isfx']) {
                $data['fxname'] = self::$SHOP['set']['fxname'];
            } else {
                $data['fxname'] = '非' . self::$SHOP['set']['fxname'];
            }
        }
        $mvip = M('Vip');
        //超级VIP取20层，普通取3层
        //dump($data['plv']);
        $maxlv = $data['plv'] + 3;
        $likepath = $data['path'] . '-' . $data['id'];
        //取出超级VIP团队总人数
        //		if($data['isfxgd']){
        //			$maphg['plv']=array('elt',$data['plv']+20);
        //			$maphg['path']=array('like',$likepath.'%');
        //			$data['total_hglink']=$mvip->field('id')->where($maphg)->count();
        //		}
        //取出符合的所有会员ID;
        //两次模糊查询
        //1:取出第一层，2:取出其他层
        $firstlv = $data['plv'] + 1;
        $firstpath = $likepath;
        $mapfirst['plv'] = $firstlv;
        $mapfirst['path'] = $firstpath;
        $firstsub = $mvip->field('id,plv,path,nickname')->where($mapfirst)->select();
        //dump($sub);
        if ($firstsub) {
            //模糊查询第二层和第三层
            $maplike['plv'] = array('gt', $firstlv);
            $maplike['plv'] = array('elt', $maxlv);
            $maplike['path'] = array('like', $likepath . '-%');
            $sesendsub = $mvip->field('id,plv,path,nickname')->where($maplike)->select();
            //dump($firstsub);
            //dump($sesendsub);
            //合并两个数组
            if ($sesendsub) {
                $sub = array_merge($firstsub, $sesendsub);
            } else {
                $sub = $firstsub;
            }

            //分组
            $subarr = array();
            foreach ($sub as $v) {
                //按层级分组
                $subarr[$v['plv']] = $subarr[$v['plv']] . $v['id'] . ',';
                //array_push($subarr[$v['plv']],$v['id']);
            }

            $subarr = array_values($subarr);
            //dump($subarr);
            //找出关联订单
            $shopset = M('ShopSet')->find();
            $morder = M('ShopOrder');
            $fx1rate = $shopset['fx1rate'];
            $fx2rate = $shopset['fx2rate'];
            $fx3rate = $shopset['fx3rate'];
            $commission = D('Commission');
            if ($fx1rate && $subarr[0]) {
                $tmprate = $fx1rate;
                $tmplv = $data['plv'] + 1;
                $maporder['ispay'] = 1;
                $maporder['status'] = array('in', array('2', '3'));
                $maporder['vipid'] = array('in', in_parse_str($subarr[0]));
                $tmptotal = $morder->where($maporder)->sum('payprice');
                $fx1total = $tmptotal * ($tmprate / 100);
                // 添加修改
                $tempids = array();
                $temp = $morder->field('id')->where($maporder)->select();
                foreach ($temp as $v) {
                    array_push($tempids, $v['id']);
                }
                $fx1total = $commission->ordersCommission('fx1rate', $tempids);
            } else {
                $fx1total = 0;
            }
            if ($fx2rate) {
                $tmprate = $fx2rate;
                $tmplv = $data['plv'] + 2;
                $maporder['ispay'] = 1;
                $maporder['status'] = array('in', array('2', '3'));
                $maporder['vipid'] = array('in', in_parse_str($subarr[1]));

                $tmptotal = $morder->where($maporder)->sum('payprice');
                $fx2total = $tmptotal * ($tmprate / 100);
                // 添加修改
                $tempids = array();
                $temp = $morder->field('id')->where($maporder)->select();
                foreach ($temp as $v) {
                    array_push($tempids, $v['id']);
                }
                $fx2total = $commission->ordersCommission('fx2rate', $tempids);
            } else {
                $fx2total = 0;
            }
            if ($fx3rate) {
                $tmprate = $fx3rate;
                $tmplv = $data['plv'] + 3;
                $maporder['ispay'] = 1;
                $maporder['status'] = array('in', array('2', '3'));
                $maporder['vipid'] = array('in', in_parse_str($subarr[2]));

                $tmptotal = $morder->where($maporder)->sum('payprice');
                $fx3total = $tmptotal * ($tmprate / 100);
                // 添加修改
                $tempids = array();
                $temp = $morder->field('id')->where($maporder)->select();
                foreach ($temp as $v) {
                    array_push($tempids, $v['id']);
                }
                $fx3total = $commission->ordersCommission('fx3rate', $tempids);
            } else {
                $fx3total = 0;
            }
            $data['fxmoney'] = number_format(($fx1total + $fx2total + $fx3total), 2);

        } else {
            $data['fxmoney'] = 0.00;
        }
        $maptx['vipid'] = $data['id'];
        $maptx['status'] = 1;
        $txtotal = M('VipTx')->where($maptx)->sum('txprice');
        if ($txtotal > 0) {
            $data['txmoney'] = number_format($txtotal, 2);
        } else {
            $data['txmoney'] = number_format(0, 2);
        }
        //dump($txtotal);
        $this->assign('data', $data);
        $this->display();
    }

    public function paihang()
    {
        $m = M('Vip');
        $map['isfx'] = 1;
        $map['total_xxyj'] = array('gt', 0);
        $cache = $m->where($map)->limit(50)->order('total_xxyj desc')->select();
        $this->assign('cache', $cache);
        $this->display();
    }

    public function myqrcode()
    {
        //追入分享特效
        $options['appid'] = self::$_wxappid;
        $options['appsecret'] = self::$_wxappsecret;
        $wx = new \Util\Wx\Wechat($options);
        //生成JSSDK实例
        $opt['appid'] = self::$_wxappid;
        $opt['token'] = $wx->checkAuth();
        $opt['url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $jssdk = new \Util\Wx\Jssdk($opt);
        $jsapi = $jssdk->getSignPackage();
        if (!$jsapi) {
            die('未正常获取数据！');
        }
        $this->assign('jsapi', $jsapi);
        //这里是判断是是他人的链接
        $pid=I('pid');
        if ($pid) {
            $img = U('getuserqrcode',array('pid'=>$pid));
            // $img = __ROOT__."/QRcode/promotion/" . $vip['openid'] . '.jpg';
        }else{
           $vip = self::$WAP['vip'];
           $img = U('getuserqrcode');
           // $img = __ROOT__."/QRcode/promotion/" . $vip['openid'] . '.jpg'; 
        }
        $this->assign('img', $img);
        $this->assign('vip', $vip);
        $this->display();
    }
    public function getuserqrcode(){
        $vip = self::$WAP['vip'];
        $pid=I('pid');
        if ($pid) {
            $vip=M('vip')->where('id='.$pid)->find();
        }
        $options['appid'] = self::$_wxappid;
        $options['appsecret'] = self::$_wxappsecret;
        $wx = new \Util\Wx\Wechat($options);
        $background = $this->createQrcodeBg();
        $qrcode = $this->createQrcode($vip['id'], $vip['openid']);
        // 获取头像信息
        $mark == false; // 是否需要写入将图片写入文件
        $headimg = $this->getRemoteHeadImage($vip['headimgurl']);
        if (!$headimg) {// 没有头像先从头像库查找，再没有就选择默认头像
            if (file_exists('./QRcode/headimg/' . $vip['openid'] . '.jpg')) { // 获取不到远程头像，但存在本地头像，需要更新
                $headimg = file_get_contents('./QRcode/headimg/' . $vip['openid'] . '.jpg');
            } else {
                $headimg = file_get_contents('./QRcode/headimg/' . 'default' . '.jpg');
            }
            $mark = true;
        }
        $headimg = imagecreatefromstring($headimg);
        // 获取头像信息 结束

        // 生成二维码推广图片=======================

        // Combine QRcode and background and HeadImg
        $b_width = imagesx($background);
        $b_height = imagesy($background);
        $q_width = imagesx($qrcode);
        $q_height = imagesy($qrcode);
        $h_width = imagesx($headimg);
        $h_height = imagesy($headimg);
        imagecopyresampled($background, $qrcode, $b_width * 0.24, $b_height * 0.5, 0, 0, 297, 297, $q_width, $q_height);
        imagecopyresampled($background, $headimg, $b_width * 0.10, 12, 0, 0, 120, 120, $h_width, $h_height);

        // Set Font Type And Color
        $fonttype = './Public/Common/fonts/wqy-microhei.ttc';
        $fontcolor = imagecolorallocate($background, 0x00, 0x00, 0x00);

        // Combine All And Text, Then store in local
        imagettftext($background, 18, 0, 280, 100, $fontcolor, $fonttype, $vip['nickname']);
        imagejpeg($background, './QRcode/promotion/' . $vip['openid'] . '.jpg');
        
        $fileres = file_get_contents('./QRcode/promotion/' . $vip['openid'] . '.jpg');
        addslashes($filers);
        header('Content-type: image/jpeg');
        echo $fileres;
    }
    // 获取头像函数
    function getRemoteHeadImage($headimgurl)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_URL, $headimgurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $headimg = curl_exec($ch);
        curl_close($ch);
        return $headimg;
    }
    // 创建背景
    function createQrcodeBg()
    {
        $autoset = M('Autoset')->find();
        if (!file_exists('./' . $autoset['qrcode_background'])) {
            $background = imagecreatefromstring(file_get_contents('./QRcode/background/default.jpg'));
        } else {
            $background = imagecreatefromstring(file_get_contents('./' . $autoset['qrcode_background']));
        }
        return $background;
    }
    // 创建二维码
    function createQrcode($id, $openid)
    {

        if ($id == 0 || $openid == '') {
            return false;
        }
        if (!file_exists('./QRcode/qrcode/' . $openid . '.png')) {
            //二维码进入系统
//            $url = 'http://' . $_SERVER['HTTP_HOST'] . __ROOT__ . '/App/Shop/index/ppid/' . $id;
//            \Util\QRcode::png($url, './QRcode/qrcode/' . $openid . '.png', 'L', 6, 2);

            //二维码进入公众号
            $this->getQRCode($id, $openid);

        }
        $qrcode = imagecreatefromstring(file_get_contents('./QRcode/qrcode/' . $openid . '.png'));

        return $qrcode;
    }
    public function getQRCode($id, $openid)
    {
        $ticket = self::$_wx->getQRCode($id, 1);

        self::$_ppvip->where(array("id" => $id))->save(array("ticket" => $ticket["ticket"]));
        $qrUrl = self::$_wx->getQRUrl($ticket["ticket"]);

        $data = file_get_contents($qrUrl);
        file_put_contents('./QRcode/qrcode/' . $openid . '.png', $data);
    }
    // public function getqrcode()
    // {
    //     $set = M('Set')->find();
    //     $url = $set['wxurl'] . '/App/Shop/index/ppid/' . self::$WAP['vipid'];
    //     $QR = new \Util\QRcode();
    //     $QR::png($url);
    // }

    public function myuser()
    {
        $m = M('vip');
        $type = intval(I('type')) ? intval(I('type')) : 1;
        $vipid = $_SESSION['WAP']['vipid'];
        if ($type == 1) {
            $this->assign('type', self::$SHOP['set']['fx1name']);
            $cache = $m->where(array('pid' => $vipid))->order('ctime desc')->limit(50)->select();
            $total = $m->where(array('pid' => $vipid))->count();
        }
        if ($type == 2) {
            $this->assign('type', self::$SHOP['set']['fx2name']);
            $arr = array();
            $tmp = $m->field('id')->where(array('pid' => $vipid))->order('ctime desc')->limit(50)->select();
            foreach ($tmp as $v) {
                array_push($arr, $v['id']);
            }
            $cache = $m->where(array('pid' => array('in', in_parse_str($arr))))->select();
            $total = $m->where(array('pid' => array('in', in_parse_str($arr))))->count();
        }
        if ($type == 3) {
            $this->assign('type', self::$SHOP['set']['fx3name']);
            $arr = array();
            $tmp = $m->field('id')->where(array('pid' => $vipid))->select();
            foreach ($tmp as $v) {
                array_push($arr, $v['id']);
            }
            $tmp2 = $m->field('id')->where(array('pid' => array('in', in_parse_str($arr))))->select();
            $arr2 = array();
            foreach ($tmp2 as $v) {
                array_push($arr2, $v['id']);
            }

            if (!$arr2) {
                $arr2 = '';
            }
            $cache = $m->where(array('pid' => array('in', in_parse_str($arr2))))->order('ctime desc')->limit(50)->select();
            $total = $m->where(array('pid' => array('in', in_parse_str($arr2))))->count();
        }
        $this->assign('total', $total);
        $this->assign('cache', $cache);
        $this->display();
    }

    public function dslog()
    {
        $m = M('fx_dslog');
        $map['to'] = $_SESSION['WAP']['vipid'];
        $map['status'] = 1;
        $cache = $m->where($map)->limit(50)->order('ctime desc')->select();
        $this->assign('cache', $cache);
        $this->display();
    }

    public function fxlog()
    {
        $m = M('fx_syslog');
        $map['to'] = $_SESSION['WAP']['vipid'];
        $map['status'] = 1;
        $cache = $m->where($map)->limit(50)->order('ctime desc')->select();
        $this->assign('cache', $cache);
        $this->display();
    }

    public function tjlog()
    {
        $m = M('fx_log_tj');
        $map['vipid'] = $_SESSION['WAP']['vipid'];
        $cache = $m->where($map)->limit(50)->order('ctime desc')->select();
        $this->assign('cache', $cache);
        $this->display();
    }

    public function about()
    {
        $this->display();
    }

}

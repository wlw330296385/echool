<?php
// 本类由系统自动生成，仅供测试用途
namespace App\Controller;

class ShopController extends BaseController
{

    public function _initialize()
    {
        //你可以在此覆盖父类方法   
        parent::_initialize();
        $shopset = M('Shop_set')->where('id=1')->find();
        if ($shopset['pic']) {
            $listpic = $this->getPic($shopset['pic']);
            $shopset['sharepic'] = $listpic['imgurl'];
        }
        if ($shopset) {
            self::$WAP['shopset'] = $_SESSION['WAP']['shopset'] = $shopset;
            $this->assign('shopset', $shopset);
        } else {
            $this->diemsg(0, '您还没有进行商城配置！');
        }
    }

    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public function index()
    {
        //追入分享特效
        $options['appid'] = self::$_wxappid;
        $options['appsecret'] = self::$_wxappsecret;
        $wx = new \Util\Wx\Wechat($options);

        //生成JSSDK实例
        $opt['appid'] = self::$_wxappid;
        $opt['token'] = $wx->checkAuth();
        $opt['url'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        $jssdk = new \Util\Wx\Jssdk($opt);
        $jsapi = $jssdk->getSignPackage();
        if (!$jsapi) {
            die('未正常获取数据！');
        }
        $this->assign('jsapi', $jsapi);
        //首页活动图片
        $m = M('Shop_goods');
        $group = M('Shop_group')->select();
        foreach($group as $k=>$v){
            $group[$k]['pic'] = $this->getPic($v['icon']);
        }
        $this->assign('group', $group);
        //重磅推荐
        $mtid = M('Shop_group')->where('id=1')->getField('goods');
        $mrtj = $m->where(array('id' => array('in', in_parse_str($mtid))))->select();
        foreach ($mrtj as $k => $v) {
            $listpic = $this->getPic($v['listpic']);
            $mrtj[$k]['imgurl'] = $listpic['imgurl'];
        }
        // var_dump($mrtj);
        $this->assign('mrtj', $mrtj);
        $type = intval(I('type')) ? intval(I('type')) : 0;
        $this->assign('type', $type);
        if ($type) {
            $map['cid'] = $type;
        }
        $map['status'] = 1;
        $cache = $m->where($map)->order('sells desc')->select();
        foreach ($cache as $k => $v) {
            $listpic = $this->getPic($v['listpic']);
            $cache[$k]['imgurl'] = $listpic['imgurl'];
        }
        $this->assign('type', $type);
        $this->assign('cache', $cache);
        //分组调用
        // $mapx['id'] = array('in', in_parse_str(self::$WAP['shopset']['indexgroup']));
        $indexicons = M('Shop_cate')->order('sorts asc')->select();
        // foreach ($indexicons as $k => $v) {
        //     $listpic = $this->getPic($v['icon']);
        //     $indexicons[$k]['iconurl'] = $listpic['imgurl'];
        //     $indexicons[$k]['ison'] = $type == $v['id'] ? '1' : '0';
        //     // 获取下级
        //     if ($indexicons[$k]['soncate']) {
        //         $son = M('Shop_cate')->where(array('id' => array('in', in_parse_str($indexicons[$k]['soncate']))))->select();
        //         foreach ($son as $kk => $vv) {
        //             $temp = $this->getPic($vv['icon']);
        //             $son[$kk]['iconurl'] = $temp['imgurl'];
        //             $son[$kk]['ison'] = $type == $vv['id'] ? '1' : '0';
        //             $son[$kk]['url'] = U('App/Shop/index#nav', array('type' => $v['id']));
        //         }
        //         $indexicons[$k]['son'] = 1;
        //         $indexicons[$k]['sonlist'] = $son;
        //         $indexicons[$k]['url'] = "javascript:;";
        //     } else {
        //         $indexicons[$k]['son'] = 0;
        //         $indexicons[$k]['url'] = U('App/Shop/index#nav', array('type' => $v['id']));
        //     }

        // }
        //首页轮播图集
        $indexalbum = M('Shop_ads')->where('id', array('in', in_parse_str(self::$WAP['shopset']['indexalbum'])))->order('id desc')->limit(3)->select();
        foreach ($indexalbum as $k => $v) {
            $listpic = $this->getPic($v['pic']);
            $indexalbum[$k]['imgurl'] = $listpic['imgurl'];
        }
        $this->assign('indexalbum', $indexalbum);
//        dump($indexicons);
        $articals = M('artical')->limit(5)->select();
        $this->assign('articals', $articals);
        $this->assign('indexicons', $indexicons);
        //首页分享特效
        //dump(self::$WAP['vip']['ppid']);
        if (!self::$WAP['vip']['subscribe']) {
//            if (self::$WAP['vip']['pid']) {
//                $father = M('Vip')->where('id=' . self::$WAP['vip']['pid'])->find();
//                $this->assign('showsub', 1);
//                if ($father) {
//                    $this->assign('showfather', 1);
//                    $this->assign('father', $father);
//                } else {
//                    $this->assign('showfather', 0);
//                }
//
//            } else {
                $this->assign('showsub', 1);
//                $this->assign('showfather', 0);
//            }
        } else {
            $this->assign('showsub', 0);
        }
        $shop_cate=$this->category();
        $this->assign('shop_cate',$shop_cate);
        // dump($shop_cate);
        $this->display();
    }
    public function lists(){
        $p=I('get.p')==''?'1':I('get.p');
        $n=I('get.n')==''?'4':I('get.n');
        $gid = I('gid');
        $type = I('type');
        $k = I('get.k');
        //排序
        $ord='sorts asc';
        $sorts=I('post.sorts');
        $order=I('post.order');
        if($sorts&&$order){
            $order=$order=='1'?'DESC':'ASC';
            $ord=$sorts.' '.$order;
        }
        // 查询满足要求的总记录数
        if($gid){
            $w['id'] = $gid;
            $group = M('shop_group')->where($w)->find();
            $gw['id'] = array('in',$group['goods']);
            $this->assign('title',$group['name']);
            $this->assign('gid',$gid);
            $ord='field(id,'.$group['goods'].');';

        }
        if($type){
            $gw['cid'] = $type;
            $this->assign('title','分类');
            $this->assign('type',$type);
        }
        if($k){
            $gw['name'] = array('like','%'.$k.'%');
            $this->assign('title','搜索');
            $this->assign('k',$k);
        }
        // $count =M('shop_goods')->where($gw)->count();
        // $Page  = new \Think\Page($count,5);// 实例化分页类 传入总记录数和每页显示的记录数
        // $show  = $Page->show();//分页显示输出
        // $limit = $Page->firstRow.','.$Page->listRows;
        // $cache = M('shop_goods')->where($gw)->limit($limit)->select();//用页码输出
        $cache = M('shop_goods')->where($gw)->order($ord)->select();
        //按照销量排序
        // if ($sorts=='sells') {  
        // //将假销量替换真销量
        //     foreach ($cache as $key => $value) {
        //         if ($value['dissells']>0) {
        //             $value['sells']=$value['dissells'];
        //         }
        //     }
        //     usort($cache, "$this->mySort");
        // }
        foreach ($cache as $k => $v) {
            $listpic = $this->getPic($v['listpic']);
            $cache[$k]['imgurl'] = $listpic['imgurl'];
        }
        if(IS_AJAX){
            die(json_encode($cache));
        }
        else{
            $this->assign('pages',$show);// 赋值分页输出
            $this->assign('cache',$cache);
            $this->display();
        }
        
    }
    //这里递归分类
    public function category($pid="0",$arr=array()){
        $select=M('shop_cate');
        $list=$select->where('pid='.$pid)->order('sorts asc')->select();
        $category_id=I('get.id');
        foreach ($list as $key => $value) {                     
            // $arr[]=$value;       
            $arr[]=$this->category($value['id'],$value);           
        }       
        return $arr;
    }
    //woo这里是加载分类
    public function goods_cate(){
        $order=I('get.order')==''?'id':I('get.order');
        $id=I('get.id');
        if ($id) {
            $goods=M('shop_goods')
                        ->where('cid='.$id)
                        ->order($order.' desc')
                        ->select();
        }else{
             $goods=M('shop_goods')
                        ->where(1)
                        ->order($order.' desc')
                        ->select();
        }
        die(json_encode($goods));
    }
    public function goods_list(){
        $id=I('get.id');
        $p=I('get.p')==''?'1':I('get.p');
        $n=I('get.p')==''?'10':I('get.p');
        
        $order=I('get.order')==''?'id':I('get.order');
        if ($id) {
            $arr=M('shop_goods')
                        ->where('cid='.$id)
                        ->order($order.' desc')
                        ->page($p)
                        ->limit($n)
                        ->select();
        }else{
             $arr=M('shop_goods')
                        ->where(1)
                        ->order($order.' DESC')
                        ->page($p)
                        ->limit($n)
                        ->select();
        }
        if(!$arr){
            $arr=-1;
        }
         foreach ($arr as $k => $v) {
            $listpic = $this->getPic($v['listpic']);
            $arr[$k]['imgurl'] = $listpic['imgurl'];
        }
        die(json_encode($arr));
    }
    public function goods()
    {
        $id = I('id') ? I('id') : $this->diemsg(0, '缺少ID参数!');
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
        //获取商品信息
        $m = M('Shop_goods');
        $cache = $m->where('id=' . $id)->find();
        if (!$cache) {
            $this->error('此商品已下架！', U('App/Shop/index'));
        }
        if (!$cache['status']) {
            $this->error('此商品已下架！', U('App/Shop/index'));
        }
        //自动计数
        $rclick = $m->where('id=' . $id)->setInc('clicks', 1);
        //读取标签
        foreach (explode(',', $cache['lid']) as $k => $v) {
            $label[$k] = M('ShopLabel')->where(array('id' => $v))->getField('name');
        }
        $cache['label'] = $label;
        $this->assign('cache', $cache);
        if ($cache['issku']) {
            if ($cache['skuinfo']) {
                $skuinfo = unserialize($cache['skuinfo']);
                $skm = M('Shop_skuattr_item');
                foreach ($skuinfo as $k => $v) {
                    $checked = explode(',', $v['checked']);
                    $attr = $skm->field('path,name')->where('pid=' . $v['attrid'])->select();
                    foreach ($attr as $kk => $vv) {
                        $attr[$kk]['checked'] = in_array($vv['path'], $checked) ? 1 : '';
                    }
                    $skuinfo[$k]['allitems'] = $attr;
                }
                $this->assign('skuinfo', $skuinfo);
            } else {
                $this->diemsg(0, '此商品还没有设置SKU属性！');
            }
            $skuitems = M('Shop_goods_sku')->field('sku,skuattr,price,num,hdprice,hdnum')->where(array('goodsid' => $id, 'status' => 1))->select();
            if (!$skuitems) {
                $this->diemsg(0, '此商品还未生成SKU!');
            }
            $skujson = array();
            foreach ($skuitems as $k => $v) {
                $skujson[$v['sku']]['sku'] = $v['sku'];
                $skujson[$v['sku']]['skuattr'] = $v['skuattr'];
                $skujson[$v['sku']]['price'] = $v['price'];
                $skujson[$v['sku']]['num'] = $v['num'];
                $skujson[$v['sku']]['hdprice'] = $v['hdprice'];
                $skujson[$v['sku']]['hdnum'] = $v['hdnum'];
            }
            $this->assign('skujson', json_encode($skujson));
        }
        //绑定图集
        if ($cache['album']) {
            $appalbum = $this->getAlbum($cache['album']);
            if ($appalbum) {
                $this->assign('appalbum', $appalbum);
            }
        }
        //绑定图片
        if ($cache['pic']) {
            $apppic = $this->getPic($cache['pic']);
            if ($apppic) {
                $this->assign('apppic', $apppic);
            }
        }
        //绑定购物车数量
        $basketnum = M('Shop_basket')->where(array('sid' => 0, 'vipid' => self::$WAP['vipid']))->sum('num');
        $this->assign('basketnum', $basketnum);
        //绑定登陆跳转地址
        $backurl = base64_encode(U('App/Shop/goods', array('id' => $id)));
        $loginback = U('App/Vip/login', array('backurl' => $backurl));
        $this->assign('loginback', $loginback);
        $this->assign('lasturl', $backurl);
        $this->display();
    }
//这里是购物车;
    public function basket()
    {
        $sid = I('sid') <> '' ? I('sid') : $this->diemsg(0, '缺少SID参数');//sid可以为0
        $lasturl = I('lasturl') ? I('lasturl') : $this->diemsg(0, '缺少LastURL参数');
        $basketlasturl = base64_decode($lasturl);
        $basketurl = U('App/Shop/basket', array('sid' => $sid, 'lasturl' => $lasturl));
        $backurl = base64_encode($basketurl);
        $basketloginurl = U('App/Vip/login', array('backurl' => $backurl));
        $re = $this->checkLogin($backurl);
        //保存当前购物车地址
        $this->assign('basketurl', $basketurl);
        //保存登陆购物车地址
        $this->assign('basketloginurl', $basketloginurl);
        //保存购物车前地址
        $this->assign('basketlasturl', $basketlasturl);
        //保存购物车加密地址，用于OrderMaker正常返回
        $this->assign('lasturlencode', $lasturl);
        //已登陆
        $m = M('Shop_basket');
        $mgoods = M('Shop_goods');
        $msku = M('Shop_goods_sku');
        $returnurl = base64_decode($lasturl);
        $this->assign('returnurl', $returnurl);
        //找到vip所属购物车
        $cache = $m->where(array('sid' => $sid, 'vipid' => $_SESSION['WAP']['vipid']))->select();
        //错误标记
        $errflag = 0;
        //等待删除ID
        $todelids = '';
        //totalprice
        $totalprice = 0;
        //totalnum
        $totalnum = 0;
        // 循环该用户所有购物车
        foreach ($cache as $k => $v) {
            //sku模型
            $goods = $mgoods->where('id=' . $v['goodsid'])->find();
            if (($goods['shortselle']<(time()*1000))&&$goods['shortselle']>0) {
                $todelids = $todelids . $v['id'] . ',';
            }else{
                //限购判断
                $count=0;
                $limitnum=M('Shop_goods')->where(array('id'=>$goods['id'],'islimitsell'=>1))->getField('limitsell');            
                if ($limitnum) {
                    $limitnum=intval($limitnum);
                    $count=M('Shop_limitbuy')->where(array('vipid'=>$_SESSION['WAP']['vipid'],'goodsid'=>$goods['id'],'status'=>1))->sum('totalnum');
                    // $sum=M('Shop_limitbuy')->where(array('vipid'=>$_SESSION['WAP']['vipid'],'goodsid'=>$goods['id'],'status'=>2))->sum('totalnum');
                    if ($count<$limitnum){
                        $v['num']=$limitnum-$count;
                    }else{
                        $todelids = $todelids . $v['id'] . ',';
                    }
                }
                $pic = $this->getPic($goods['pic']);
                if ($v['sku']) {
                    //取商品数据             
                    if ($goods['issku'] && $goods['status']) {
                        $map['sku'] = $v['sku'];
                        $sku = $msku->where($map)->find();//在属性表中找到对应属性;
                        if ($sku['status']) {
                            if ($sku['num']) {
                                //调整购买量
                                $cache[$k]['name'] = $goods['name'];
                                $cache[$k]['skuattr'] = $sku['skuattr'];
                                $cache[$k]['num'] = $v['num'] > $sku['num'] ? $sku['num'] : $v['num'];
                                $cache[$k]['price'] = $sku['price'];
                                $cache[$k]['total'] = $sku['num'];
                                $cache[$k]['pic'] = $pic['imgurl'];
                                $cache[$k]['islimitsell']=$goods['islimitsell'];
                                $totalnum = $totalnum + $cache[$k]['num'];
                                $totalprice = $totalprice + $cache[$k]['price'] * $cache[$k]['num'];
                            } else {
                                //无库存删除
                                $todelids = $todelids . $v['id'] . ',';
                                unset($cache[$k]);
                            }
                        } else {
                            //下架删除
                            $todelids = $todelids . $v['id'] . ',';
                            unset($cache[$k]);
                        }
                    } else {
                        //下架删除
                        $todelids = $todelids . $v['id'] . ',';
                        unset($cache[$k]);
                    }

                } else {
                    if ($goods['status']) {
                        if ($goods['num']) {
                            //调整购买量
                            $cache[$k]['name'] = $goods['name'];
                            // $cache[$k]['skuattr'] = $sku['skuattr'];
                            $cache[$k]['num'] = $v['num'] > $goods['num'] ? $goods['num'] : $v['num'];
                            $cache[$k]['price'] = $goods['price'];
                            $cache[$k]['total'] = $goods['num'];
                            $cache[$k]['pic'] = $pic['imgurl'];
                            $cache[$k]['islimitsell']=$goods['islimitsell'];
                            $totalnum = $totalnum + $cache[$k]['num'];
                            $totalprice = $totalprice + $cache[$k]['price'] * $cache[$k]['num'];
                        } else {
                            //无库存删除
                            $todelids = $todelids . $v['id'] . ',';
                            unset($cache[$k]);
                        }
                    } else {
                        //下架删除
                        $todelids = $todelids . $v['id'] . ',';
                        unset($cache[$k]);
                    }
                }  
                }
            

        }
        if ($todelids) {
            $rdel = $m->delete($todelids);
            if (!$rdel) {
                $this->error('购物车获取失败，请重新尝试！');
            }
        }

        $this->assign('cache', $cache);
        $this->assign('totalprice', $totalprice);
        $this->assign('totalnum', $totalnum);
        $this->display();
    }

    //添加购物车
    public function addtobasket()
    {
        if (IS_AJAX) {
            $m = M('Shop_basket');
            $data = I('post.');

            if (!$data) {
                $info['status'] = 0;
                $info['msg'] = '未获取数据，请重新尝试';
                $this->ajaxReturn($info);
            }        

            //判断是否限购
        $count=0;
        $limitnum=M('Shop_goods')->where(array('id'=>$data['goodsid'],'islimitsell'=>1))->getField('limitsell');       
        if ($limitnum) {
            $limitnum=intval($limitnum);
            $count=M('Shop_limitbuy')->where(array('vipid'=>$data['vipid'],'goodsid'=>$data['goodsid']))->sum('totalnum');           
            if ($count>=$limitnum) {
                $info=array(
                    'msg'=>'大于购买次数',
                    'total'=>0,
                    'status'=>0
                    );
            }elseif($count<$limitnum && ($limitnum-$count)<$data['num']) {
                $info=array(
                    'msg'=>'超过购买次数',
                    'total'=>0,
                    'status'=>0
                    );
            }else{
                //区分SKU模式
                if ($data['sku']) {
                    $old = $m->where(array('sid' => $data['sid'], 'vipid' => $data['vipid'], 'sku' => $data['sku']))->find();
                    if ($old) {
                        $old['num'] = $old['num'] + $data['num'];
                        $rold = $m->save($old);
                        if ($rold === FALSE) {
                            $info['status'] = 0;
                            $info['msg'] = '添加购物车失败，请重新尝试！';
                        } else {
                            $total = $m->where(array('sid' => $data['sid'], 'vipid' => $data['vipid']))->sum('num');
                            $info['total'] = $total;
                            $info['status'] = 1;
                            $info['msg'] = '添加购物车成功！';
                        }
                    } else {
                        $rold = $m->add($data);
                        if ($rold) {
                            $info['status'] = 1;
                            $info['msg'] = '添加购物车成功！';
                        } else {
                            $info['status'] = 0;
                            $info['msg'] = '添加购物车失败，请重新尝试！';
                        }
                    }
                    } else {
                        $old = $m->where(array('sid' => $data['sid'], 'vipid' => $data['vipid'], 'goodsid' => $data['goodsid']))->find();
                        if ($old) {
                            $old['num'] = $old['num'] + $data['num'];
                            $rold = $m->save($old);
                            if ($rold === FALSE) {
                                $info['status'] = 0;
                                $info['msg'] = '添加购物车失败，请重新尝试！';
                            } else {
                                $total = $m->where(array('sid' => $data['sid'], 'vipid' => $data['vipid']))->sum('num');
                                $info['total'] = $total;
                                $info['status'] = 1;
                                $info['msg'] = '添加购物车成功！';
                            }
                        } else {
                            $rold = $m->add($data);
                            if ($rold) {
                                $total = $m->where(array('sid' => $data['sid'], 'vipid' => $data['vipid']))->sum('num');
                                $info['total'] = $total;
                                $info['status'] = 1;
                                $info['msg'] = '添加购物车成功！';
                            } else {
                                $info['status'] = 0;
                                $info['msg'] = '添加购物车失败，请重新尝试！';
                            }
                        }
                    }
                    //sku结束
                }
        }
        //这里判断限购结束  

        if (!$limitnum) {
            //区分SKU模式
                if ($data['sku']) {
                    $old = $m->where(array('sid' => $data['sid'], 'vipid' => $data['vipid'], 'sku' => $data['sku']))->find();
                    if ($old) {
                        $old['num'] = $old['num'] + $data['num'];
                        $rold = $m->save($old);
                        if ($rold === FALSE) {
                            $info['status'] = 0;
                            $info['msg'] = '添加购物车失败，请重新尝试！';
                        } else {
                            $total = $m->where(array('sid' => $data['sid'], 'vipid' => $data['vipid']))->sum('num');
                            $info['total'] = $total;
                            $info['status'] = 1;
                            $info['msg'] = '添加购物车成功！';
                        }
                    } else {
                        $rold = $m->add($data);
                        if ($rold) {
                            $info['status'] = 1;
                            $info['msg'] = '添加购物车成功！';
                        } else {
                            $info['status'] = 0;
                            $info['msg'] = '添加购物车失败，请重新尝试！';
                        }
                    }
                    } else {
                        $old = $m->where(array('sid' => $data['sid'], 'vipid' => $data['vipid'], 'goodsid' => $data['goodsid']))->find();
                        if ($old) {
                            $old['num'] = $old['num'] + $data['num'];
                            $rold = $m->save($old);
                            if ($rold === FALSE) {
                                $info['status'] = 0;
                                $info['msg'] = '添加购物车失败，请重新尝试！';
                            } else {
                                $total = $m->where(array('sid' => $data['sid'], 'vipid' => $data['vipid']))->sum('num');
                                $info['total'] = $total;
                                $info['status'] = 1;
                                $info['msg'] = '添加购物车成功！';
                            }
                        } else {
                            $rold = $m->add($data);
                            if ($rold) {
                                $total = $m->where(array('sid' => $data['sid'], 'vipid' => $data['vipid']))->sum('num');
                                $info['total'] = $total;
                                $info['status'] = 1;
                                $info['msg'] = '添加购物车成功！';
                            } else {
                                $info['status'] = 0;
                                $info['msg'] = '添加购物车失败，请重新尝试！';
                            }
                        }
                    }
                    //sku结束
        }
            $this->ajaxReturn($info);
        } else {
            $this->diemsg(0, '禁止外部访问！');
        }
    }

    //删除购物车
    public function delbasket()
    {
        if (IS_AJAX) {
            $id = I('id');
            if (!$id) {
                $info['status'] = 0;
                $info['msg'] = '未获取ID参数,请重新尝试！';
                $this->ajaxReturn($info);
            }
            $m = M('Shop_basket');
            $re = $m->where('id=' . $id)->delete();
            if ($re) {
                $info['status'] = 1;
                $info['msg'] = '删除成功，更新购物车状态...';

            } else {
                $info['status'] = 0;
                $info['msg'] = '删除失败，自动重新加载购物车...';
            }
            $this->ajaxReturn($info);
        } else {
            $this->diemsg(0, '禁止外部访问！');
        }
    }

    //清空购物车
    public function clearbasket()
    {
        if (IS_AJAX) {
            $sid = $_GET['sid'];
            //前端必须保证登陆状态
            $vipid = $_SESSION['WAP']['vipid'];
            if (!$vipid) {
                $info['status'] = 3;
                $info['msg'] = '登陆已超时，2秒后自动跳转登陆页面！';
                $this->ajaxReturn($info);
            }
            if ($sid == '') {
                $info['status'] = 0;
                $info['msg'] = '未获取SID参数,请重新尝试！';
                $this->ajaxReturn($info);
            }
            $m = M('Shop_basket');
            $re = $m->where(array('sid' => $sid, 'vipid' => $vipid))->delete();
            if ($re) {
                $info['status'] = 2;
                $info['msg'] = '购物车已清空';
                $this->ajaxReturn($info);
            } else {
                $info['status'] = 0;
                $info['msg'] = '购物车清空失败，请重新尝试！';
                $this->ajaxReturn($info);
            }
        } else {
            $this->diemsg(0, '禁止外部访问！');
        }
    }

    //购物车库存检测
    public function checkbasket()
    {
        if (IS_AJAX) {
            $sid = $_GET['sid'];
            //前端必须保证登陆状态
            $vipid = $_SESSION['WAP']['vipid'];
            if (!$vipid) {
                $info['status'] = 3;
                $info['msg'] = '登陆已超时，2秒后自动跳转登陆页面！';
                $this->ajaxReturn($info);
            }
            $arr = $_POST;
            if ($sid == '') {
                $info['status'] = 0;
                $info['msg'] = '未获取SID参数';
                $this->ajaxReturn($info);
            }
            if (!$arr) {
                $info['status'] = 0;
                $info['msg'] = '未获取数据，请重新尝试';
                $this->ajaxReturn($info);
            }
            $m = M('Shop_basket');
            $mgoods = M('Shop_goods');
            $msku = M('Shop_goods_sku');
            $data = $m->where(array('sid' => $sid, 'vipid' => $_SESSION['WAP']['vipid']))->select();
            foreach ($data as $k => $v) {
                $goods = $mgoods->where('id=' . $v['goodsid'])->find();
                if ($v['sku']) {
                    $sku = $msku->where(array('sku' => $v['sku']))->find();
                    if ($sku && $sku['status'] && $goods && $goods['issku'] && $goods['status']) {
                        $nownum = $arr[$v['id']];
                        if ($sku['num'] - $nownum >= 0) {
                            //保存购物车新库存
                            if ($nownum <> $v['num']) {
                                $v['num'] = $nownum;
                                $rda = $m->save($v);
                            }
                        } else {
                            $info['status'] = 2;
                            $info['msg'] = '存在已下架或库存不足商品！';
                            $this->ajaxReturn($info);
                        }

                    } else {
                        $info['status'] = 2;
                        $info['msg'] = '存在已下架或库存不足商品！';
                        $this->ajaxReturn($info);
                    }
                } else {
                    if ($goods && $goods['status']) {
                        $nownum = $arr[$v['id']];
                        if ($goods['num'] - $nownum >= 0) {
                            //保存购物车新库存
                            if ($nownum <> $v['num']) {
                                $v['num'] = $nownum;
                                $rda = $m->save($v);
                            }
                        } else {
                            $info['status'] = 2;
                            $info['msg'] = '存在已下架或库存不足商品！';
                            $this->ajaxReturn($info);
                        }

                    } else {
                        $info['status'] = 2;
                        $info['msg'] = '存在已下架或库存不足商品！';
                        $this->ajaxReturn($info);
                    }

                }
            }
            $info['status'] = 1;
            $info['msg'] = '商品库存检测通过，进入结算页面！';
            $this->ajaxReturn($info);
        } else {
            $this->diemsg(0, '禁止外部访问！');
        }
    }

    //立刻购买逻辑
    public function fastbuy()
    {
        if (IS_AJAX) {
            $m = M('Shop_basket');
            $data = I('post.');
            // die(json_encode($data));
            if (!$data) {
                $info['status'] = 0;
                $info['msg'] = '未获取数据，请重新尝试';
                $this->ajaxReturn($info);
                die;
            }

            //  $this->ajaxReturn($info);
            //判定是否有库存
//          if($data['sku']){
//              $gd=M('Shop_goods_sku')->where('id='.$data['sku'])->find();
//              if(!$gd['status']){
//                  $info['status']=0;
//                  $info['msg']='此产品已下架，请挑选其他产品！';
//                  $this->ajaxReturn($info);
//              }
//              if($gd['num']-$data['num']<0){
//                  $info['status']=0;
//                  $info['msg']='该属性产品缺货或库存不足，请调整购买量！';
//                  $this->ajaxReturn($info);
//              }
//          }else{
//              $info['status']=0;
//              $info['msg']='此产品已下架，请挑选其他产品！';
//              $this->ajaxReturn($info);
//              $gd=M('Shop_goods')->where('id='.$data['goodsid'])->find();
//              if(!$gd['status']){
//                  $info['status']=0;
//                  $info['msg']='此产品已下架，请挑选其他产品！';
//                  $this->ajaxReturn($info);
//              }
//              if($gd['num']-$data['num']<0){
//                  $info['status']=0;
//                  $info['msg']='该产品缺货或库存不足，请调整购买量！';
//                  $this->ajaxReturn($info);
//              }
//          }
            //限购判断
            $count=0;
            $limitnum=M('Shop_goods')->where(array('id'=>$data['goodsid'],'islimitsell'=>1))->getField('limitsell');            
            if ($limitnum) {
                $limitnum=intval($limitnum);
                $count=M('Shop_limitbuy')->where(array('vipid'=>$data['vipid'],'goodsid'=>$data['goodsid']))->sum('totalnum');
                
                if ($count>=$limitnum) {
                    $info=array(
                        'msg'=>'大于购买次数',
                        'num'=>0,
                        'status'=>0
                        );
                }elseif ($count<$limitnum && ($limitnum-$count)<$data['num']) {
                    $info=array(
                        'msg'=>'超过购买次数',
                        'num'=>0,
                        'status'=>0
                        );
                }else{
            //这里是可以购买
                //清除购物车
                $sid = 0;
                //前端必须保证登陆状态
                $vipid = $_SESSION['WAP']['vipid'];
                $re = $m->where(array('sid' => $sid, 'vipid' => $vipid))->delete();
                //购买成功删掉购物车里对应的数据并且库存减去相应的数量;
                //区分SKU模式
                if ($data['sku']) {
                    $rold = $m->add($data);//在basket里增加一条对应的信息;
                    if ($rold) {
                        $info['status'] = 1;
                        $info['msg'] = '库存检测通过！2秒后自动生成订单！';
                    } else {
                        $info['status'] = 0;
                        $info['msg'] = '通讯失败，请重新尝试！';
                    }
                    } else {
                        $rold = $m->add($data);
                        if ($rold) {
                            $info['status'] = 1;
                            $info['msg'] = '库存检测通过！2秒后自动生成订单！';
                        } else {
                            $info['status'] = 0;
                            $info['msg'] = '通讯失败，请重新尝试！';
                        }
                    }
             //可以购买结束
               }         
        }   
        if (!$limitnum) {
           //这里是可以购买
                //清除购物车
                $sid = 0;
                //前端必须保证登陆状态
                $vipid = $_SESSION['WAP']['vipid'];
                $re = $m->where(array('sid' => $sid, 'vipid' => $vipid))->delete();
                //购买成功删掉购物车里对应的数据并且库存减去相应的数量;
                //区分SKU模式
                if ($data['sku']) {
                    $rold = $m->add($data);//在basket里增加一条对应的信息;
                    if ($rold) {
                        $info['status'] = 1;
                        $info['msg'] = '库存检测通过！2秒后自动生成订单！';
                    } else {
                        $info['status'] = 0;
                        $info['msg'] = '通讯失败，请重新尝试！';
                    }
                    } else {
                        $rold = $m->add($data);
                        if ($rold) {
                            $info['status'] = 1;
                            $info['msg'] = '库存检测通过！2秒后自动生成订单！';
                        } else {
                            $info['status'] = 0;
                            $info['msg'] = '通讯失败，请重新尝试！';
                        }
                    }
             //可以购买结束
        }
            $this->ajaxReturn($info);
        } else {
            $this->diemsg(0, '禁止外部访问！');
        }
    }

    //Order逻辑
    public function orderMake()
    {
        if (IS_POST) {
            $morder = M('Shop_order');
            $data = I('post.');
            $data['items'] = stripslashes(htmlspecialchars_decode($data['items']));//这是地址;
            $data['ispay'] = 0;
            $data['status'] = 1;//订单成功，未付款
            $data['ctime'] = time();
            $data['payprice'] = $data['totalprice'];
            //代金卷流程
            // dump($data);die;
            if ($data['djqid']) {
                $mcard = M('Vip_card');
                $djq = $mcard->where('id=' . $data['djqid'])->find();
                if (!$djq) {
                    $this->error('通讯失败！请重新尝试支付！');
                }
                if ($djq['usetime']) {
                    $this->error('此代金卷已使用！');
                }
                $djq['status'] = 2;
                $djq['usetime'] = time();
                $rdjq = $mcard->save($djq);
                if (FALSE === $rdjq) {
                    $this->error('通讯失败！请重新尝试支付！');
                }
                //修改支付价格
                $data['payprice'] = $data['totalprice'] - $djq['money'];
            }
            //邮费逻辑
            if (self::$WAP['shopset']['isyf']) {
                if ($data['totalprice'] >= self::$WAP['shopset']['yftop']) {
                    $data['yf'] = 0;
                } else {
                    $data['yf'] = self::$WAP['shopset']['yf'];
                    $data['payprice'] = $data['payprice'] + $data['yf'];
                }

            } else {
                $data['yf'] = 0;
            }
            $re = $morder->add($data);
            if ($re) {
                $old = $morder->where('id=' . $re)->setField('oid', date('YmdHis') . '-' . $re);
                // 限购商品
                $items=unserialize($data['items']);
                foreach ($items as $key => $v) { 
                    if ($v['islimitsell']==1) {
                            //如果是限购商品
                               $limitsell=array(
                                'sid'=>$v['sid'],
                                'totalnum'=>$v['num'],
                                'vipid'=>$data['vipid'],
                                'vipopenid'=>$data['vipopenid'],
                                'vipname'=>$data['vipname'],
                                'vipmobile'=>$data['vipmobile'],
                                'goodsid'=>$v['goodsid'],
                                'sku'=>$v['sku'],
                                'status'=>1,
                                'ctime'=>time(),
                                'islimitsell'=>1
                                );
                               $limitbuy=M('Shop_limitbuy')->add($limitsell);
                            }
                }
                if (FALSE !== $old) {
                    //后端日志
                    $mlog = M('Shop_order_syslog');
                    $dlog['oid'] = $re;
                    $dlog['msg'] = '订单创建成功';
                    $dlog['type'] = 1;
                    $dlog['ctime'] = time();
                    $rlog = $mlog->add($dlog);
                    //清空购物车
                    $rbask = M('Shop_basket')->where(array('sid' => $data['sid'], 'vipid' => $data['vipid']))->delete();
//                  $this->success('订单创建成功，转向支付界面!',U('App/Shop/pay/',array('sid'=>$data['sid'],'orderid'=>$re)));
                    //查询库存
                    $this->redirect('App/Shop/pay/', array('sid' => $data['sid'], 'orderid' => $re));
                    
                } else {
                    $old = $morder->delete($re);
                    $this->error('订单生成失败！请重新尝试！');
                }
            } else {
                //可能存在代金卷问题
                $this->error('订单生成失败！请重新尝试！');
            }

        } else {
            //非提交状态
            $sid = $_GET['sid'] <> '' ? $_GET['sid'] : $this->diemsg(0, '缺少SID参数');//sid可以为0
            $lasturl = $_GET['lasturl'] ? $_GET['lasturl'] : $this->diemsg(0, '缺少LastURL参数');
            $basketlasturl = base64_decode($lasturl);
            $basketurl = U('App/Shop/basket', array('sid' => $sid, 'lasturl' => $lasturl));
            $backurl = base64_encode($basketurl);
            $basketloginurl = U('App/Vip/login', array('backurl' => $backurl));
            $re = $this->checkLogin($backurl);
            //保存当前购物车地址
            $this->assign('basketurl', $basketurl);
            //保存登陆购物车地址
            $this->assign('basketloginurl', $basketloginurl);
            //保存购物车前地址
            $this->assign('basketlasturl', $basketlasturl);
            //保存lasturlencode
            //保存购物车加密地址，用于OrderMaker正常返回
            $this->assign('lasturlencode', $lasturl);
            $this->assign('sid', $sid);
            //清空临时地址
            unset($_SESSION['WAP']['orderURL']);
            //已登陆
            $m = M('Shop_basket');
            $mgoods = M('Shop_goods');
            $msku = M('Shop_goods_sku');
            $cache = $m->where(array('sid' => $sid, 'vipid' => $_SESSION['WAP']['vipid']))->select();
            //错误标记
            $errflag = 0;
            //等待删除ID
            $todelids = '';
            //totalprice
            $totalprice = 0;
            //totalnum
            $totalnum = 0;
            foreach ($cache as $k => $v) {
                //sku模型
                $goods = $mgoods->where('id=' . $v['goodsid'])->find();
                $pic = $this->getPic($goods['pic']);
                
                if ($v['sku']) {
                    //取商品数据             
                    if ($goods['issku'] && $goods['status']) {
                        $map['sku'] = $v['sku'];
                        $sku = $msku->where($map)->find();
                        if ($sku['status']) {
                            if ($sku['num']) {
                                //调整购买量
                                $cache[$k]['goodsid'] = $goods['id'];
                                $cache[$k]['skuid'] = $sku['id'];
                                $cache[$k]['name'] = $goods['name'];
                                $cache[$k]['skuattr'] = $sku['skuattr'];
                                $cache[$k]['num'] = $v['num'] > $sku['num'] ? $sku['num'] : $v['num'];
                                $cache[$k]['price'] = $sku['price'];
                                $cache[$k]['total'] = $v['num'] * $sku['price'];
                                $cache[$k]['pic'] = $pic['imgurl'];
                                $cache[$k]['islimitsell']=$goods['islimitsell'];
                                $cache[$k]['dissells'] = $goods['dissells'];
                                $totalnum = $totalnum + $cache[$k]['num'];
                                $totalprice = $totalprice + $cache[$k]['price'] * $cache[$k]['num'];
                            } else {
                                //无库存删除
                                $todelids = $todelids . $v['id'] . ',';
                                unset($cache[$k]);

                            }
                        } else {
                            //下架删除
                            $todelids = $todelids . $v['id'] . ',';
                            unset($cache[$k]);
                        }
                    } else {
                        //下架删除
                        $todelids = $todelids . $v['id'] . ',';
                        unset($cache[$k]);
                    }

                } else {
                    if ($goods['status']) {
                        if ($goods['num']) {
                            //调整购买量
                            $cache[$k]['goodsid'] = $goods['id'];
                            $cache[$k]['skuid'] = 0;
                            $cache[$k]['name'] = $goods['name'];
                            // $cache[$k]['skuattr'] = $sku['skuattr'];
                            $cache[$k]['num'] = $v['num'] > $goods['num'] ? $goods['num'] : $v['num'];
                            $cache[$k]['price'] = $goods['price'];
                            $cache[$k]['total'] = $v['num'] * $goods['price'];
                            $cache[$k]['pic'] = $pic['imgurl'];
                            $cache[$k]['islimitsell']=$goods['islimitsell'];
                            $cache[$k]['dissells'] = $goods['dissells'];
                            $totalnum = $totalnum + $cache[$k]['num'];
                            $totalprice = $totalprice + $cache[$k]['price'] * $cache[$k]['num'];
                        } else {
                            //无库存删除
                            $todelids = $todelids . $v['id'] . ',';
                            unset($cache[$k]);
                        }
                    } else {
                        //下架删除
                        $todelids = $todelids . $v['id'] . ',';
                        unset($cache[$k]);
                    }
                }
            }
            if ($todelids) {
                $rdel = $m->delete($todelids);
                if (!$rdel) {
                    $this->error('购物车获取失败，请重新尝试！');
                }
            }
            //商品列表
            sort($cache);
            $allitems = serialize($cache);
            $this->assign('allitems', $allitems);
            //VIP信息
            $vipadd = I('vipadd');
            if ($vipadd) {
                $vip = M('Vip_address')->where('id=' . $vipadd)->find();
            } else {
                $vip = M('Vip_address')->where('vipid=' . $_SESSION['WAP']['vipid'])->find();
            }
            $this->assign('vip', $vip);
            //可用代金卷
            $mdjq = M('Vip_card');
            $mapdjq['type'] = 2;
            $mapdjq['vipid'] = $_SESSION['WAP']['vipid'];
            $mapdjq['status'] = 1;//1为可以使用
            $mapdjq['usetime'] = 0;
            $mapdjq['etime'] = array('gt', time());
            $mapdjq['usemoney'] = array('lt', $totalprice);
            $djq = $mdjq->field('id,money')->where($mapdjq)->select();
            $this->assign('djq', $djq);
            //邮费逻辑
            if (self::$WAP['shopset']['isyf']) {
                $this->assign('isyf', 1);
                $yf = $totalprice >= self::$WAP['shopset']['yftop'] ? 0 : self::$WAP['shopset']['yf'];
                $this->assign('yf', $yf);
                $this->assign('yftop', self::$WAP['shopset']['yftop']);
            } else {
                $this->assign('isyf', 0);
                $this->assign('yf', 0);
            }
            //是否可以用余额支付
            $useryue = $_SESSION['WAP']['vip']['money'];
            $isyue = $_SESSION['WAP']['vip']['money'] - $totalprice >= 0 ? 0 : 1;
            $this->assign('isyue', $isyue);
            $this->assign('goods',$goods);
            $this->assign('cache', $cache);
            $this->assign('totalprice', $totalprice);
            $this->assign('totalnum', $totalnum);
            $this->display();
        }

    }

    //订单地址跳转
    public function orderAddress()
    {
        $sid = I('sid');
        $lasturlencode = I('lasturl');
        $backurl = U('App/Shop/orderMake', array('sid' => $sid, 'lasturl' => $lasturlencode));
        $_SESSION['WAP']['orderURL'] = $backurl;
        $this->redirect('App/Vip/address');
    }

    //订单列表
    public function orderList()
    {
        $sid = I('sid') <> '' ? I('sid') : $this->diemsg(0, '缺少SID参数');//sid可以为0
        $type = I('type') ? I('type') : 4;
        $this->assign('type', $type);
        $bkurl = U('App/Shop/orderList', array('sid' => $sid, 'type' => $type));
        $backurl = base64_encode($bkurl);
        $loginurl = U('App/Vip/login', array('backurl' => $backurl));
        $re = $this->checkLogin($backurl);
        //已登陆
        $m = M('Shop_order');
        $vipid = $_SESSION['WAP']['vipid'];
        $map['sid'] = $sid;
        $map['vipid'] = $vipid;
        switch ($type) {
            case '1':
                $map['status'] = 1;
                break;
            case '2':
                $map['status'] = array('in', array('2', '3'));
                break;
            case '3':
                $map['status'] = array('in', array('5', '6'));
                break;
            case '4':
                //全部
                $map['status'] = array('neq', '0');
                break;
            default:
                $map['status'] = 1;
                break;
        }
        $cache = $m->where($map)->order('ctime desc')->select();
        if ($cache) {
            foreach ($cache as $k => $v) {
                if ($v['items']) {
                    $cache[$k]['items'] = unserialize($v['items']);
                }
            }
        }
        $this->assign('cache', $cache);
        //高亮底导航
        $this->assign('actname', 'ftorder');
        $this->display();
    }
    public function orderList_wx(){
        $m = M('Shop_order');
        $oid = I('oid') <> '' ? I('oid') : $this->diemsg(0, '缺少OID参数');
        $openid=$_SESSION['sqopenid'];
        $vipid=$_SESSION['WAP']['vip']['id'];
        $wxid=M('shop_set')->getField('wxid');
        $wxid=explode(',', $wxid);
        // dump($wxid);
        $one=M('vip')->where(array('openid'=>$openid,'id'=>$vipid))->find();
        // dump($one);
        if($one&&in_array($vipid, $wxid)){
           $map=array(
            'ispay'=>1,
            'status'=>2,
            'oid'=>$oid
            );
            $cache=$m->where($map)->find();
            $cache['items'] = unserialize($cache['items']);
            if (IS_AJAX) {
               $id = I('oid');
                if (!$id) {
                    $info['status'] = 0;
                    $info['msg'] = '未正常获取ID数据！';
                    }
                    $re = M('Shop_order')->where('id=' . $id)->setField('status', 3);
                    $mlog = M('Shop_order_log');
                    $mslog = M('Shop_order_syslog');
                    $dwechat = D('Wechat');
                    if (FALSE !== $re) {
                        $log['oid'] = $id;
                        $log['msg'] = '订单已配送';
                        $log['ctime'] = time();
                        $rlog = $mlog->add($log);
                        //后端LOG
                        $log['type'] = 3;
                        $log['paytype'] = $cache['paytype'];
                        $rslog = $mslog->add($log);
                        // 插入订单发货模板消息=====================
                        $order = M('Shop_order')->where('id=' . $id)->find();
                        $vip = M('vip')->where(array('id' => $order['vipid']))->find();
                        $templateidshort = 'OPENTM201541214';
                        $templateid = $dwechat->getTemplateId($templateidshort);
                        if ($templateid) { // 存在才可以发送模板消息
                            $data = array();
                            $data['touser'] = $vip['openid'];
                            $data['template_id'] = $templateid;
                            $data['topcolor'] = "#0000FF";
                            $data['data'] = array(
                                'first' => array('value' => '您好，您的订单已配送'),
                                'keyword1' => array('value' => $order['oid']),
                                'keyword2' => array('value' => '配送员已确认配送'),
                                'keyword3' => array('value' => date("Y-m-d h:i:s",time())),
                                'keyword4' => array('value' => $order['vipaddress']),
                                'remark' => array('value' => '如有问题请直接微信留言，我们第一时间为你解决！')
                            );
                            // $options['appid'] = self::$SYS['set']['wxappid'];
                            // $options['appsecret'] = self::$SYS['set']['wxappsecret'];
                            $options['appid'] = self::$_wxappid;
                            $options['appsecret'] = self::$_wxappsecret;
                            $wx = new \Util\Wx\Wechat($options);
                            $rere = $wx->sendTemplateMessage($data);

                        }
                        // 插入订单发货模板消息结束=================
                        $info['status'] = 1;
                        $info['msg'] = '发货成功！';
                    } else {
                        $info['status'] = 0;
                        $info['msg'] = '操作失败！';
                    }
                die(json_encode($info));
            }
        $this->assign('cache', $cache);
        $this->display();  
        }else{
            $this->error('您没有权限进行订单管理!');
        }    
    }
    //订单详情
    //订单列表
    public function orderDetail()
    {
        $sid = I('sid') <> '' ? I('sid') : $this->diemsg(0, '缺少SID参数');//sid可以为0
        $orderid = I('orderid') <> '' ? I('orderid') : $this->diemsg(0, '缺少ORDERID参数');
        $bkurl = U('App/Shop/orderDetail', array('sid' => $sid, 'orderid' => $orderid));
        $backurl = base64_encode($bkurl);
        $loginurl = U('App/Vip/login', array('backurl' => $backurl));
        $re = $this->checkLogin($backurl);
        //已登陆
        $m = M('Shop_order');
        $vipid = $_SESSION['WAP']['vipid'];
        $map['sid'] = $sid;
        $map['id'] = $orderid;
        $cache = $m->where($map)->find();
        if (!$cache) {
            $this->diemsg('此订单不存在!');
        }

        $cache['items'] = unserialize($cache['items']);
        //order日志
        $mlog = M('Shop_order_log');
        $log = $mlog->where('oid=' . $cache['id'])->select();
        $this->assign('log', $log);
        if (!$cache['status'] == 1) {
            //是否可以用余额支付
            $useryue = $_SESSION['WAP']['vip']['money'];
            $isyue = $_SESSION['WAP']['vip']['money'] - $cache['payprice'] >= 0 ? 0 : 1;
            $this->assign('isyue', $isyue);
        }
        $this->assign('cache', $cache);
        //代金卷调用
        if ($cache['djqid']) {
            $djq = M('Vip_card')->where('id=' . $cache['djqid'])->find();
            $this->assign('djq', $djq);
        }
        //高亮底导航
        $this->assign('actname', 'ftorder');
        $this->display();
    }

    //订单取消
    public function orderCancel()
    {
        $sid = I('sid') <> '' ? I('sid') : $this->diemsg(0, '缺少SID参数');//sid可以为0
        $orderid = I('orderid') <> '' ? I('orderid') : $this->diemsg(0, '缺少ORDERID参数');
        $bkurl = U('App/Shop/orderDetail', array('sid' => $sid, 'orderid' => $orderid));
        $backurl = base64_encode($bkurl);
        $loginurl = U('App/Vip/login', array('backurl' => $backurl));
        $re = $this->checkLogin($backurl);
        //已登陆
        $m = M('Shop_order');
        $map['sid'] = $sid;
        $map['id'] = $orderid;
        $cache = $m->where($map)->find();
        if (!$cache) {
            $this->diemsg(0, '此订单不存在!');
        }
        if ($cache['status'] <> 1) {
            $this->error('只有未付款订单可以取消！');
        }
        $re = $m->where($map)->setField('status', 0);
        if ($re) {
            //订单取消只有后端日志
            $mslog = M('Shop_order_syslog');
            $dlog['oid'] = $cache['id'];
            $dlog['msg'] = '订单取消';
            $dlog['type'] = 0;
            $dlog['ctime'] = time();
            $rlog = $mslog->add($dlog);
            $this->success('订单取消成功！');
        } else {
            $this->error('订单取消失败,请重新尝试！');
        }
    }

    //确认收货
    public function orderOK()
    {
        $sid = I('sid') <> '' ? I('sid') : $this->diemsg(0, '缺少SID参数');//sid可以为0
        $orderid = I('orderid') <> '' ? I('orderid') : $this->diemsg(0, '缺少ORDERID参数');
        $bkurl = U('App/Shop/orderDetail', array('sid' => $sid, 'orderid' => $orderid));
        $backurl = base64_encode($bkurl);
        $loginurl = U('App/Vip/login', array('backurl' => $backurl));
        $re = $this->checkLogin($backurl);
        //已登陆
        $m = M('Shop_order');
        $map['sid'] = $sid;
        $map['id'] = $orderid;
        $cache = $m->where($map)->find();
        if (!$cache) {
            $this->diemsg(0, '此订单不存在!');
        }
        if ($cache['status'] <> 3) {
            $this->error('只有待收货订单可以确认收货！');
        }
        $cache['etime'] = time();//交易完成时间
        $cache['status'] = 5;
        $rod = $m->save($cache);
        if (FALSE !== $rod) {
            //修改会员账户金额、经验、积分、等级
            $data_vip['id'] = $cache['vipid'];
            $data_vip['score'] = array('exp', 'score+' . round($cache['payprice'] * self::$WAP['vipset']['cz_score'] / 100,0));
            if (self::$WAP['vipset']['cz_exp'] > 0) {
                $data_vip['exp'] = array('exp', 'exp+' . round($cache['payprice'] * self::$WAP['vipset']['cz_exp'] / 100,0));
                $data_vip['cur_exp'] = array('exp', 'cur_exp+' . round($cache['payprice'] * self::$WAP['vipset']['cz_exp'] / 100,0));
                $level = $this->getLevel(self::$WAP['vip']['cur_exp'] + round($cache['payprice'] * self::$WAP['vipset']['cz_exp'] / 100,0));
                $data_vip['levelid'] = $level['levelid'];
                //会员分销统计字段
                //会员购买一次变成分销商
                $data_vip['isfx'] = 1;
                //会员合计支付
                $data_vip['total_buy'] = $data_vip['total_buy'] + $cache['payprice'];
            }
            $re = M('vip')->save($data_vip);
            if (FALSE === $re) {
                $this->error('更新会员信息失败！');
            }

            //分销佣金计算
            $commission = D('Commission');
            $orderids = array();
            $orderids[] = $cache['id'];

            $pid = $_SESSION['WAP']['vip']['pid'];
            $mvip = M('vip');
            $mfxlog = M('fx_syslog');
            $fxlog['oid'] = $cache['id'];
            $fxlog['fxprice'] = $fxprice = $cache['payprice'] - $cache['yf'];
            $fxlog['ctime'] = time();
            // $fx1rate=self::$WAP['shopset']['fx1rate']/100;
            // $fx2rate=self::$WAP['shopset']['fx2rate']/100;
            // $fx3rate=self::$WAP['shopset']['fx3rate']/100;
            $fxtmp = array();//缓存3级数组
            if ($pid) {
                //第一层分销
                $fx1 = $mvip->where('id=' . $pid)->find();
                if ($fx1['isfx']) {
                    $fxlog['fxyj'] = $commission->ordersCommission('fx1rate', $orderids);
                    $fx1['money'] = $fx1['money'] + $fxlog['fxyj'];
                    $fx1['total_xxbuy'] = $fx1['total_xxbuy'] + 1;//下线中购买产品总次数
                    $fx1['total_xxyj'] = $fx1['total_xxyj'] + $fxlog['fxyj'];//下线贡献佣金
                    $rfx = $mvip->save($fx1);
                    $fxlog['from'] = $_SESSION['WAP']['vipid'];
                    $fxlog['fromname'] = $_SESSION['WAP']['vip']['nickname'];
                    $fxlog['to'] = $fx1['id'];
                    $fxlog['toname'] = $fx1['nickname'];
                    if (FALSE !== $rfx) {
                        //佣金发放成功
                        $fxlog['status'] = 1;
                    } else {
                        //佣金发放失败
                        $fxlog['status'] = 0;
                    }
                    //单层逻辑                  
                    //$rfxlog=$mfxlog->add($fxlog);
                    //file_put_contents('./Data/app_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
                    array_push($fxtmp, $fxlog);
                }
                //第二层分销
                if ($fx1['pid']) {
                    $fx2 = $mvip->where('id=' . $fx1['pid'])->find();
                    if ($fx2['isfx']) {
                        $fxlog['fxyj'] = $commission->ordersCommission('fx2rate', $orderids);
                        $fx2['money'] = $fx2['money'] + $fxlog['fxyj'];
                        $fx2['total_xxbuy'] = $fx2['total_xxbuy'] + 1;//下线中购买产品人数计数
                        $fx2['total_xxyj'] = $fx2['total_xxyj'] + $fxlog['fxyj'];//下线贡献佣金
                        $rfx = $mvip->save($fx2);
                        $fxlog['from'] = $_SESSION['WAP']['vipid'];
                        $fxlog['fromname'] = $_SESSION['WAP']['vip']['nickname'];
                        $fxlog['to'] = $fx2['id'];
                        $fxlog['toname'] = $fx2['nickname'];
                        if (FALSE !== $rfx) {
                            //佣金发放成功
                            $fxlog['status'] = 1;
                        } else {
                            //佣金发放失败
                            $fxlog['status'] = 0;
                        }
                        //单层逻辑
                        //$rfxlog=$mfxlog->add($fxlog);
                        //file_put_contents('./Data/app_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
                        array_push($fxtmp, $fxlog);
                    }
                }
                //第三层分销
                if ($fx2['pid']) {
                    $fx3 = $mvip->where('id=' . $fx2['pid'])->find();
                    if ($fx3['isfx']) {
                        $fxlog['fxyj'] = $commission->ordersCommission('fx3rate', $orderids);
                        $fx3['money'] = $fx3['money'] + $fxlog['fxyj'];
                        $fx3['total_xxbuy'] = $fx3['total_xxbuy'] + 1;//下线中购买产品人数计数
                        $fx3['total_xxyj'] = $fx3['total_xxyj'] + $fxlog['fxyj'];//下线贡献佣金
                        $rfx = $mvip->save($fx3);
                        $fxlog['from'] = $_SESSION['WAP']['vipid'];
                        $fxlog['fromname'] = $_SESSION['WAP']['vip']['nickname'];
                        $fxlog['to'] = $fx3['id'];
                        $fxlog['toname'] = $fx3['nickname'];
                        if (FALSE !== $rfx) {
                            //佣金发放成功
                            $fxlog['status'] = 1;
                        } else {
                            //佣金发放失败
                            $fxlog['status'] = 0;
                        }
                        //单层逻辑
                        //$rfxlog=$mfxlog->add($fxlog);
                        //file_put_contents('./Data/app_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
                        array_push($fxtmp, $fxlog);
                    }
                }
                //多层分销
                if (count($fxtmp) >= 1) {
                    $refxlog = $mfxlog->addAll($fxtmp);
                    if (!$refxlog) {
                        file_put_contents('./Data/app_fx_error.txt', '错误日志时间:' . date('Y-m-d H:i:s') . PHP_EOL . '错误纪录信息:' . $rfxlog . PHP_EOL . PHP_EOL . $mfxlog->getLastSql() . PHP_EOL . PHP_EOL, FILE_APPEND);
                    }
                }
                //花鼓分销方案
                $allhg = $mvip->field('id')->where('isfxgd=1')->select();
                if ($allhg) {
                    $tmppath = array_slice(explode('-', $_SESSION['WAP']['vip']['path']), -20);
                    $tmphg = array();
                    foreach ($allhg as $v) {
                        array_push($tmphg, $v['id']);
                    }
                    //需要计算的花鼓
                    $needhg = array_intersect($tmphg, $tmppath);
                    if (count($needhg)) {
                        $fxlog['oid'] = $cache['id'];
                        $fxlog['fxprice'] = $fxprice;
                        $fxlog['ctime'] = time();
                        $fxlog['fxyj'] = $fxprice * 0.05;
                        $fxlog['from'] = $_SESSION['WAP']['vipid'];
                        $fxlog['fromname'] = $_SESSION['WAP']['vip']['nickname'];
                        foreach ($needhg as $k => $v) {
                            $hg = $mvip->where('id=' . $v)->find();
                            if ($hg) {
                                $rhg = $mvip->where('id=' . $v)->setInc('money', $fxlog['fxyj']);
                                if ($rhg) {
                                    $fxlog['to'] = $hg['id'];
                                    $fxlog['toname'] = $hg['nickname'] . '[花股收益]';
                                    $rehgfxlog = $mfxlog->add($fxlog);
                                }
                            }
                        }
                    }
                }

            }

            $mlog = M('Shop_order_log');
            $dlog['oid'] = $cache['id'];
            $dlog['msg'] = '确认收货,交易完成。';
            $dlog['ctime'] = time();
            $rlog = $mlog->add($dlog);
            //后端日志
            $mlog = M('Shop_order_syslog');
            $dlog['oid'] = $cache['id'];
            $dlog['msg'] = '交易完成-会员点击';
            $dlog['type'] = 5;
            $dlog['paytype'] = $cache['paytype'];
            $dlog['ctime'] = time();
            $rlog = $mlog->add($dlog);
            $this->success('交易已完成，感谢您的支持！');
        } else {
            //后端日志
            $mlog = M('Shop_order_syslog');
            $dlog['oid'] = $cache['id'];
            $dlog['msg'] = '确认收货失败';
            $dlog['type'] = -1;
            $dlog['paytype'] = $cache['paytype'];
            $dlog['ctime'] = time();
            $rlog = $mlog->add($dlog);
            $this->error('确认收货失败，请重新尝试！');
        }
    }

    //订单退货
    public function orderTuihuo()
    {
        $sid = I('sid') <> '' ? I('sid') : $this->diemsg(0, '缺少SID参数');//sid可以为0
        $orderid = I('orderid') <> '' ? I('orderid') : $this->diemsg(0, '缺少ORDERID参数');
        $bkurl = U('App/Shop/orderTuihuo', array('sid' => $sid, 'orderid' => $orderid));
        $backurl = base64_encode($bkurl);
        $loginurl = U('App/Vip/login', array('backurl' => $backurl));
        $re = $this->checkLogin($backurl);
        //已登陆
        $m = M('Shop_order');
        $vipid = $_SESSION['WAP']['vipid'];
        $map['sid'] = $sid;
        $map['id'] = $orderid;
        $cache = $m->where($map)->find();
        if (!$cache) {
            $this->diemsg('此订单不存在!');
        }
        //退货数量
        
        $cache['items'] = unserialize($cache['items']);
        $this->assign('cache', $cache);
        //代金卷调用
        if ($cache['djqid']) {
            $djq = M('Vip_card')->where('id=' . $cache['djqid'])->find();
            $this->assign('djq', $djq);
        }
        //高亮底导航
        $this->assign('actname', 'ftorder');
        $this->display();
    }

    //订单取消
    public function orderTuihuoSave()
    {
        $sid = I('sid') <> '' ? I('sid') : $this->diemsg(0, '缺少SID参数');//sid可以为0
        $orderid = I('orderid') <> '' ? I('orderid') : $this->diemsg(0, '缺少ORDERID参数');
        $bkurl = U('App/Shop/orderTuihuo', array('sid' => $sid, 'orderid' => $orderid));
        $backurl = base64_encode($bkurl);
        $loginurl = U('App/Vip/login', array('backurl' => $backurl));
        $re = $this->checkLogin($backurl);
        //已登陆
        $m = M('Shop_order');
        $map['sid'] = $sid;
        $map['id'] = $orderid;
        $cache = $m->where($map)->find();
        if (!$cache) {
            $this->diemsg(0, '此订单不存在!');
        }
        if ($cache['status'] == 3 || $cache['status'] == 2) {
            
            $data = I('post.');
            $cache['status'] = 4;
            $cache['tuihuoprice'] = $cache['payprice'];
            $cache['tuihuokd'] = '-';
            $cache['tuihuokdnum'] = '-';
            $cache['tuihuomsg'] = $data['tuihuomsg'];
            //退货申请时间
            $cache['tuihuosqtime'] = time();
            $re = $m->where($map)->save($cache);
            if ($re) {
                //后端日志
                $mlog = M('Shop_order_log');
                $mslog = M('Shop_order_syslog');
                $dlog['oid'] = $cache['id'];
                $dlog['msg'] = '申请退货';
                $dlog['ctime'] = time();
                $rlog = $mlog->add($dlog);
                $dlog['type'] = 4;
                $rslog = $mslog->add($dlog);
                $this->success('申请退货成功！请等待工作人员审核！');
            } else {
                $this->error('申请退货失败,请重新尝试！');
            }
        
        }else{
            $this->error('只有待收货订单可以办理退货！');
        }
    }

    //订单支付
    public function pay()
    {
        $sid = I('sid') <> '' ? I('sid') : $this->diemsg(0, '缺少SID参数');//sid可以为0
        $orderid = I('orderid') <> '' ? I('orderid') : $this->diemsg(0, '缺少ORDERID参数');
        $type = I('type');
        $bkurl = U('App/Shop/pay', array('sid' => $sid, 'orderid' => $orderid, 'type' => $type));
//      $backurl=base64_encode($orderdetail);
        $backurl = base64_encode($bkurl);
        $loginurl = U('App/Vip/login', array('backurl' => $backurl));
        $re = $this->checkLogin($backurl);
        //已登陆
        $m = M('Shop_order');
        $order = $m->where('id=' . $orderid)->find();
        if (!$order) {
            $this->error('此订单不存在！');
        }
        if ($order['status'] <> 1) {
            $this->error('此订单不可以支付！');
        }
        //检查库存
        $items=unserialize($order['itmes']);
        foreach ($itmes as $key => $value) {
            $goodsnum=M('shop_goods')->where(array('id'=>$value['goodsid']))->getField('num');
            if ($goodsnum<$value['num']) {
                //删掉订单
                $this->error('商品库存不足,请去购物车调整购买数量');
            }
        }
        $paytype = I('type') ? I('type') : $order['paytype'];
        switch ($paytype) {
            case 'money':
                $mvip = M('Vip');
                $vip = $mvip->where('id=' . $_SESSION['WAP']['vipid'])->find();
                $pp = $vip['money'] - $order['payprice'];
                if ($pp >= 0) {
                    $re = $mvip->where('id=' . $_SESSION['WAP']['vipid'])->setField('money', $pp);
                    if ($re) {
                        $order['paytype'] = 'money';
                        $order['ispay'] = 1;
                        $order['paytime'] = time();
                        $order['status'] = 2;
                        $rod = $m->save($order);
                        //查询是否参与限购
                        $limitbuy=unserialize($order['items']);
                        foreach ($limitbuy as $key => $value) {
                            if ($value['islimitsell']==1) {
                                M('Shop_limitbuy')->where(array('vipid'=>$_SESSION['WAP']['vipid'],'goodsid'=>$value['goodsid']))->setField('status',2);
                            }
                        }
                        //限购结束
                        if (FALSE !== $rod) {
                            //销量计算-只减不增
                            $rsell = $this->doSells($order);
                            //前端日志
                            $mlog = M('Shop_order_log');
                            $dlog['oid'] = $order['id'];
                            $dlog['msg'] = '余额-付款成功';
                            $dlog['ctime'] = time();
                            $rlog = $mlog->add($dlog);
                            //后端日志
                            $mlog = M('Shop_order_syslog');
                            $dlog['type'] = 2;
                            $rlog = $mlog->add($dlog);

                            // 插入订单支付成功模板消息=====================
                            $templateidshort = 'OPENTM200444326';
                            $dwechat = D('Wechat');
                            $templateid = $dwechat->getTemplateId($templateidshort);
                            if ($templateid) { // 存在才可以发送模板消息
                                $data = array();
                                $data['touser'] = $vip['openid'];
                                $data['template_id'] = $templateid;
                                $data['topcolor'] = "#00FF00";
                                $data['data'] = array(
                                    'first' => array('value' => '您好，您的订单已付款成功'),
                                    'keyword1' => array('value' => $order['oid']),
                                    'keyword2' => array('value' => date("Y-m-d h:i:sa", $order['paytime'])),
                                    'keyword3' => array('value' => $order['payprice']),
                                    'keyword4' => array('value' => $order['paytype']),
                                    'remark' => array('value' => '')
                                );
                                $options['appid'] = self::$_wxappid;
                                $options['appsecret'] = self::$_wxappsecret;
                                $wx = new \Util\Wx\Wechat($options);
                                $re = $wx->sendTemplateMessage($data);//发送微信消息
                            }
                            $this->sendMobanMsmToShop($order['id'],1);
                            // 插入订单支付成功模板消息结束=================
                            
                            //首次支付成功自动变为花蜜
                            // if ($vip && !$vip['isfx']) {
                            //     $rvip = $mvip->where('id=' . $_SESSION['WAP']['vipid'])->setField('isfx', 1);
                            //     $data_msg['pids'] = $_SESSION['WAP']['vipid'];

                            //     $shopset = self::$WAP['shopset'] = $_SESSION['WAP']['shopset'];
                            //     $data_msg['title'] = "您成功升级为" . $shopset['name'] . "的" . $shopset['fxname'] . "！";
                            //     $data_msg['content'] = "欢迎成为" . $shopset['name'] . "的" . $shopset['fxname'] . "，开启一个新的旅程！";
                            //     $data_msg['ctime'] = time();
                            //     $rmsg = M('vip_message')->add($data_msg);
                            // }

                            //代收花生米计算-只减不增
                            $rds = $this->doDs($order);
                            //查询库存
                            $items=unserialize($order['items']);
                            
                            foreach ($items as $key => $value) {
                                $goods=M('Shop_goods')->where('id='.$value['goodsid'])->find();
                                $msg=array();
                                //库存小于10则发送模板通知
                                if ($goods['num']<=10) {
                                    $templateidshort = 'OPENTM205213466';
                                    $dwechat = D('Wechat');
                                    $templateid =  $dwechat->getTemplateId($templateidshort);
                                    if ($templateid) { // 存在才可以发送模板消息
                                        $msg = array();
                                        $msg['topcolor'] = "#00FF00";
                                        // $msg['url'] = "http://www.gxeschool.com/App/Shop/goods/id/".$value['goodsid'];
                                        $msg['template_id'] = $templateid;
                                        $msg['data'] = array(
                                            'first' => array('value' => '商品库存不足'),
                                            'keyword1' => array('value' => $value['goodsid']),
                                            'keyword2' => array('value' => $goods['name']),
                                            'keyword3' => array('value' => $goods['num']),
                                            'remark' => array('value' => '请及时补充库存')
                                        );
                                        $options['appid'] = self::$_wxappid;
                                        $options['appsecret'] = self::$_wxappsecret;
                                        $wx = new \Util\Wx\Wechat($options);
                                        $wxid=M('shop_set')->getField('wxid');
                                        $wxid=explode(',', $wxid);            
                                        foreach ($wxid as $k => $val) {
                                            $openid = M('vip')->where('id=' . $val)->getField('openid');
                                            $msg['touser'] =$openid;
                                            $re = $wx->sendTemplateMessage($msg);
                                        }
                                    }
                                }
                            }
                            //查询库存结束
                            //打印小票
                            $this->sendPrintPP($order);
                        } else {
                            //后端日志
                            $mlog = M('Shop_order_syslog');
                            $dlog['oid'] = $order['id'];
                            $dlog['msg'] = '余额付款失败';
                            $dlog['type'] = -1;
                            $dlog['ctime'] = time();
                            $rlog = $mlog->add($dlog);
                            $this->error('余额付款失败！请联系客服！');
                        }

                    } else {
                        //后端日志
                        $mlog = M('Shop_order_syslog');
                        $dlog['oid'] = $order['id'];
                        $dlog['msg'] = '余额付款失败';
                        $dlog['type'] = -1;
                        $dlog['ctime'] = time();
                        $this->error('余额支付失败，请重新尝试！');
                    }
                } else {
                    $this->error('余额不足，请使用其它方式付款！');
                }
                break;
            case 'alipayApp':
                $this->redirect("App/Alipay/alipay", array('sid' => $sid, 'price' => $order['payprice'], 'oid' => $order['oid']));
                break;
            case 'wxpay':
                $_SESSION['wxpaysid'] = 0;
                $_SESSION['wxpayopenid'] = $_SESSION['WAP']['vip']['openid'];//追入会员openid
                $this->redirect('Home/Wxpay/pay', array('oid' => $order['oid']));
                break;
            default:
                $this->error('支付方式未知！');
                break;
        }

    }
    //打印机
    function sendPrintPP($order){
        $items = unserialize($order['items']);
        $msg .= '<FB>###############################</FB>\r\n\r\n';
        $msg .= '<center>Eschool校园服务平台</center>\r\n';
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
        $msg .= '<center>微信扫二维码 校园购物更便利</center>';
        printPP($msg);
    }
    //发送商家模板消息
    //type=0:订单新生成（未付款）
    //type=1:订单已付款
    function sendMobanMsmToShop($oid, $type, $flag = FALSE)
    {
        //构造消息体
        $order = M('shop_order')->where('id=' . $oid)->find();
        // $shop = M('Shop')->where('id=' . $order['sid'])->find();
        
        $ppid = $order['vipid'];
        
            if ($order['vipmobile'] == '') {
                $addressinfo = M('vip_address')->where('vipid=' . $ppid)->order('id asc')->select();
                if ($addressinfo) {
                    $vipname = $addressinfo[0]['name'];
                    $customerInfo =   $addressinfo[0]['mobile'];
                } else {
                    $vipname = '暂未登记';
                    $customerInfo = '暂未登记';
                }
            } else {
                $vipname = $order['vipname'];
                $customerInfo =  $order['vipmobile'] . ' ' .$order['vipaddress'];
            }
            $first = '新订单通知（已付款）';

            $arr = explode('|', $order['goods']);
            $money = 0;
            $remark = "\\n";
            foreach ($arr as $k => $val) {
                $a = explode(',', $val);
                $money = $money + $a['5'] * $a['3'];
                $remark = $remark . $a['1'] . "：" . $a['3'] . $a['4'] . "\\n";
            }
            $data = array();
            $data['template_id'] = "DCy6SJ8xHheN9hN0hSqwUQlvqC9rRj5Tqf_JI9Zsk6A";
            $data['topcolor'] = "#00FF00";
            $data['url'] = "http://www.gxeschool.com/App/Shop/orderList_wx/oid/".$oid;
            $data['data'] = array(
                'first' => array('value' => $first),
                'keyword1' => array('value' => $order['oid']),
                'keyword2' => array('value' => '余额支付'),
                'keyword3' => array('value' => $order['payprice']  ),
                'keyword4' => array('value' => date("Y-m-d h:i:s", $order['paytime'])),
                'keyword5' => array('value' => $vipname),
                'remark' => array('value' => $customerInfo)
            );
            //发送消息
            $options['appid'] = self::$_wxappid;
            $options['appsecret'] = self::$_wxappsecret;
            $mx = new \Util\Wx\Wechat($options);
            $wxid=M('shop_set')->getField('wxid');
            $wxid=explode(',', $wxid);            
            foreach ($wxid as $k => $val) {
                $openid = M('vip')->where('id=' . $val)->getField('openid');
                $data['touser'] = $openid;
                $rtn = $mx->sendTemplateMessage($data);
                file_put_contents('./logs/message/msg.txt', PHP_EOL . '发送商家模板消息成功:' . date('Y-m-d H:i:s') . $rtn, FILE_APPEND);
            }
    }
    //销量计算
    private function doSells($order)
    {
        $mgoods = M('Shop_goods');
        $msku = M('Shop_goods_sku');
        $mlogsell = M('Shop_syslog_sells');
        //封装dlog
        $dlog['oid'] = $order['id'];
        $dlog['vipid'] = $order['vipid'];
        $dlog['vipopenid'] = $order['vipopenid'];
        $dlog['vipname'] = $order['vipname'];
        $dlog['ctime'] = time();
        $items = unserialize($order['items']);
        $tmplog = array();
        foreach ($items as $k => $v) {
            //销售总量
            $dnum = $dlog['num'] = $v['num'];
            if ($v['skuid']) {
                $rg = $mgoods->where('id=' . $v['goodsid'])->setDec('num', $dnum);
                $rg = $mgoods->where('id=' . $v['goodsid'])->setInc('sells', $dnum);
                $rg = $mgoods->where('id=' . $v['goodsid'])->setInc('dissells', $dnum);
                $rs = $msku->where('id=' . $v['skuid'])->setDec('num', $dnum);
                $rs = $msku->where('id=' . $v['skuid'])->setInc('sells', $dnum);
                //sku模式
                $dlog['goodsid'] = $v['goodsid'];
                $dlog['goodsname'] = $v['name'];
                $dlog['skuid'] = $v['skuid'];
                $dlog['skuattr'] = $v['skuattr'];
                $dlog['price'] = $v['price'];
                $dlog['num'] = $v['num'];
                $dlog['total'] = $v['total'];
            } else {
                $rg = $mgoods->where('id=' . $v['goodsid'])->setDec('num', $dnum);
                $rg = $mgoods->where('id=' . $v['goodsid'])->setInc('sells', $dnum);
                $rg = $mgoods->where('id=' . $v['goodsid'])->setInc('dissells', $dnum);
                //纯goods模式
                $dlog['goodsid'] = $v['goodsid'];
                $dlog['goodsname'] = $v['name'];
                $dlog['skuid'] = 0;
                $dlog['skuattr'] = 0;
                $dlog['price'] = $v['price'];
                $dlog['num'] = $v['num'];
                $dlog['total'] = $v['total'];
            }
            array_push($tmplog, $dlog);
        }
        if (count($tmplog)) {
            $rlog = $mlogsell->addAll($tmplog);
        }
        return true;
    }

    //代收花生米计算
    public function doDs($order)
    {
        //分销佣金计算
        $commission = D('Commission');
        $orderids = array();
        $orderids[] = $order['id'];

        $vipid = $order['vipid'];
        $mvip = M('vip');
        $vip = $mvip->where('id=' . $vipid)->find();
        if (!$vip && !$vip['pid']) {
            return FALSE;
        }
        //初始化 
        $pid = $vip['pid'];
        $mfxlog = M('fx_dslog');
        $shopset = M('Shop_set')->find();//追入商城设置
        $fxlog['oid'] = $order['id'];
        $fxlog['fxprice'] = $fxprice = $order['payprice'] - $order['yf'];
        $fxlog['ctime'] = time();
        // $fx1rate=$shopset['fx1rate']/100;
        // $fx2rate=$shopset['fx2rate']/100;
        // $fx3rate=$shopset['fx3rate']/100;
        $fxtmp = array();//缓存3级数组
        if ($pid) {
            //第一层分销
            $fx1 = $mvip->where('id=' . $pid)->find();
            if ($fx1['isfx']) {
                $fxlog['fxyj'] = $commission->ordersCommission('fx1rate', $orderids);
                $fxlog['from'] = $vip['id'];
                $fxlog['fromname'] = $vip['nickname'];
                $fxlog['to'] = $fx1['id'];
                $fxlog['toname'] = $fx1['nickname'];
                $fxlog['status'] = 1;
                //单层逻辑                  
                //$rfxlog=$mfxlog->add($fxlog);
                //file_put_contents('./Data/app_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
                array_push($fxtmp, $fxlog);
            }
            //第二层分销
            if ($fx1['pid']) {
                $fx2 = $mvip->where('id=' . $fx1['pid'])->find();
                if ($fx2['isfx']) {
                    $fxlog['fxyj'] = $commission->ordersCommission('fx2rate', $orderids);
                    $fxlog['from'] = $vip['id'];
                    $fxlog['fromname'] = $vip['nickname'];
                    $fxlog['to'] = $fx2['id'];
                    $fxlog['toname'] = $fx2['nickname'];
                    $fxlog['status'] = 1;
                    //单层逻辑
                    //$rfxlog=$mfxlog->add($fxlog);
                    //file_put_contents('./Data/app_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
                    array_push($fxtmp, $fxlog);
                }
            }
            //第三层分销
            if ($fx2['pid']) {
                $fx3 = $mvip->where('id=' . $fx2['pid'])->find();
                if ($fx3['isfx']) {
                    $fxlog['fxyj'] = $commission->ordersCommission('fx3rate', $orderids);
                    $fxlog['from'] = $vip['id'];
                    $fxlog['fromname'] = $vip['nickname'];
                    $fxlog['to'] = $fx3['id'];
                    $fxlog['toname'] = $fx3['nickname'];
                    $fxlog['status'] = 1;
                    //单层逻辑
                    //$rfxlog=$mfxlog->add($fxlog);
                    //file_put_contents('./Data/app_debug.txt','日志时间:'.date('Y-m-d H:i:s').PHP_EOL.'纪录信息:'.$rfxlog.PHP_EOL.PHP_EOL.$mfxlog->getLastSql().PHP_EOL.PHP_EOL,FILE_APPEND);
                    array_push($fxtmp, $fxlog);
                }
            }
            //多层分销
            if (count($fxtmp) >= 1) {
                $refxlog = $mfxlog->addAll($fxtmp);
                if (!$refxlog) {
                    file_put_contents('./Data/app_fx_error.txt', '错误日志时间:' . date('Y-m-d H:i:s') . PHP_EOL . '错误纪录信息:' . $rfxlog . PHP_EOL . PHP_EOL . $mfxlog->getLastSql() . PHP_EOL . PHP_EOL, FILE_APPEND);
                }
            }
            //花鼓分销方案
            $allhg = $mvip->field('id')->where('isfxgd=1')->select();
            if ($allhg) {
                $tmppath = array_slice(explode('-', $vip['path']), -20);
                $tmphg = array();
                foreach ($allhg as $v) {
                    array_push($tmphg, $v['id']);
                }
                //需要计算的花鼓
                $needhg = array_intersect($tmphg, $tmppath);
                if (count($needhg)) {
                    $fxlog['oid'] = $order['id'];
                    $fxlog['fxprice'] = $fxprice;
                    $fxlog['ctime'] = time();
                    $fxlog['fxyj'] = $fxprice * 0.05;
                    $fxlog['from'] = $vip['vipid'];
                    $fxlog['fromname'] = $vip['nickname'];
                    foreach ($needhg as $k => $v) {
                        $hg = $mvip->where('id=' . $v)->find();
                        if ($hg) {
                            $fxlog['to'] = $hg['id'];
                            $fxlog['toname'] = $hg['nickname'] . '[花股收益]';
                            $fxlog['ishg'] = 1;
                            $rehgfxlog = $mfxlog->add($fxlog);
                        }
                    }
                }
            }

        }
        return true;
        //逻辑完成
    }

    public function limitbuy($vipid,$goodsid,$num){
        if (IS_AJAX) {
            $vipid=I('vipid');
            $goodsid=I('goodsid');
            $num=I('num'); 
        }
        $count=0;
        $limitnum=M('Shop_goods')->where(array('id'=>$goodsid,'islimitsell'=>1))->getField('limitsell');
        $limitnum=intval($limitnum);
        if ($limitnum>0) {
            $order=M('Shop_limitbuy')->where(array('vipid'=>$vipid))->select();
            if ($count>=$limitnum) {
                $msg=array(
                    'res'=>'大于购买次数',
                    'num'=>0,
                    'status'=>2
                    );
            }elseif ($count<$limitnum && ($limitnum-$count)<$num) {
                $msg=array(
                    'res'=>'超过购买次数',
                    'num'=>0,
                    'status'=>2
                    );
            }else{
                $msg=array(
                    'res'=>'允许购买',
                    'num'=>$limitnum-$count,
                    'status'=>1
                    );
            }
        }else{
            $msg=array(
                'res'=>'不存在该限购商品',
                'num'=>999,
                'status'=>2
                );
        }
        die(json_encode($msg));
    }

    private function mySort($value1,$value2){
        if ($value1['sells']==$value2['sells']) {
            return 0;
        }else{
            return ($value1['sells']>$value2['sells'])?1:-1;
        }
    }
}

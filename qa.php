<?php

$con = mysql_connect("localhost","root","thunlp4506");
if (!$con)
{
    die('Could not connect: ' . mysql_error());
}
mysql_select_db("isgs", $con);
mysql_query("set character set 'utf8'"); 

function number($str)
{
    return preg_replace('/\D/s', '', $str);
}

header("Content-type:text/html;charset=utf-8");
session_start();

$state = 0;
$product = 0;
$brand = '';
$screen = '';
$system = '';
$category = '';
$price = 4000;
$skip = 0;

$ask = $_POST['data'];
//$ask = "再来一个";

$json_nlu = array("input"=>$ask);
$data_string = json_encode($json_nlu);
$ch = curl_init('http://115.182.62.171:9000/api/nlu/KogUedlW2mdzXiRyt3JC14EDBQvS12zR');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data_string))                                                           
);

$result = curl_exec($ch);
$dict = json_decode($result, true);

if(isset($_SESSION['state'])){
    $state = $_SESSION['state'];
}
if(isset($_SESSION['brand'])){
    $brand = $_SESSION['brand'];
}
if(isset($_SESSION['screen'])){
    $screen = $_SESSION['screen'];
}
if(isset($_SESSION['size'])){
    $size = $_SESSION['size'];
}
if(isset($_SESSION['system'])){
    $system = $_SESSION['system'];
}
if(isset($_SESSION['price'])){
    $price = $_SESSION['price'];
}
if(isset($_SESSION['category'])){
    $category = $_SESSION['category'];
}
if(isset($_SESSION['skip'])){
    $skip = $_SESSION['skip'];
}

/* isgs state
1.  init
2.  recommend
3.  fresh
4.  price
5.  pay
6.  confirm
7.  delete
8.  delete
9.  thx
10. bye 
*/

foreach ($dict[msg][patternlist] as $item){
    if($max_level < $item[_level]){ 
        $max_level = $item[_level];
        $max_act_type = $item[_act_type];
        $max = $item;
    }
}

// print_r($max);
// print_r($_SESSION);

if($max_act_type == 'recommend'){ // 推荐
    if(array_key_exists("brand",$max)){
        $brand = $max[brand];
        $_SESSION['brand'] = $max[brand];
        $_SESSION['system'] = '';
        $system = $max[system];
    }elseif(array_key_exists("system",$max)){
        $system = $max[system];
        $_SESSION['system'] = $max[system];
        $_SESSION['brand'] = '';
        $brand = $max[brand];
    }elseif(array_key_exists("price",$max)){
        $price = $max[price];
        $_SESSION['price'] = $max[price];
    }elseif(array_key_exists("screen",$max)){
        $screen = $max[screen];
        $_SESSION['screen'] = $max[screen];
    }
    $skip = rand(0,3);

    $sql = "SELECT * FROM Product WHERE bname LIKE '%{$brand}%' AND system LIKE '%{$system}%' ORDER BY (total-bad) DESC LIMIT {$skip} , 1";

    $result = mysql_query($sql);
    $row = mysql_fetch_array($result);
    
    $_SESSION['skip']  = $skip;

    echo "为您推荐".$row[name]."手机";

    $_SESSION['state'] = 2; // 有意向手机
    $_SESSION['name'] = $row[name];
    $_SESSION['product'] = $row[id];
    if(strlen($row[brand]) > 2){
        $_SESSION['brand'] = $row[bname];
    }
    if(strlen($row[system]) > 2){
        $_SESSION['system'] = $row[system];
    }
    $_SESSION['price'] = $row[price];
    $_SESSION['bad'] = $row[bad];
    $_SESSION['total'] = $row[total];

}elseif($max_act_type == 'whether'){ // 确认

    if(array_key_exists("brand",$max)){
        if(!strpos($max[brand], $_SESSION['brand']))
            echo "这款手机的品牌是".$max[brand];
        else
            echo "很抱歉，这款手机的品牌是".$_SESSION['brand']."，是否需要为您推荐".$max[brand]."品牌的手机";
    }elseif(array_key_exists("system",$max)){
        if(!strpos($max[system], $_SESSION['system']) and strlen($_SESSION['system']) > 1)
            echo "这款手机的系统是".$max[system];
        else
            echo "很抱歉，这款手机的系统是".$_SESSION['system']."，是否需要为您推荐".$max[system]."系统的手机";
    }elseif(array_key_exists("price",$max)){
        if($_SESSION['brand'] == $max[price])
            echo "这款手机的价格是".$max[price];
        else
            echo "很抱歉，这款手机的价格是".$_SESSION['price']."，是否需要为您推荐".$max[price]."价格左右的手机";
    }elseif(array_key_exists("screen",$max)){
        if($_SESSION['screen'] == $max[screen])
            echo "这款手机的尺寸是".$max[screen];
        else
            echo "很抱歉，这款手机的尺寸是".$_SESSION['screen']."，是否需要为您推荐".$max[screen]."尺寸的手机";
    }

}elseif($max_act_type == 'recommend:refresh=true'){ // 重新推荐
    $skip = rand(0,3);
    $_SESSION['skip'] = $skip;
    $skip = $_SESSION['skip'];
    $brand = $_SESSION['brand'];
    $system = $_SESSION['system'];

    $sql = "SELECT * FROM Product WHERE bname LIKE '%{$brand}%' AND system LIKE '%{$system}%' ORDER BY (total-bad) DESC LIMIT {$skip} , 1";

    echo $sql;
    $result = mysql_query($sql);
    $row = mysql_fetch_array($result);
    
    $_SESSION['skip']  = 0;
    echo "重新为您推荐".$row[name]."手机,\n价格:".$row[price]."\n操作系统:".$row[system];

}elseif($max_act_type == 'recommend:PriceSame'){ // 重新推荐
    $skip = 0;
    $_SESSION['skip'] = $skip;
    $skip = $_SESSION['skip'];
    $brand = $_SESSION['brand'];
    $system = $_SESSION['system'];
    $mid = number($ask);

    $sql = "SELECT * FROM Product WHERE bname LIKE '%{$brand}%' AND system LIKE '%{$system}%' AND (price-$mid) >= 0 ORDER BY (price-$mid) ASC LIMIT {$skip} , 1";

    $result = mysql_query($sql);
    $row = mysql_fetch_array($result);
    
    $_SESSION['skip']  = 0;
    echo "重新为您推荐".$row[name]."手机,\n价格:".$row[price]."\n操作系统:".$row[system];

}elseif($max_act_type == 'request:ask=commit'){ // 询价
    echo $_SESSION['name']."共有".$_SESSION['total']."条评价，其中差评".$_SESSION['bad']."条。";
    if($_SESSION['bad'] / $_SESSION['total'] < 0.05){
        echo "您可以放心购买";
    }else{
        echo "请谨慎购买";
    }

}elseif($max_act_type == 'request:ask=price'){ // 询价
    if(!isset($_SESSION['product']))
        echo "您还没有选择手机，先选择一款手机吧";
    else
        echo "这款手机的价格是".$_SESSION[price]; 

}elseif($max_act_type == 'request:ask=brand'){ // 品牌
    if(!isset($_SESSION['product']))
        echo "您还没有选择手机，先选择一款手机吧";
    else
        echo "这款手机的品牌是".$_SESSION[brand];

}elseif($max_act_type == 'request:ask=screen'){ // 尺寸
    echo "这款手机的尺寸是".$_SESSION[screen];

}elseif($max_act_type == 'request:ask=size'){ // 内存
    echo "这款手机的内存是";

}elseif($max_act_type == 'request:ask=parameter'){ // 型号
    if(!isset($_SESSION['product']))
        echo "您还没有选择手机，先选择一款手机吧";
    else
        echo "这款手机的型号是".$_SESSION[xing];

}elseif($max_act_type == 'request:ask=system'){ // 系统
    if(!isset($_SESSION['product']))
        echo "您还没有选择手机，先选择一款手机吧";
    else
        echo "这款手机的系统是".$_SESSION[system];

}elseif($max_act_type == 'recommend:lessThanPrice=30'){ // 价格区间
    echo "最近本店促销活动，跟您打个9折吧，折后价格".$_SESSION['price']*0.9;

}elseif($max_act_type == 'order:pay'){ // 支付
    echo '已经支付';

}elseif($max_act_type == 'order:confirm=true'){ // 确认支付
    echo '已经为您确认订单';

}elseif($max_act_type == 'delete'){ // 删除订单
    echo '已经为您删除订单';
    $_SESSION = array();

}elseif($max_act_type == 'gift'){ // 礼物
    echo '本店将为你赠送原装钢化膜';

}elseif($max_act_type == 'thx'){ // 感谢
    echo '不客气';

}elseif($max_act_type == 'free'){ // 感谢
    echo '本店包邮，会尽快为您寄出';

}elseif($max_act_type == 'bye'){ // 再见
    echo '再见，欢迎下次光临!';

}else{
    echo "我不太懂你在说什么，可以换种说法吗";
}


// $data = array("_act_type" => "hi");                                                                
// $data_string = json_encode($data);
// $ch = curl_init('http://115.182.62.171:9000/api/nlg/KogUedlW2mdzXiRyt3JC14EDBQvS12zR');
// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
// curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//     'Content-Type: application/json',
//     'Content-Length: ' . strlen($data_string))                                                           
// );                                                                                          
// $result = curl_exec($ch);
// echo ($result);
?>
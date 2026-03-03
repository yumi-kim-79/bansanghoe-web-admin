<?php
require_once "./_common.php";

$sql_buildigs = "";

if($building_id != ""){
    $sql_buildigs = " and (building_id = '{$building_id}' or building_id = '-1') ";
}

$default_data = $building_dates > $ho_tenant_at_de ? $building_dates : $ho_tenant_at_de;

//print_r($_POST);
if($code == 'all'){
    $sql = "SELECT * FROM a_building_bbs WHERE is_del = 0 and is_view = 1 and is_before = 0 and post_id = '{$post_id}' and created_at >= '{$default_data}' {$sql_buildigs} ORDER BY bb_id desc";
    // echo $sql;
    $res = sql_query($sql);
    
    $bbs_array = array();
    
    while($row = sql_fetch_array($res)){
    
        //print_r2($row);
        if($row['bbs_type'] == 'infomation'){
            if($row['is_submit'] == 'S'){
                array_push($bbs_array, $row);
            }
        }else{
            array_push($bbs_array, $row);
        }
    }
}else{

    $submit_sql = "";

    if($code == 'infomation'){
        $submit_sql = " and is_submit = 'S' ";
    }

    $sql = "SELECT * FROM a_building_bbs WHERE bbs_type = '{$code}' and is_del = 0 and is_view = 1 and is_before = 0 and post_id = '{$post_id}' and created_at >= '{$ho_tenant_at_de}' {$submit_sql} {$sql_buildigs} ORDER BY bb_id desc";
    // echo $sql;
    $res = sql_query($sql);

    $bbs_array = array();

    while($row = sql_fetch_array($res)){
    
        //print_r2($row);
        array_push($bbs_array, $row);
    }

}

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // print_r2($user_building);
    echo $sql.'<br>';
    echo $building_dates.'<br>';
    echo $ho_tenant_at_de.'<br>';
    echo $default_data.'<br>';
}
for($i=0;$i<count($bbs_array);$i++){
    switch($bbs_array[$i]['bbs_type']){
        case "public":
            $cate = "공문";
            break;
        case "event":
            $cate = "이벤트";
            break;
        case "infomation":
            $cate = "안내문";
            break;
    }
?>
<a href="/building_new_info.php?bb_id=<?php echo $bbs_array[$i]['bb_id']; ?>&type=<?php echo $type; ?>" class="content_box">
    <div class="content_box_icons">
        <img src="/images/build_icons.svg" alt="">
    </div>
    <div class="content_box_ct">
        <div class="content_box_ct1">
            <span><?php echo $cate; ?></span> <?php echo date('Y.m.d', strtotime($bbs_array[$i]['created_at'])); ?>
        </div>
        <div class="content_box_ct2">
            <?php echo $bbs_array[$i]['bb_title']; ?>
        </div>
    </div>
</a>
<?php }?>
<?php if($i==0){?>
<div class="building_news_empty complain_empty">
    등록된 내역이 없습니다.
</div>
<?php }?>
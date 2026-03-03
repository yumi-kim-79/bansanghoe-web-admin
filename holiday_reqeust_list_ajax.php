<?php
require_once "./_common.php";

//print_r($_POST);
$hd_titles = sql_fetch("SELECT sign_cate_name FROM a_sign_off_category WHERE sign_cate_code = '{$types}'");

$sql_where = "";

$page_name = $hd_titles['sign_cate_name']." 서류가 ";
$empty_msg = "등록된 ".$page_name." 없습니다.";

if($code == "R"){
    $sql_where = "and sign_off.sign_status = 'R'";

    $empty_msg = "결재 반려된 ".$page_name." 없습니다.";
}else if($code == "E"){
    $sql_where = "and sign_off.sign_status = 'E'";

    $empty_msg = "결재 승인된 ".$page_name." 없습니다.";
}else if($code == "P"){
    $sql_where = "and sign_off.sign_status = 'P'";

    $empty_msg = "결재 승인 중인 ".$page_name." 없습니다.";
}else if($code == "N"){
    $sql_where = "and sign_off.sign_status = 'N'";

    $empty_msg = "결재 승인 대기중인 ".$page_name." 없습니다.";
}


$mng_infos = get_manger($mb_id);

$sql_sign = "";
if($mng_infos['mng_certi'] != 'D'){
    $sql_sign = " and sign_off.sign_off_category = '{$types}' ";
}else{
    $sql_sign = " and sign_off.sign_off_category = '{$types}' and sign_off.mng_id = '{$mb_id}' ";
}



$sign_sql = "SELECT sign_off.*, cate.sign_cate_name FROM a_sign_off as sign_off
            LEFT JOIN a_sign_off_category AS cate ON sign_off.sign_off_category = cate.sign_cate_code
            WHERE sign_off.is_del = 0 {$sql_sign} {$sql_where} ORDER BY sign_id desc";
$sign_res = sql_query($sign_sql);

// if($_SERVER["REMOTE_ADDR"] == ADMIN_IP) echo $sign_sql;
// echo $sign_sql;
for($i=0;$sign_row = sql_fetch_array($sign_res);$i++){
    switch($sign_row['sign_status']){
        case "N":
            $status = "승인대기";
            break;
        case "P":
            $status = "승인중";
            break;
        case "E":
            $status = "승인완료";
            break;
        case "R":
            $status = "반려";
            break;
    }
    //echo $status; 

    $sign_mng = get_manger($sign_row['mng_id']);
   
?>
<a href="/holiday_reqeust_info.php?types=<?php echo $sign_row['sign_off_category']; ?>&sign_id=<?php echo $sign_row['sign_id']; ?>&tabIdx=<?php echo $tabIdx;?>&tabCode=<?php echo $tabCode; ?>" class="content_box ver3 ver_np">
    <div class="content_box_ct ver2">
        <div class="content_box_ct1">
            <span><?php echo $status; ?></span> <?php echo date("Y.m.d", strtotime($sign_row['created_at']));?>
        </div>
        <div class="content_box_ct2">
            <?php echo $sign_row['sign_cate_name'];?>
        </div>
        <div class="sign_writer mgt10">
            <div class="sign_writer_box"><?php echo $sign_mng['mng_name'];?></div>
            <div class="sign_writer_box"><?php echo $sign_mng['md_name'];?></div>
        </div>
    </div>
</a>
<?php }?>
<?php if($i==0){?>
<div class="content_box_empty"><?php echo $empty_msg; ?></div>
<?php }?>
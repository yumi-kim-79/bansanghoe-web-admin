<?php
require_once "./_common.php";

//print_r($_POST);
$sql_sch = "";

if($approval_sdate != "" && $approval_edate != ""){
    $sql_sch .= " and sign_off.created_at >= '{$approval_sdate}' and sign_off.created_at <= '{$approval_edate}' ";
}

if($department_type != ""){
    $sql_sch .= " and sign_off.mng_department = '{$department_type}' ";
}

if($sch_text != ""){
    $sql_sch .= " and mng.mng_name like '%{$sch_text}%' ";
}

$sql_where = "";
if($code == "reject"){
    $sql_where = "and sign_off.sign_status = 'R'";

    $empty_msg = "결재 반려된 서류가 없습니다.";
}else if($code == "success"){
    $sql_where = "and sign_off.sign_status = 'E'";

    $empty_msg = "결재 승인된 서류가 없습니다.";
}else{
    $sql_where = "and sign_off.sign_status IN ('N', 'P')";

    $empty_msg = "결재 서류가 없습니다.";
}

if($mng_certi != 'D'){

    // $sql_sign = " and ( ( sign_off.sign_off_mng_id1 = '{$mb_id}' or sign_off.sign_off_mng_id2 = '{$mb_id}' or sign_off.sign_off_mng_id3 = '{$mb_id}' )  or sign_off.mng_id = '{$mb_id}'  ) ";
    $sql_sign = "";

}else{
    // $sql_search = " WHERE sign_off.is_del = 0 and sign_off.mng_id = '{$mb_ids}' ";

    $sql_sign = " and sign_off.mng_id = '{$mb_id}' ";
}



$sign_sql = "SELECT sign_off.*, cate.sign_cate_name, mng.mng_name FROM a_sign_off as sign_off
            LEFT JOIN a_sign_off_category AS cate ON sign_off.sign_off_category = cate.sign_cate_code
            LEFT JOIN a_mng AS mng ON sign_off.mng_id = mng.mng_id
            WHERE sign_off.is_del = 0 {$sql_sch} {$sql_sign} {$sql_where} ORDER BY sign_id desc";
$sign_res = sql_query($sign_sql);

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    echo $mng_certi.'<br>';
    echo $sign_sql.'<br>';
}

//echo $sign_sql;
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
<a href="/holiday_reqeust_info.php?types=<?php echo $sign_row['sign_off_category']; ?>&sign_id=<?php echo $sign_row['sign_id']; ?>&mng=<?php echo $mng_chk; ?>" class="content_box ver3 ver_np">
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
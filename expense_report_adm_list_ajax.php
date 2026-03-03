<?php
require_once "./_common.php";

$sql_where = "";

if($code == "N"){

    $sql_where = " and ex_status IN ('N', 'P') ";

}else{
    $sql_where = " and ex_status = '{$code}' ";
}

$expense_sql = "SELECT * FROM a_expense_report WHERE is_del = 0 and building_id = '{$building_id}' and dong_id IN ('{$dong_id}', '-1'){$sql_where} ORDER BY ex_id desc";
$expense_res = sql_query($expense_sql);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    // echo $expense_sql.'<br>'; 
}

for($i=0;$expense_row = sql_fetch_array($expense_res);$i++){
    switch($expense_row['ex_status']){
        case "N":
            $status_r = "승인대기";
            break;
        case "P":
            $status_r = "승인중";
            break;
        case "E":
            $status_r = "승인완료";
            break;
    }
?>
<a href="/expense_report_adm_info.php?ex_id=<?php echo $expense_row['ex_id'];?>" class="content_box">
    <div class="content_box_icons">
        <img src="/images/report_icons.svg" alt="">
    </div>
    <div class="content_box_ct">
        <div class="content_box_ct1">
            <span><?php echo $status_r; ?></span> <?php echo date("Y.m.d", strtotime($expense_row['created_at'])); ?>
        </div>
        <div class="content_box_ct2">
            <?php echo $expense_row['ex_title']; ?>
        </div>
    </div>
</a>
<?php }?>
<?php if($i==0){?>
<div class="content_box_empty"><?php echo $code == 'N' ? '승인대기 중인' : '승인완료된' ;?> 품의서가 없습니다.</div>
<?php }?>
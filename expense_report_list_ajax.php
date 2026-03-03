<?php
require_once "./_common.php";

$sql_where = "";

if($code != ""){
    $sql_where = " and ex_status = '{$code}' ";
}

$expense_sql = "SELECT * FROM a_expense_report WHERE is_del = 0 and building_id = '{$building_id}' {$sql_where} ORDER BY ex_id desc";
//echo $expense_sql;
$expense_res = sql_query($expense_sql);

$empty_status = "";

switch($code){
    case "N":
        $empty_status = "승인대기 중인 품의서가 없습니다.";
        break;
    case "P":
        $empty_status = "승인 중인 품의서가 없습니다.";
        break;
    case "E":
        $empty_status = "승인 완료된 품의서가 없습니다.";
        break;
    default:
        $empty_status = "등록된 품의서가 없습니다.";
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

    $writer = "";
    if($expense_row['wid'] == "admin"){
        $writer = "신반상회";
    }else{
        $mng_info = get_manger($expense_row['wid']);

        $writer = $mng_info['md_name'].' '.$mng_info['mng_name'];
    }

    $enforce_info = get_manger($expense_row['enforce_id']);
?>
<a href="/expense_report_info.php?building_id=<?php echo $building_id; ?>&types=sm&ex_id=<?php echo $expense_row['ex_id']; ?>" class="content_box ver2">
    <div class="content_box_icons">
        <img src="/images/report_icons_g.svg" alt="">
    </div>
    <div class="content_box_ct">
        <div class="content_box_ct1">
            <span><?php echo $status_r; ?></span> <?php echo date("Y.m.d", strtotime($expense_row['created_at'])); ?>
        </div>
        <div class="content_box_ct2">
            <?php echo $expense_row['ex_title']; ?>
        </div>
        <div class="sm_schedule_box_bot mgt10">
            <div class="sm_schedule_box_bot2">
                <div class="sm_sche_bot_cont">작성자: <?php echo $expense_row['ex_name']; ?></div>
                <?php if($expense_row['enforce_deaprt'] != ""){?>
                <div class="sm_sche_bot_cont">담당자: <?php echo $enforce_info['md_name'];?> <?php echo $enforce_info['mng_name'];?></div>
                <?php }?>
            </div>
        </div>
    </div>
</a>
<?php }?>
<?php if($i==0){?>
<div class="content_box_empty"><?php echo $empty_status;?></div>
<?php }?>
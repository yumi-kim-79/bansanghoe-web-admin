<?php
require_once "./_common.php";

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
    $sql_sign = "";
}else{
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

    $sign_mng = get_manger($sign_row['mng_id']);

    // 1~3차 결재자 정보 조회
    $sign_steps = [];
    for($s=1;$s<=3;$s++){
        $mng_id_key = "sign_off_mng_id{$s}";
        $status_key = "sign_off_status{$s}";
        if($s == 1) $status_key = "sign_off_status";

        $mng_id_val = $sign_row[$mng_id_key];
        if($mng_id_val != ''){
            $mng_info = sql_fetch("SELECT mg.mg_name FROM a_mng as m LEFT JOIN a_mng_grade as mg ON m.mng_grades = mg.mg_idx WHERE m.mng_id = '{$mng_id_val}'");
            $sign_steps[] = [
                'grades' => $mng_info['mg_name'] ? $mng_info['mg_name'] : $s.'차',
                'status' => $sign_row[$status_key],
            ];
        }
    }
?>
<a href="/holiday_reqeust_info.php?types=<?php echo $sign_row['sign_off_category']; ?>&sign_id=<?php echo $sign_row['sign_id']; ?>&mng=<?php echo $mng_chk; ?>" class="content_box ver3 ver_np sign_list_item">
    <div class="sign_list_left">
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
    <?php if(count($sign_steps) > 0){ ?>
    <div class="sign_steps_wrap">
        <?php foreach($sign_steps as $step){ ?>
        <div class="sign_step_item <?php echo $step['status'] == 1 ? 'signed' : 'unsigned'; ?>">
            <span class="sign_step_name"><?php echo $step['grades']; ?></span>
            <span class="sign_step_icon"><?php echo $step['status'] == 1 ? '✓' : '–'; ?></span>
        </div>
        <?php } ?>
    </div>
    <?php } ?>
</a>
<?php }?>
<?php if($i==0){?>
<div class="content_box_empty"><?php echo $empty_msg; ?></div>
<?php }?>
<style>
.sign_list_item {
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
.sign_list_left {
    flex: 1;
    min-width: 0;
}
.sign_steps_wrap {
    display: flex;
    flex-direction: column;
    gap: 5px;
    flex-shrink: 0;
}
.sign_step_item {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    border: 1px solid #ddd;
    background: #f5f5f5;
    white-space: nowrap;
}
.sign_step_item.signed {
    background: #e8f5e9;
    border-color: #4caf50;
    color: #2e7d32;
}
.sign_step_item.unsigned {
    background: #f5f5f5;
    border-color: #ddd;
    color: #999;
}
.sign_step_icon {
    font-weight: bold;
    font-size: 12px;
}
</style>

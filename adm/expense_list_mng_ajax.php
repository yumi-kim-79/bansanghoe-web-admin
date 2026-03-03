<?php
require_once './_common.php';

//동
$dong_sql = "SELECT dong.*, building.building_name FROM a_building_dong as dong
             LEFT JOIN a_building as building on dong.building_id = building.building_id
             WHERE dong_id = '{$dong_id}'";
$dong_row = sql_fetch($dong_sql);

$buidling_name = get_builiding_info($building_id)['building_name'];

$ex_report_sql = "SELECT * FROM a_expense_report WHERE is_del = 0 and ex_id = '{$ex_id}'";
// echo $ex_report_sql;
$ex_report_row = sql_fetch($ex_report_sql);

$ex_approver1 = get_mng_team($ex_report_row['ex_approver1']);
$ex_approver2 = get_mng_team($ex_report_row['ex_approver2']);
$ex_approver3 = get_mng_team($ex_report_row['ex_approver3']);

$dong_sql = "and mt.dong_id = '{$dong_id}'";

if($dong_id == '-1' || $dong_id == ''){
  $dong_sql = " and mt.build_id = '{$building_id}'";
}


//1차 결재자 정보
$mng_team_info = "SELECT mt.*, dong.dong_name, ho.ho_name FROM a_mng_team as mt
                    LEFT JOIN a_building_dong as dong on mt.dong_id = dong.dong_id
                    LEFT JOIN a_building_ho as ho on mt.ho_id = ho.ho_id
                    WHERE mt.mb_id = '{$ex_report_row['ex_approver1']}' {$dong_sql}";
// echo $mng_team_info.'<br>';
$mng_tem_info_row = sql_fetch($mng_team_info);

if($mng_tem_info_row['mt_type'] == 'OUT'){
    $approval1_info = "외부인 (".$mng_tem_info_row['mt_name'].") - ".$ex_approver1['gr_name'];
}else{
    $approval1_info = $mng_tem_info_row['dong_name']."동 ".$mng_tem_info_row['ho_name']."호 (".$mng_tem_info_row['mt_name'].") - ".$ex_approver1['gr_name'];
}

if($ex_report_row['ex_apprval1_chk']){
    $expense_sign_date_row = sql_fetch("SELECT ex_id, approval_id, apprval_type, created_at FROM a_expense_report_sign WHERE ex_id = '{$ex_id}' and apprval_type = 'apprval1' and approval_id = '{$ex_report_row['ex_approver1']}'");

    $expense_sign_date1 = $expense_sign_date_row['created_at'];
}

//2차 결재자 정보
$mng_team_info2 = "SELECT mt.*, dong.dong_name, ho.ho_name FROM a_mng_team as mt
                    LEFT JOIN a_building_dong as dong on mt.dong_id = dong.dong_id
                    LEFT JOIN a_building_ho as ho on mt.ho_id = ho.ho_id
                    WHERE mt.mb_id = '{$ex_report_row['ex_approver2']}' {$dong_sql}";
$mng_tem_info_row2 = sql_fetch($mng_team_info2);

if($mng_tem_info_row2['mt_type'] == 'OUT'){
    $approval2_info = "외부인 (".$mng_tem_info_row2['mt_name'].") - ".$ex_approver2['gr_name'];
}else{
    $approval2_info = $mng_tem_info_row2['dong_name']."동 ".$mng_tem_info_row2['ho_name']."호 (".$mng_tem_info_row2['mt_name'].") - ".$ex_approver2['gr_name'];
}

if($ex_report_row['ex_apprval2_chk']){
    $expense_sign_date_row2 = sql_fetch("SELECT ex_id, approval_id, apprval_type, created_at FROM a_expense_report_sign WHERE ex_id = '{$ex_id}' and apprval_type = 'apprval2' and approval_id = '{$ex_report_row['ex_approver2']}'");

    $expense_sign_date2 = $expense_sign_date_row2['created_at'];
}


//3차 결재자 정보
$mng_team_info3 = "SELECT mt.*, dong.dong_name, ho.ho_name FROM a_mng_team as mt
                    LEFT JOIN a_building_dong as dong on mt.dong_id = dong.dong_id
                    LEFT JOIN a_building_ho as ho on mt.ho_id = ho.ho_id
                    WHERE mt.mb_id = '{$ex_report_row['ex_approver3']}' {$dong_sql}";
$mng_tem_info_row3 = sql_fetch($mng_team_info3);

if($mng_tem_info_row3['mt_type'] == 'OUT'){
    $approval3_info = "외부인 (".$mng_tem_info_row3['mt_name'].") - ".$ex_approver3['gr_name'];
}else{
    $approval3_info = $mng_tem_info_row3['dong_name']."동 ".$mng_tem_info_row3['ho_name']."호 (".$mng_tem_info_row3['mt_name'].") - ".$ex_approver3['gr_name'];
}

if($ex_report_row['ex_apprval3_chk']){
    $expense_sign_date_row3 = sql_fetch("SELECT ex_id, approval_id, apprval_type, created_at FROM a_expense_report_sign WHERE ex_id = '{$ex_id}' and apprval_type = 'apprval3' and approval_id = '{$ex_report_row['ex_approver3']}'");

    $expense_sign_date3 = $expense_sign_date_row3['created_at'];
}


//echo $ex_approver1['mt_name']; - echo $ex_approver1['gr_name'];
?>
<div class="cm_pop_desc4 ver2 mgt10"><?php echo $buidling_name; ?> <?php echo $dong_id != '-1' ? $dong_row['dong_name'].'동' : '전체';?></div>
<div class="sign_off_list_pop_wrap mgt10">
    <div class="sign_off_list_pop_box">
        <p class="sign_off_list_label">1차 결재자</p>
        <div class="sign_off_list_info mgt5"><?php echo $approval1_info; ?></div>
        <div class="sign_off_list_pop_box_status <?php echo $ex_report_row['ex_apprval1_chk'] ? 'ver2' : ''?>">
            <div class="sign_off_list_pop_box_status_t">
                <?php echo $ex_report_row['ex_apprval1_chk'] ? '결재완료' : '결재대기'?>
            </div>
            <div class="sign_off_dates mgt5"><?php echo $expense_sign_date1; ?></div>
        </div>
    </div>
    <?php if($ex_report_row['ex_approver2'] != ""){?>
    <div class="sign_off_list_pop_box">
        <p class="sign_off_list_label">2차 결재자</p>
        <div class="sign_off_list_info mgt5"><?php echo $approval2_info; ?></div>
        <div class="sign_off_list_pop_box_status <?php echo $ex_report_row['ex_apprval2_chk'] ? 'ver2' : ''?>">
            <div class="sign_off_list_pop_box_status_t">
                <?php echo $ex_report_row['ex_apprval2_chk'] ? '결재완료' : '결재대기'?>
            </div>
            <div class="sign_off_dates mgt5"><?php echo $expense_sign_date2; ?></div>
        </div>
    </div>
    <?php }?>
    <?php if($ex_report_row['ex_approver3'] != ""){?>
    <div class="sign_off_list_pop_box">
        <p class="sign_off_list_label">3차 결재자</p>
        <div class="sign_off_list_info mgt5"><?php echo $approval3_info; ?></div>
        <div class="sign_off_list_pop_box_status <?php echo $ex_report_row['ex_apprval3_chk'] ? 'ver2' : ''?>">
            <div class="sign_off_list_pop_box_status_t">
                <?php echo $ex_report_row['ex_apprval3_chk'] ? '결재완료' : '결재대기'?>
            </div>
            <div class="sign_off_dates mgt5"><?php echo $expense_sign_date3; ?></div>
        </div>
    </div>
    <?php }?>
</div>
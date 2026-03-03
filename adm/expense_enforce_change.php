<?php
require_once './_common.php';

if($enforce_deaprt == "") die(result_data(false, "시행자의 부서명을 선택해주세요.", 'enforce_deaprt'));
if($enforce_grade == "") die(result_data(false, "시행자의 직급을 선택해주세요.", 'enforce_grade'));
if($enforce_id == "") die(result_data(false, "시행자의 이름을 선택해주세요.", 'enforce_id'));

$expense_row = sql_fetch("SELECT * FROM a_expense_report WHERE ex_id = '{$ex_id}'");

$today = date("Y-m-d H:i:s");

//시행자 이름 가져옴
$enforce_name = get_member($enforce_id);
$enforce_name = $enforce_name['mb_name'];

//수정
$update_query = "UPDATE a_expense_report SET
                    enforce_deaprt = '{$enforce_deaprt}',
                    enforce_grade = '{$enforce_grade}',
                    enforce_name = '{$enforce_name}',
                    enforce_id = '{$enforce_id}'
                    WHERE ex_id = '{$ex_id}'";

sql_query($update_query);

$msg = '품의서에 시행자가 등록되었습니다.';

//시행자가 변경되거나 입력되면
if($expense_row['enforce_id'] != $enforce_id){
    $enforce_info = get_member($enforce_id);

    $push_title = "[품의서] 시행자로 등록되었습니다.";
    $push_content = "품의서의 시행자로 등록되었습니다.";

    //푸시발송
    $insert_push = "INSERT INTO a_push SET
                    recv_id_type = 'sm',
                    recv_id = '{$enforce_id}',
                    push_title = '{$push_title}',
                    push_content = '{$push_content}',
                    wid = '{$mb_id}',
                    push_type = 'expense2',
                    push_idx = '{$ex_id}',
                    created_at = '{$today}'";
    sql_query($insert_push);

    if($enforce_info['mb_token'] != ""){ //토큰이 있는경우 푸시 발송
        fcm_send($enforce_info['mb_token'], $push_title, $push_content, 'expense2', "{$ex_id}", "/expense_report_info.php?types=sm&building_id=".$expense_row['building_id']."&ex_id=");
    }
}

echo result_data(true, $msg, $ex_id);
<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

//print_r2($_POST);

if($w == "u"){

    $sql_confirm = "SELECT COUNT(*) as cnt FROM a_manage_company WHERE company_name = '{$company_name}' and company_idx != '{$company_idx}'";
    $row_confirm = sql_fetch($sql_confirm);

    if($row_confirm['cnt'] > 0) alert("이미 등록된 업체명입니다.");

    $insert_query = "UPDATE a_manage_company SET
                        company_number = '{$company_number}',
                        company_industry = '{$company_industry}',
                        company_tel = '{$company_tel}',
                        company_mng_name = '{$company_mng_name}',
                        company_mng_tel = '{$company_mng_tel}',
                        company_bank_name = '{$company_bank_name}',
                        company_account_number = '{$company_account_number}',
                        company_account_name = '{$company_account_name}',
                        company_memo = '{$company_memo}'
                        WHERE company_idx = '{$company_idx}'";
    //echo $insert_query.'<br>';

    sql_query($insert_query);
}else{

    $sql_confirm = "SELECT COUNT(*) as cnt FROM a_manage_company WHERE company_name = '{$company_name}'";
    $row_confirm = sql_fetch($sql_confirm);

    if($row_confirm['cnt'] > 0) alert("이미 등록된 업체명입니다.");

    $insert_query = "INSERT INTO a_manage_company SET
                        company_number = '{$company_number}',
                        company_name = '{$company_name}',
                        company_industry = '{$company_industry}',
                        company_tel = '{$company_tel}',
                        company_mng_name = '{$company_mng_name}',
                        company_mng_tel = '{$company_mng_tel}',
                        company_bank_name = '{$company_bank_name}',
                        company_account_number = '{$company_account_number}',
                        company_account_name = '{$company_account_name}',
                        company_memo = '{$company_memo}',
                        created_at = '{$today}'";
    
    //echo $insert_query.'<br>';
    sql_query($insert_query);
}

//exit;

if($w == "u"){
    alert('업체가 수정되었습니다.');
}else{

    alert('업체가 추가되었습니다.');
}
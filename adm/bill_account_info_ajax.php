<?php
require_once './_common.php';


if($types == "bank"){
    if($bill_account_name == "") die(result_data(false, "은행명을 입력하세요.", "bill_account_name"));
    if($bill_account == "") die(result_data(false, "납부 계좌번호를 입력하세요.", "bill_account"));

    $update_bill_info = "UPDATE a_bill_mng SET
                        bill_account_name = '{$bill_account_name}',
                        bill_account = '{$bill_account}' ";

    //die(result_data(false, $update_bill_info, []));
    sql_query($update_bill_info);

    echo result_data(true, "납부계좌 정보가 수정되었습니다.", []);
}else{
    //if($bill_memo == "") die(result_data(false, "공지사항을 입력하세요.", "bill_memo"));

    $update_bill_info = "UPDATE a_bill_mng SET
                        bill_memo = '{$bill_memo}'";

    //die(result_data(false, $update_bill_info, []));
    sql_query($update_bill_info);

    echo result_data(true, "고지서 공지사항이 수정되었습니다.", []);
}

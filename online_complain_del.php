<?php
include_once('./_common.php');

$today = date("Y-m-d H:i:s");

$complain_del = "UPDATE a_online_complain SET
                    is_del = 1,
                    deleted_at = '{$today}'
                    WHERE complain_idx = '{$complain_idx}'";
sql_query($complain_del);

//die();

echo result_data(true, "민원이 삭제되었습니다.", []);
?>
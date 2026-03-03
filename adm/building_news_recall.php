<?php
require_once "./_common.php";

if($bb_id == "") die(result_data(false, "회수에 실패하였습니다.", []));

$today = date("Y-m-d H:i:s");

$update_query = "UPDATE a_building_bbs SET
                    is_submit = 'R',
                    recalled_at = '{$today}',
                    recall_memo = '{$recall_memo}'
                    WHERE bb_id = '{$bb_id}'";
sql_query($update_query);

echo result_data(true, "회수되었습니다.", []);
?>
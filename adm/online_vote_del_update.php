<?php
require_once './_common.php';

if($vt_id == "") die(result_data(false, "잘못된 접근입니다.", []));

$today = date("Y-m-d H:i:s");

$del_update = "UPDATE a_online_vote SET
                is_del = 1,
                deleted_at = '{$today}'
                WHERE vt_id = {$vt_id}";
sql_query($del_update);


$del_question = "UPDATE a_online_vote_question SET
                    is_del = 1,
                    deleted_at = '{$today}'
                    WHERE vt_id = '{$vt_id}'";
sql_query($del_question);

echo result_data(true, "투표가 삭제되었습니다.", []);
?>
<?php
include_once('./_common.php');

$sql = "SELECT * FROM a_building WHERE building_id = '{$building_id}'";
$row = sql_fetch($sql);

// [고지서 2026-09] 단지를 바꾸면 화면의 '지역' 표시도 같이 바꿔야 해서 지역명을 함께 내려준다.
//  msg 는 기존 호출부가 post_id 로 쓰고 있으므로 그대로 둔다.
$post_name = '';
if (!empty($row['post_id'])) {
    $post = sql_fetch("SELECT post_name FROM a_post_addr WHERE post_idx = '{$row['post_id']}'");
    if ($post) $post_name = $post['post_name'];
}

echo result_data(true, $row['post_id'], array(
    'post_id'   => $row['post_id'],
    'post_name' => $post_name,
));

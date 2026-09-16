<?php
require_once './_common.php';

$post_sql = "";
if($post_id != ""){
    $post_sql = " and post_id = '{$post_id}' ";
}

$sql_building = "SELECT * FROM a_building WHERE is_use = 1 and building_name like '%{$building_name}%' {$post_sql} ORDER BY building_name asc";

// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $sql_building;
$res_building = sql_query($sql_building);

$total_building = sql_num_rows($res_building);

if($total_building > 0){
for($i=0;$row_building = sql_fetch_array($res_building);$i++){
?>
<button type="button" onclick="building_select('<?php echo $row_building['building_id']; ?>', '<?php echo $row_building['building_name']; ?>');"><?php echo $row_building['building_name']; ?></button>
<?php }?>
<?php }else{

// [2026-09] 결과가 없으면 아무것도 안 그려서, 사용자에게는 "검색이 안 된다"로만 보였다.
//  왜 안 나오는지까지 알려준다. (지역 필터에 걸린 건지, 사용 안 함 단지인지)
$hint = '검색 결과가 없습니다.';

$other = sql_fetch("SELECT COUNT(*) as cnt FROM a_building WHERE is_use = 1 and building_name like '%{$building_name}%'");

if($post_sql != "" && $other['cnt'] > 0){
    $hint = '선택한 지역에는 없습니다. 다른 지역에 '.$other['cnt'].'건 있습니다 — 지역을 [전체]로 바꾼 뒤 검색해 주세요.';
}else{
    $off = sql_fetch("SELECT COUNT(*) as cnt FROM a_building WHERE is_use = 0 and building_name like '%{$building_name}%'");
    if($off['cnt'] > 0){
        $hint = '사용 안 함 상태인 단지입니다. 단지관리에서 사용 여부를 확인해 주세요.';
    }
}
?>
<div class="sch_no_result" style="padding:9px 11px;color:#a3252c;font-size:13px;line-height:1.5;"><?php echo $hint; ?></div>
<?php }?>

<?php
require_once "./_common.php";

$building_sql = "SELECT * FROM a_building WHERE is_del = 0 and is_use = 1 and building_name like '%{$sch_text}%' ORDER BY building_id desc";
//echo $building_sql;
$building_res = sql_query($building_sql);

for($i=0;$building_row=sql_fetch_array($building_res);$i++){
?>
<div class="sch_building_box" data-idx="<?php echo $building_row['building_id']; ?>" data-name="<?php echo $building_row['building_name']; ?>">
    <div class="sch_building_tit"><?php echo $building_row['building_name']; ?></div>
    <div class="sch_building_addr mgt10"><?php echo $building_row['building_addr']; ?></div>
</div>
<?php }?>
<?php if($i==0){?>
<div class="faq_empty_box">등록된 단지가 없습니다.</div>
<?php }?>
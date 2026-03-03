<?php
require_once "./_common.php";

$sql_sch = "";
if($dong_id != ""){
    $sql_sch .= " and ho.dong_id = '{$dong_id}' ";
}

if($schText != ""){
    $sql_sch .= " and ho.ho_name like '%{$schText}%' ";
}



$order_by = " dong_name asc, CAST(SUBSTRING_INDEX(ho_name, '-', 1) AS UNSIGNED) ASC, 
  CASE WHEN ho_name REGEXP '-' THEN 1 ELSE 0 END ASC,         
  CAST(SUBSTRING_INDEX(ho_name, '-', -1) AS UNSIGNED) ASC, 
  ho_name ASC ";
   //echo $sql_sch;


$ho_sql = "SELECT ho.*, dong.dong_name FROM a_building_ho as ho
            LEFT JOIN a_building_dong as dong on ho.dong_id = dong.dong_id
            WHERE ho.is_del = 0 and ho.building_id = '{$building_id}' {$sql_sch} ORDER BY {$order_by}";
$ho_res = sql_query($ho_sql);

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    echo $ho_sql.'<br>';
}
//echo $ho_sql;
for($i=0;$ho_row = sql_fetch_array($ho_res);$i++){
?>
 <li>
    <a href="/household_mng_info.php?ho_id=<?php echo $ho_row['ho_id']; ?>" class="<?php echo $ho_row['ho_status'] == 'Y' ? 'ver2' : '';?>">
        <div class="hh_left">
            <div class="hh_left_dong"><?php echo $ho_row['dong_name']; ?>동</div>
            <div class="hh_left_ho"><?php echo $ho_row['ho_name']; ?>호</div>
        </div>
        <div class="hh_right"><?php echo $ho_row['ho_status'] == 'Y' ? '입주' : '공실';?></div>
    </a>
</li>
<?php }?>
<?php if($i==0){?>
<li class="empty_li">등록된 호수가 없습니다.</li>
<?php }?>
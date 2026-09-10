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

// [2026-09] 관리자 IP 접속 시 목록 위에 SQL 원문을 그대로 출력하던 디버그 코드 제거
//echo $ho_sql;
for($i=0;$ho_row = sql_fetch_array($ho_res);$i++){
?>
 <?php
    // [세대목록 2026-09] 소유자/입주자 성함 추가 (2줄 구성)
    //  윗줄: 동 / 호수 / 입주·공실 (기존 그대로)
    //  아랫줄: 소유자 · 입주자 — 이름이 길어도 안 잘리도록 별도 줄로 뺐다.
    //  값은 이미 목록 SQL(SELECT ho.*)에 들어 있어 추가 쿼리가 없다.
    //  공실 세대의 입주자는 '-' (관리자웹 세대목록·앱 상세화면과 동일 규칙)
    $hh_is_in  = ($ho_row['ho_status'] == 'Y');
    $hh_owner  = trim((string)$ho_row['ho_owner']);
    $hh_tenant = trim((string)$ho_row['ho_tenant']);
    if($hh_owner === '')  $hh_owner  = '-';
    $hh_tenant = $hh_is_in ? ($hh_tenant === '' ? '-' : $hh_tenant) : '-';
 ?>
 <li>
    <a href="/household_mng_info.php?ho_id=<?php echo $ho_row['ho_id']; ?>" class="hh_v3 <?php echo $hh_is_in ? 'ver2' : '';?>">
        <div class="hh_row">
            <div class="hh_left">
                <div class="hh_left_dong"><?php echo $ho_row['dong_name']; ?>동</div>
                <div class="hh_left_ho"><?php echo $ho_row['ho_name']; ?>호</div>
            </div>
            <div class="hh_right"><?php echo $hh_is_in ? '입주' : '공실';?></div>
        </div>
        <div class="hh_names">
            <span>소유자 <b><?php echo $hh_owner; ?></b></span>
            <span>입주자 <b><?php echo $hh_tenant; ?></b></span>
        </div>
    </a>
</li>
<?php }?>
<?php if($i==0){?>
<li class="empty_li">등록된 호수가 없습니다.</li>
<?php }?>
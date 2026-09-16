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
    // [세대목록 2026-09] 한 세대를 한 줄로 — 입주여부 / 동 / 호수 / 소유자 / 입주자
    //  값은 이미 목록 SQL(SELECT ho.*)에 들어 있어 추가 쿼리가 없다.
    //  공실 세대의 입주자는 '-' (관리자웹 세대목록·앱 상세화면과 동일 규칙)
    //  이름이 길면 자르지 않고 다음 줄로 흘려보낸다(flex-wrap).
    $hh_is_in  = ($ho_row['ho_status'] == 'Y');
    $hh_owner  = trim((string)$ho_row['ho_owner']);
    $hh_tenant = trim((string)$ho_row['ho_tenant']);
    if($hh_owner === '')  $hh_owner  = '-';
    $hh_tenant = $hh_is_in ? ($hh_tenant === '' ? '-' : $hh_tenant) : '-';
 ?>
 <li>
    <a href="/household_mng_info.php?ho_id=<?php echo $ho_row['ho_id']; ?>" class="hh_v3 <?php echo $hh_is_in ? 'ver2' : '';?>">
        <span class="hh_state"><?php echo $hh_is_in ? '입주' : '공실';?></span>
        <span class="hh_dong"><?php echo $ho_row['dong_name']; ?>동</span>
        <span class="hh_ho"><?php echo $ho_row['ho_name']; ?>호</span>
        <span class="hh_person"><em>소유자</em> <?php echo $hh_owner; ?></span>
        <span class="hh_person hh_person2"><em>입주자</em> <?php echo $hh_tenant; ?></span>
    </a>
</li>
<?php }?>
<?php if($i==0){?>
<li class="empty_li">등록된 호수가 없습니다.</li>
<?php }?>
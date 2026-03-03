<?php
require_once "./_common.php";

$sql_where = "";

if($dong_id != ""){
    $sql_where = " and mng_t.dong_id = '{$dong_id}' ";
}

$mng_team_sql = "SELECT mng_t.*, mng_gr.gr_name, building.is_use, dong.dong_name, ho.ho_name FROM a_mng_team as mng_t
                 LEFT JOIN a_mng_team_grade as mng_gr on mng_t.mt_grade = mng_gr.gr_id
                 LEFT JOIN a_building_dong as dong on mng_t.dong_id = dong.dong_id
                 LEFT JOIN a_building_ho as ho on mng_t.ho_id = ho.ho_id
                 LEFT JOIN a_building as building on mng_t.build_id = building.building_id
                 WHERE mng_t.is_del = 0 and mng_t.build_id = '{$building_id}' and building.is_use = 1 {$sql_where} ORDER BY dong.dong_name + 1 asc, ho.ho_name + 1 asc, mng_t.mt_id desc";
// echo $mng_team_sql;

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    echo $mng_team_sql.'<br>';
}
$mng_team_res = sql_query($mng_team_sql);

for($i=0;$mng_team_row = sql_fetch_array($mng_team_res);$i++){
?>
<div class="mng_cont_box_wrap">
    <div class="mng_cont_box">
        <div class="mng_cont_label">동/호수</div>
        <div class="mng_cont_infos">
            <?php echo $mng_team_row['mt_type'] == 'OUT' ? '외부인' : $mng_team_row['dong_name'].'동 '.$mng_team_row['ho_name'].'호' ;?>
        </div>
    </div>
    <div class="mng_cont_box">
        <div class="mng_cont_label">직책</div>
        <div class="mng_cont_infos"><?php echo $mng_team_row['gr_name']; ?></div>
    </div>
    <div class="mng_cont_box">
        <div class="mng_cont_label">이름</div>
        <div class="mng_cont_infos"><?php echo $mng_team_row['mt_name']; ?></div>
    </div>
    <div class="mng_cont_box tel">
        <div class="mng_cont_label">연락처</div>
        <div class="mng_cont_infos tel">
            <?php echo $mng_team_row['mt_hp']; ?>
            <a href="tel:<?php echo $mng_team_row['mt_hp']; ?>" class="tel_btn"><i><img src="/images/phone_icons.svg" alt=""></i>전화걸기</a>
        </div>
    </div>
    <div class="mng_cont_box">
        <div class="mng_cont_label">메모</div>
        <div class="mng_cont_infos"><?php echo nl2br($mng_team_row['mt_memo']); ?></div>
    </div>
</div>
<?php }?>
<?php if($i==0){?>
<div class="mng_cont_empty">등록된 관리단이 없습니다.</div>
<?php }?>
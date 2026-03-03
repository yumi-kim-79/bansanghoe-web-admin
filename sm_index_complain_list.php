<?php
require_once "./_common.php";

//$cstatus = "CB";

if($status == "CB"){
    $empty_msg = "할당대기 중인 민원이 없습니다.";
}else if($status == "CA"){
    $empty_msg = "접수대기 중인 민원이 없습니다.";
}else if($status == "CC"){
    $empty_msg = "진행 중인 민원이 없습니다.";
}else if($status == "CD"){
    $empty_msg = "완료된 민원이 없습니다.";
}

$mng_building = get_mng_building($mb_id);

$mng_building_t = "'".implode("','", $mng_building)."'";

$sql_where = "";
if($status == "CA"){
    $sql_where = " and complain.mng_department = '{$department}' ";
}else if($status == "CC"){
    $sql_where = " and complain.mng_id = '{$mb_id}' ";
}

if($sch_text != ""){
    $sql_where .= " and building.building_name like '%{$sch_text}%' ";
}

//and complain.building_id IN ({$mng_building_t})
$complain_sql = "SELECT complain.*, cstatus.cs_name, building.building_name, building.is_use, dong.dong_name, ho.ho_name FROM a_online_complain as complain
                 LEFT JOIN a_complain_status as cstatus ON complain.complain_status = cstatus.cs_code
                 LEFT JOIN a_building as building ON complain.building_id = building.building_id
                 LEFT JOIN a_building_dong as dong ON complain.dong_id = dong.dong_id
                 LEFT JOIN a_building_ho as ho ON complain.ho_id = ho.ho_id
                 WHERE complain.is_del = 0 and building.is_use = 1  and complain.complain_status = '{$status}' {$sql_where}
                 ORDER BY complain.complain_idx desc";
$complain_res = sql_query($complain_sql);
// echo $complain_sql;

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $complain_sql;
}

for($i=0;$complain_row = sql_fetch_array($complain_res);$i++){
    $building_infos = get_builiding($complain_row['ho_id']);

    if($complain_row['complain_type'] == "admin"){
        $writer = get_manger($complain_row['complain_id'])['mng_name'];
    }else{
        $writer = get_user($complain_row['complain_id'])['mb_name'];
    }
?>
<div class="sm_schedule_box sm_schedule_box3 <?php echo $complain_row['complain_type'] == 'user' ? 'on3' : '';?>">
    <a href="/sm_complain_info.php?complain_idx=<?php echo $complain_row['complain_idx']; ?>&complain_status=<?php echo $complain_row['complain_status']; ?>">
        <div class="sm_schedule_box_top">
            <div class="sm_schedule_date"><?php echo date("Y.m.d", strtotime($complain_row['created_at'])); ?></div>
            <div class="sm_schedule_status"><?php echo $complain_row['cs_name']; ?></div>
        </div>
        <div class="sm_schedule_box_addr mgt5">
        <?php echo $complain_row['building_name'];?> <?php echo $complain_row['dong_name'];?>동 <?php echo $complain_row['ho_name']; ?>호
        </div>
        <div class="sm_schedule_box_mid mgt5">
            <?php echo $complain_row['complain_title']; ?>
        </div>
        <div class="sm_complain_box_wrap">
            <div class="sm_complain_box">
                <div class="sm_complain_label">작성자</div>
                <div class="sm_compain_ct"><?php echo $complain_row['complain_name']; ?></div>
            </div>
            <div class="sm_complain_box">
                <div class="sm_complain_label">담당 부서</div>
                <div class="sm_compain_ct"><?php echo $complain_row['mng_department'] != "" ? get_department_name($complain_row['mng_department']) : "-";?></div>
            </div>
            <div class="sm_complain_box">
                <div class="sm_complain_label">담당자</div>
                <div class="sm_compain_ct">
                    <?php if($complain_row['mng_id'] != ""){
                        $mng_name = get_manger($complain_row['mng_id'])['mng_name'];
                        $mg_name = get_manger($complain_row['mng_id'])['mg_name'];
                        echo $mng_name.' '.$mg_name;
                        ?>
                    <?php }else{ ?>
                        -
                    <?php }?>
                </div>
            </div>
        </div>
    </a>
</div>
<?php }?>
<?php if($i==0){?>
<div class="complain_empty"><?php echo $empty_msg; ?></div>
<?php }?>
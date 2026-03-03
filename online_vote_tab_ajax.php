<?php
require_once "./_common.php";

$today = date("Y-m-d");

$vote_arr = array();

$dong_id_sql = "";
if($types != "sm"){
    $dong_id_sql = " and (dong_id = '{$dong_id}' or dong_id = '-1')";
}

$sql_sch = " and building_id = '{$building_id}' {$dong_id_sql} ";

if($code == 'prg'){
    

    if($types != "sm"){
        $sql_sch .= " and created_at >= '{$ho_tenant_at_de}' ";
    }

    $vote_sql = "SELECT * FROM a_online_vote WHERE is_del = 0 and vt_status = 1  {$sql_sch}  ORDER BY vt_id desc ";
    // echo $vote_sql;

    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        // echo $vote_sql;
    }
    $vote_res = sql_query($vote_sql);

    while($vote_row = sql_fetch_array($vote_res)){

        if($vote_row['vt_period_type'] == 'period'){

            if($vote_row['vt_status'] == '1'){
                if($vote_row['vt_edate'] >= $today && $vote_row['vt_sdate'] <= $today){
                    array_push($vote_arr, $vote_row);
                }
            }
           
        }else{

            if($vote_row['vt_period_type'] == 'personnel'){

                $sql_building_ho_number = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_ho WHERE ho_status = 'Y' {$sql_sch} ");
                //echo $sql_building_ho_number['cnt'].'<br>';

                $vote_number = sql_fetch("SELECT COUNT(*) as cnt FROM a_online_vote_result WHERE vt_id = '{$vote_row['vt_id']}'");
                //echo $vote_number['cnt'];

                if($sql_building_ho_number['cnt'] > $vote_number['cnt']){
                    array_push($vote_arr, $vote_row);
                }
            }else{
                array_push($vote_arr, $vote_row);
            }
        }
        
    }

}else{

    if($types != "sm"){
        $sql_sch .= " and created_at >= '{$ho_tenant_at_de}' ";
    }

    $vote_sql = "SELECT * FROM a_online_vote WHERE is_del = 0  {$sql_sch} ORDER BY vt_id desc ";
    // echo $vote_sql;
    $vote_res = sql_query($vote_sql);

    if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
        // echo $vote_sql;
    }

    while($vote_row = sql_fetch_array($vote_res)){

        if($vote_row['vt_period_type'] == 'period'){
            if($vote_row['vt_edate'] < $today || $vote_row['vt_status'] == '2'){
                array_push($vote_arr, $vote_row);
            }
        }else{
            if($vote_row['vt_period_type'] == 'personnel' || $vote_row['vt_period_type'] == 'period_not'){

                // $sql_building_ho_number = sql_fetch("SELECT COUNT(*) as cnt FROM a_building_ho WHERE ho_status = 'Y' {$sql_sch} ");
                // //echo $sql_building_ho_number['cnt'].'<br>';

                // $vote_number = sql_fetch("SELECT COUNT(*) as cnt FROM a_online_vote_result WHERE vt_id = '{$vote_row['vt_id']}'");
                // //echo $vote_number['cnt'];

                // if($sql_building_ho_number['cnt'] == $vote_number['cnt']){
                //     array_push($vote_arr, $vote_row);
                // }

                if($vote_row['vt_status'] == '2'){
                    array_push($vote_arr, $vote_row);
                }
            }
        }
        
    }

}

for($i=0;$i<count($vote_arr);$i++){
?>
<a href="/online_vote_info.php?vt_id=<?php echo $vote_arr[$i]['vt_id']; ?>&vote=<?php echo $code; ?>&types=<?php echo $types; ?>" class="content_box">
    <div class="content_box_icons">
        <img src="/images/vote_icon_<?php echo $code == 'prg' ? 'on' : 'off'; ?>.svg" alt="">
    </div>
    <div class="content_box_ct">
        <div class="content_box_ct1">
            <?php
            if($vote_arr[$i]['vt_period_type'] == 'period'){
            ?>
            투표 기간 : <?php echo date("Y.m.d", strtotime($vote_arr[$i]['vt_sdate'])) ?> ~ <?php echo date("Y.m.d", strtotime($vote_arr[$i]['vt_edate'])) ?>
            <?php }else{ 
                $gigan_t = "";
                switch($vote_arr[$i]['vt_period_type']){
                    case "personnel";
                        $gigan_t = "인원수 마감";
                        break;
                    case "period_not":
                        $gigan_t = "무기한";
                        break;
                }    
            ?>
            투표 기간 : <?php echo $gigan_t; ?>
            <?php }?>
        </div>
        <div class="content_box_ct2">
            <?php echo $vote_arr[$i]['vt_title']; ?>
        </div>
    </div>
</a>
<?php }?>
<?php if($i==0){?>
<div class="complain_empty"><?php echo $code == 'prg' ? '진행중인' : '종료된'; ?> 투표가 없습니다.</div>
<?php }?>
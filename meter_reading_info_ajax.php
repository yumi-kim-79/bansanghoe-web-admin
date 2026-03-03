<?php
require_once "./_common.php";


$meter_building = "SELECT * FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$mr_year}' and mr_month = '{$mr_month}' ";
if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $meter_building.'<br>';
    // exit;
}
$meter_building_row = sql_fetch($meter_building);



//$total_val = $type == 'electro' ? $meter_building_row['total_electro'] : $meter_building_row['total_water'];

$total_val = 0;
if($type == 'electro'){
    $total_val = $meter_building_row['total_electro'] != '' ? $meter_building_row['total_electro'] : 0;
}else{
    $total_val = $meter_building_row['total_water'] != '' ? $meter_building_row['total_water'] : 0;
}

$month_de = "전월";

$bf_year = $mr_month == 1 ? $mr_year - 1 : $mr_year;
$bf_month = $mr_month == 1 ? 12 : $mr_month - 1;


if($type == 'electro'){
    // $where_type = " and total_electro != '' ";
    $where_type = " and electro_date != '' ";
}else{
    // $where_type = " and total_water != '' ";
    $where_type = " and water_date != '' ";
}

// echo $where_type.'<br>';
// exit;
// $where_type = '';
$bf_meter_sql = "SELECT COUNT(*) as cnt FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$bf_year}' and mr_month = '{$bf_month}' {$where_type}";
// echo $bf_meter_sql.'<br>';

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // echo $type.'<br>';
    // echo $bf_meter_sql.'<br>';
    // exit;
}

// exit;
$bf_meter_row = sql_fetch($bf_meter_sql);

if($bf_meter_row['cnt'] > 0){

    //전월 값 가져오기
    $bf_meter_sql_total = "SELECT * FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$bf_year}' and mr_month = '{$bf_month}' {$where_type} ";

    // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $bf_meter_sql_total;
    $bf_meter_total_row = sql_fetch($bf_meter_sql_total);

    // echo $type;
    // print_r2($bf_meter_total_row);
    // exit;

    // echo $type;

    $bf_val = 0;

    if($type == 'electro'){
        $bf_val = $bf_meter_total_row['total_electro'] != '' ? $bf_meter_total_row['total_electro'] : 0;
    }else{
        $bf_val = $bf_meter_total_row['total_water'] != '' ? $bf_meter_total_row['total_water'] : 0;
    }

    // echo $bf_val;
    // $bf_val = $type == 'electro' ? $bf_meter_total_row['total_electro'] : $bf_meter_total_row['total_water'];

}else{
    //익월
    $month_de = "익월";

    

    //익월로 계산
    $bf_month = $mr_month == 1 ? 11 : $mr_month - 2;

    $bf_meter_sql2 = "SELECT COUNT(*) as cnt FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$bf_year}' and mr_month = '{$bf_month}' {$where_type} ";

    // echo $bf_meter_sql2.'<br>';
    // exit;
    $bf_meter_row2 = sql_fetch($bf_meter_sql2);

    // print_r2($bf_meter_row2);

    $bf_val = 0;

    if($bf_meter_row2['cnt'] > 0){

        //익월의 값을 가져옵니다.
        $bf_meter_sql_total = "SELECT * FROM a_meter_building WHERE building_id = '{$building_id}' and mr_year = '{$bf_year}' and mr_month = '{$bf_month}' {$where_type} ";
        // echo $bf_meter_sql_total.'<br>';
        $bf_meter_total_row = sql_fetch($bf_meter_sql_total);
        
        if($type == 'electro'){
            if($bf_meter_total_row['total_electro'] == ''){
                $bf_val = 0;
            }else{
                $bf_val = $bf_meter_total_row['total_electro'];
            }
        }else{
            if($bf_meter_total_row['total_water'] == ''){
                $bf_val = 0;
            }else{
                $bf_val = $bf_meter_total_row['total_water'];
            }
        }

        // $bf_val = $type == 'electro' ? $bf_meter_total_row['total_electro'] : $bf_meter_total_row['total_water'];
    }else{
        $bf_month = $mr_month == 1 ? 12 : $mr_month - 1;

        $month_de = "전월";

        $bf_val = 0;
    }
}

// echo $bf_val;
// exit;

if($dong_id != ""){
    $sql_dong = " and dong.dong_id = '{$dong_id}' ";
}

$ho_sql = "SELECT ho.*, dong.dong_id, dong.dong_name FROM a_building_ho as ho
            LEFT JOIN a_building_dong as dong ON ho.dong_id = dong.dong_id
            WHERE ho.is_del = 0 and ho.building_id = '{$building_id}' {$sql_dong} ORDER BY ho.dong_id + 1 asc, ho.ho_name + 1 asc, ho.ho_id asc";
// echo $ho_sql;
// exit;

$ho_res = sql_query($ho_sql);
?>
<form name="meter_frm" id="meter_frm" method="post" autocomplete="off">
    <input type="hidden" name="mr_id" value="<?php echo $meter_building_row['mr_idx']; ?>">
    <input type="hidden" name="building_id" value="<?php echo $building_id; ?>">
    <input type="hidden" name="mr_year" value="<?php echo $mr_year; ?>">
    <input type="hidden" name="mr_month" value="<?php echo $mr_month; ?>">
    <input type="hidden" name="mr_department" value="<?php echo $mr_department; ?>">
    <input type="hidden" name="wid" value="<?php echo $wid; ?>">
    <div class="meter_table_box">
        <div class="meter_table_label">메인검침</div>
        <div class="meter_tables_wrap mgt10">
            <div class="meter_tables">
                <!-- <div class="meter_tables_th"></div> -->
                <div class="meter_tables_th_right full full1">
                    <div class="meter_table_th_rbox"><?php echo $month_de; ?>(<?php echo $bf_month.'월'; ?>) 검침값</div>
                    <div class="meter_table_th_rbox">당월 검침값</div>
                    <div class="meter_table_th_rbox">사용량(<?php echo $type == 'electro' ? 'kWh' : 't';?>)</div>
                </div>
            </div>
            <div class="meter_tables">
                <!-- <div class="meter_tables_th ver2">1001</div> -->
                <div class="meter_tables_th_right full full2 ver2">
                    <div class="meter_table_th_rbox"><?php echo $bf_val; ?></div>
                    <div class="meter_table_th_rbox">
                        <input type="tel" name="total_val" class="meter_form" value="<?php echo $total_val; ?>">
                    </div>
                    <?php
                    $use_val = $total_val - $bf_val;
                    
                    if($type == 'electro'){
                        if($use_val >= 400 || $use_val < 0){
                            $use_val = '<span class="red">'.$use_val.'</span>';
                        }
                    }else{
                        if($use_val >= 50 || $use_val < 0){
                            $use_val = '<span class="red">'.$use_val.'</span>';
                        }
                    }
                    ?>
                    <div class="meter_table_th_rbox">
                        <?php 
                    
                        echo $use_val;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="meter_table_box">
        <div class="meter_table_label">검침날짜</div>
        <div class="date_form_box mgt10">
            <input type="hidden" name="mr_type" id="mr_type" value="<?php echo $type; ?>">
            <input type="tel" name="meter_date" id="meter_date" value="<?php echo $type == 'electro' ? $meter_building_row['electro_date'] : $meter_building_row['water_date']; ?>" class="bansang_ipt ver2 ipt_date" readonly>
        </div>
        <div class="meter_tables_wrap mgt10">
            <div class="meter_tables">
                <div class="meter_tables_th">동</div>
                <div class="meter_tables_th ver3">호</div>
                <div class="meter_tables_th_right">
                    <div class="meter_table_th_rbox"><?php echo $month_de; ?>(<?php echo $bf_month.'월'; ?>) 검침값</div>
                    <div class="meter_table_th_rbox">당월 검침값</div>
                    <div class="meter_table_th_rbox">사용량(<?php echo $type == 'electro' ? 'kWh' : 't';?>)</div>
                </div>
            </div>
            <?php foreach($ho_res as $idx => $hrow){

                //전월/익월
                $bf_sql = "SELECT mr.*, mb.mr_year, mb.mr_month FROM a_meter_reading as mr
                            LEFT JOIN a_meter_building as mb on mr.mr_idx = mb.mr_idx
                            WHERE mr.building_id = '{$building_id}' and mr.mr_type = '{$type}' and mr.ho_id = '{$hrow['ho_id']}' and mb.mr_year = '{$bf_year}' and mb.mr_month = '{$bf_month}' ";
                // if($_SERVER['REMOTE_ADDR'] == ADMIN_IP) echo $bf_sql.'<br>';
                $bf_val_row = sql_fetch($bf_sql);

                $bf_val = $bf_val_row['mr_val'] == '' ? 0 : $bf_val_row['mr_val'];
                
                //당월 검침값
                $mr_val_sql = "SELECT * FROM a_meter_reading WHERE ho_id = '{$hrow['ho_id']}' and mr_idx = '{$meter_building_row['mr_idx']}' and mr_type = '{$type}'";
                $mr_val_row2 = sql_fetch($mr_val_sql);

                // echo $mr_val_sql.'<br>';
            
                $mr_val = 0;
                $mr_val = $mr_val_row2['mr_val'] != '' ? $mr_val_row2['mr_val'] : 0;

                $use_vals = $mr_val - $bf_val;
                // echo json_encode(['use_vals' => $use_vals1]);
                if($type == 'electro'){
                    if($use_vals >= 400 || $use_vals < 0){
                        $calsses = 'reds';
                    }else{
                        $calsses = '';
                    }
                }else{
                    if($use_vals >= 50 || $use_vals < 0){
                        $calsses = 'reds';
                    }else{
                        $calsses = '';
                    }
                }
            ?>
            <div class="meter_tables">
                <div class="meter_tables_th ver2">
                    <input type="hidden" name="dong_id[]" value="<?php echo $hrow['dong_id']; ?>">
                    <?php echo $hrow['dong_name'].'동'; ?>
                </div>
                <div class="meter_tables_th ver3 ver2">
                    <input type="hidden" name="ho_id[]" value="<?php echo $hrow['ho_id']; ?>">
                    <?php echo $hrow['ho_name'].'호'; ?>
                </div>
                <div class="meter_tables_th_right ver2">
                    <div class="meter_table_th_rbox"><?php echo $bf_val; ?></div>
                    <div class="meter_table_th_rbox">
                        <input type="hidden" name="mr_idx[]" value="<?php echo $mr_val_row2['mr_id']; ?>">
                        <input type="tel" name="mr_val[]" class="meter_form" value="<?php echo $mr_val == "0" ? "" : $mr_val; ?>">
                    </div>
                    <div class="meter_table_th_rbox <?php echo $calsses; ?>"><?php echo $use_vals; ?></div>
                </div>
            </div>
            <?php }?>
        </div>
    </div>
    <div class="meter_info_text mgt10">당월 사용량 - 전월 사용량 = 사용량</div>
</form>
<script>
$(function(){
    //minDate:"-365d"
    $("#meter_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "0d" });
});

</script>
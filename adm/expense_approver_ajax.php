<?php
require_once './_common.php';


if($dong_id != '-1' && $dong_id != ''){
    $dong_sql = "and mng_t.dong_id = '{$dong_id}'";
}

//관리단
$mng_sql = "SELECT mng_t.*, mng_g.gr_name, dong.dong_name, ho.ho_name FROM a_mng_team as mng_t
            LEFT JOIN a_mng_team_grade as mng_g on mng_t.mt_grade = mng_g.gr_id
            LEFT JOIN a_building_dong as dong on mng_t.dong_id = dong.dong_id
            LEFT JOIN a_building_ho as ho on mng_t.ho_id = ho.ho_id
            WHERE mng_t.post_id = '{$post_id}' and mng_t.build_id = '{$build_id}' {$dong_sql} and mng_t.is_del = 0 ORDER BY dong_name + 1 asc, ho_name + 1 asc, mt_name asc, mt_id asc ";

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $mng_sql.'<br>';
}
//exit;
$mng_res = sql_query($mng_sql);
?>
<option value="">관리단 선택</option>
<?php for($i=0;$mng_row = sql_fetch_array($mng_res);$i++){
    
    $mng_name = $mng_row['mt_type'] == 'OUT' ? '외부인 '.$mng_row['mt_name'].' '.$mng_row['gr_name'] : $mng_row['dong_name'].'동 '.$mng_row['ho_name'].'호 '.$mng_row['mt_name'].' '.$mng_row['gr_name'];
?>
    <option value="<?php echo $mng_row['mb_id']; ?>"><?php echo $mng_name; ?></option>
<?php }?>
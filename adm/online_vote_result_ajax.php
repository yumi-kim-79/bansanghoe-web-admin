<?php
require_once "./_common.php";

$vt_result_list = "SELECT ovr.*, ho.ho_name, dong.dong_name, mem.mb_name FROM a_online_vote_result as ovr
                    LEFT JOIN a_building_ho as ho ON ovr.ho_id = ho.ho_id
                    LEFT JOIN a_building_dong as dong ON ho.dong_id = dong.dong_id
                    LEFT JOIN a_member as mem ON ovr.mb_id = mem.mb_id
                   WHERE ovr.vtq_id = '{$vtq_id}' ORDER BY ovr.vtr_idx DESC";
// echo $vt_result_list;
$vt_result_res = sql_query($vt_result_list);

$vt_result_total = sql_num_rows($vt_result_res);
?>
<div class="vote_result_select mgt20"><?php echo $idx + 1;?>번 선택지</div>
<div class="vote_result_pop_wrap mgt20">
    <div class="vote_result_pop_head">
        <div class="vote_pop_hd_label">동/호수</div>
        <div class="vote_pop_hd_label">투표자(세대주)</div>
    </div>
    <?php if($vt_result_total > 0){?>
        <?php while($vt_result_row = sql_fetch_array($vt_result_res)){?>
        <div class="vote_result_pop_body">
            <div class="vote_pop_bd_label1"><?php echo $vt_result_row['dong_name']; ?>동/<?php echo $vt_result_row['ho_name']; ?>호</div>
            <div class="vote_pop_bd_label2"><?php echo $vt_result_row['mb_name'];?></div>
        </div>
        <?php }?>
    <?php }else{ ?>
        <div class="vote_result_pop_body vote_result_empty">
            투표자가 없습니다.
        </div>
    <?php }?>
</div>
<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$vt_question = sql_fetch("SELECT * FROM a_online_vote_question WHERE vtq_id = '{$vtq_id}'");

$vt_result_list = "SELECT ovr.*, ho.ho_name, dong.dong_name, mem.mb_name FROM a_online_vote_result as ovr
                    LEFT JOIN a_building_ho as ho ON ovr.ho_id = ho.ho_id
                    LEFT JOIN a_building_dong as dong ON ho.dong_id = dong.dong_id
                    LEFT JOIN a_member as mem ON ovr.mb_id = mem.mb_id
                   WHERE ovr.vtq_id = '{$vtq_id}' ORDER BY ovr.vtr_idx DESC";
// echo $vt_result_list;
$vt_result_res = sql_query($vt_result_list);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="parking_sc parking_sc1">
            <div class="inner">
                <div class="select_answer"><?php echo $vt_question['vtq_name']; ?></div>
            </div>
        </div>
        <div class="inner">
            <div class="vote_res_wrap">
                <div class="vote_res_tit_box">
                    <div class="vote_res_tit_left">동/호수</div>
                    <div class="vote_res_tit_right">투표자(세대주)</div>
                </div>
                <div class="vote_res_bd_wrap mgt10">
                    <?php for($i=0;$vt_result_row = sql_fetch_array($vt_result_res);$i++){?>
                    <div class="vote_res_bd_box">
                        <div class="vote_res_bd_left"><?php echo $vt_result_row['dong_name']; ?>동/<?php echo $vt_result_row['ho_name']; ?>호</div>
                        <div class="vote_res_bd_right"><?php echo $vt_result_row['mb_name']; ?></div>
                    </div>
                    <?php }?>
                    <?php if($i==0){?>
                        <div class="faq_empty_box">투표자 리스트가 없습니다.</div>
                    <?php }?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include_once(G5_PATH.'/tail.php');
?>
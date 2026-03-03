<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');


$move_sql = "SELECT move_r.*, building.building_name, dong.dong_name, ho.ho_name, mem.mb_hp, mem.mb_name FROM a_move_request as move_r
             LEFT JOIN a_building as building ON move_r.building_id = building.building_id
             LEFT JOIN a_building_dong as dong ON move_r.dong_id = dong.dong_id
             LEFT JOIN a_building_ho as ho ON move_r.ho_id = ho.ho_id
             LEFT JOIN a_member as mem ON move_r.mb_id = mem.mb_id
             WHERE move_r.mv_idx = '{$mv_idx}' ORDER BY move_r.mv_date asc, move_r.mv_idx desc";
$move_row = sql_fetch($move_sql);
//print_r2($move_row);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="parking_sc parking_sc1">
            <div class="inner">
                <div class="move_content_box_wrap">
                    <div class="move_ct_box">
                        <div class="move_ct_label">신청자</div>
                        <div class="move_cts"><?php echo $move_row['mb_name']; ?></div>
                    </div>
                    <div class="move_ct_box">
                        <div class="move_ct_label">신청자 휴대폰 번호</div>
                        <div class="move_cts">
                            <a href="tel:<?php echo $move_row['mb_hp']; ?>">
                                <img src="/images/phone_icons_b.svg" alt="">
                                <?php echo $move_row['mb_hp']; ?>
                            </a>
                        </div>
                    </div>
                    <div class="move_ct_box">
                        <div class="move_ct_label">신청자 세대</div>
                        <div class="move_cts"><?php echo $move_row['building_name']; ?> <?php echo $move_row['dong_name']; ?> <?php echo $move_row['ho_name']; ?></div>
                    </div>
                    <div class="move_ct_box">
                        <div class="move_ct_label">신청 날짜</div>
                        <div class="move_cts"><?php echo $move_row['created_at']; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sm_move_wrapper">
            <div class="inner">
                <form action="">
                    <ul class="regi_list">
                        <li>
                            <p class="regi_list_title">이사 예정 날짜<?php if(!$w){?> <span>*</span><?php }?></p>
                            <div class="ipt_box">
                                <input type="text" name="mv_date" id="mv_date" class="bansang_ipt ipt_date ver2" readonly value="<?php echo $move_row['mv_date']; ?>">
                            </div>
                        </li>
                        <li>
                            <p class="regi_list_title">이사 시작 시간<?php if(!$w){?> <span>*</span><?php }?></p>
                            <div class="ipt_box ipt_flex ipt_box_ver2">
                                <!-- <select name="move_time" id="move_time" class="bansang_sel">
                                    <option value="">시 선택</option>
                                    <?php for($i=1;$i<24;$i++){
                                        $time = $i < 10 ? '0'.$i : $i;
                                        ?>
                                        <option value="<?php echo $time; ?>" <?php echo get_selected($move_row['move_time'], $time);?>><?php echo $time.'시'; ?></option>
                                    <?php }?>
                                </select> -->
                                <input type="text" class="bansang_ipt ver2" value="<?php echo $move_row['move_time']; ?>시" readonly>
                                <span>:</span>
                                <!-- <select name="move_min" id="move_min" class="bansang_sel">
                                    <option value="">분 선택</option>
                                    <?php for($i=0;$i<60;$i+=5){
                                        $min = $i < 10 ? '0'.$i : $i;
                                        ?>
                                        <option value="<?php echo $min; ?>" <?php echo get_selected($move_row['move_min'], $min);?>><?php echo $min.'분'; ?></option>
                                    <?php }?>
                                </select> -->
                                <input type="text" class="bansang_ipt ver2" value="<?php echo $move_row['move_min']; ?>분" readonly>
                            </div>
                        </li>
                        <li>
                            <p class="regi_list_title">부동산 <?php if(!$w){?> <span>*</span><?php }?></p>
                            <div class="ipt_box">
                                <input type="text" name="mv_estate_name" id="mv_estate_name" class="bansang_ipt ver2" placeholder="부동산 이름을 입력해 주세요." value="<?php echo $move_row['mv_estate_name']; ?>" readonly>
                            </div>
                        </li>
                        <li>
                            <p class="regi_list_title">부동산 연락처 <?php if(!$w){?> <span>*</span><?php }?></p>
                            <div class="ipt_box">
                                <input type="tel" name="mv_estate_number" id="mv_estate_number" class="bansang_ipt ver2" placeholder="부동산 연락처를 입력해 주세요." value="<?php echo $move_row['mv_estate_number']; ?>" readonly>
                            </div>
                        </li>
                        <li>
                            <p class="regi_list_title">메모</p>
                            <div class="ipt_box">
                                <textarea type="tel" name="mv_memo" id="mv_memo" class="bansang_ipt ta ver2" placeholder="기타 메모할 것을 입력해 주세요." readonly><?php echo $move_row['mv_memo']; ?></textarea>
                            </div>
                        </li>
                    </ul>
                    
                </form>
            </div>
        </div>
    </div>
</div>
<script>
$(function(){
    $("#move_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d" });
});
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>
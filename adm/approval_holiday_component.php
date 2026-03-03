<?php
require_once './_common.php';

?>
<div class="paid_holiday_request_wrapper">
    <div class="paid_holiday_request_wrap">
        <input type="hidden" name="hp_idx[]" value="<?php echo $hp_row['hp_idx']; ?>">
        <div class="paid_holiday_request_box">
            <div class="paid_holiday_info_box_wrap flex_ver3">
                <div class="paid_holiday_info_box">
                    <div class="paid_holiday_info_label">이름</div>
                    <div class="paid_holiday_info_ipt">
                        <input type="text" name="hp_name[]" class="bansang_ipt ver2" value="<?php echo $hp_row['hp_name']; ?>">
                    </div>
                </div>
                <div class="paid_holiday_info_box">
                    <div class="paid_holiday_info_label">사용일수</div>
                    <div class="paid_holiday_info_ipt">
                        <select name="hp_day[]" class="bansang_sel">
                            <option value="1">1일</option>
                            <option value="2">2일</option>
                            <option value="3">3일</option>
                            <option value="4">4일</option>
                            <option value="5">5일</option>
                            <option value="am_half">오전반차</option>
                            <option value="pm_half">오후반차</option>
                            <option value="halfhalf">반반차</option>
                        </select>
                    </div>
                </div>
                <div class="paid_holiday_info_box">
                    <div class="paid_holiday_info_label">사용일자</div>
                    <div class="paid_holiday_info_ipt">
                        <input type="text" name="hp_date[]" class="bansang_ipt ver2 ipt_date" value="">
                    </div>
                </div>
            </div>
            <div class="paid_holiday_info_box_wrap mgt15">
                <div class="paid_holiday_info_box21">
                    <div class="paid_holiday_info_label ver2">비고</div>
                    <div class="paid_holiday_info_ipt ver2 mgt10">
                        <textarea name="hp_memo[]" id="hp_memo" class="bansang_ipt ver2 full ta"></textarea>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    <button type="button" onclick="paid_holiday_remove(this)" class="btn btn_01">삭제</button>
</div>
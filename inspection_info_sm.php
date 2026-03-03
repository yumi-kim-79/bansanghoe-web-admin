<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$inspection_sql = "SELECT insp.*, indus.industry_name, indus.indutry_icon FROM a_inspection as insp
                   LEFT JOIN a_industry_list as indus on insp.inspection_category = indus.industry_idx
                   WHERE insp.is_del = 0 and insp.inspection_idx = {$inspection_idx} ";
//echo $inspection_sql;
$row = sql_fetch($inspection_sql);

$inspection_file = "SELECT * FROM g5_board_file WHERE bo_table = 'inspection' and wr_id = {$inspection_idx} ORDER BY bf_no asc";
$files_res = sql_query($inspection_file);

switch($row['inspection_status']){
    case "N":
        $status = "승인대기";
        break;
    case "Y":
        $status = "승인";
        break;
    case "R":
        $status = "재점검";
        break;
    case "H":
        $status = "보류";
        break;
}
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="bbs_wrap">
                <div class="bbs_title_box">
                    <p class="bbs_title"><?php echo $row['inspection_title']; ?></p>
                    <p class="bbs_date"><span><?php echo $status; ?></span> <?php echo date('Y-m-d', strtotime($row['created_at'])); ?></p>
                </div>
                <div class="bbs_content_inspection mgt15">
                    <p>일지 작성자 : <?php echo $row['inspection_name']; ?></p>
                    <p>
                        작성자 연락처 : <?php echo $row['inspection_hp']; ?>
                        <a href="tel:<?php echo $row['inspection_hp']; ?>">
                            <img src="/images/phone_icons.svg" alt="">
                            전화걸기
                        </a>
                    </p>
                </div>
                <div class="bbs_content_box inspection_content_box mgt20">
                    <div class="swiper insp_swp">
                        <div class="swiper-wrapper">
                            <?php for($i=0;$file_row = sql_fetch_array($files_res);$i++){?>
                            <div class="swiper-slide">
                                <div onclick="imgZoom('/data/file/inspection/<?php echo $file_row['bf_file'];?>')">
                                    <img src="/data/file/inspection/<?php echo $file_row['bf_file'];?>" alt="">
                                </div>
                            </div>
                            <?php }?>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
                <script>
                    function imgZoom(imgPath){
                        sendMessage('imgZoom', {"content":imgPath});
                    }
                </script>
                <div class="bbs_memos mgt30">
                    <p class="memo_label">특이사항</p>
                    <textarea name="inspection_memo" id="inspenction_memo" class="bansang_ipt ver2 ta" readonly><?php echo $row['inspection_memo']; ?></textarea>
                </div>
            </div>
        </div>
        <?php if($row['inspection_status'] != 'Y'){ ?>
        <div class="fix_btn_back_box"></div>
        <div class="fix_btn_box flex_ver ver3">
            <?php if($row['inspection_status'] != 'R'){ ?>
                <button type="button" onclick="popOpen('log_reject_pop')" class="fix_btn on2" id="fix_btn" >재요청</button>
            <?php }?>
            <?php if($row['inspection_status'] != 'H'){ ?>
            <button type="button" onclick="popOpen('log_hold_pop')" class="fix_btn" id="fix_btn" >보류</button>
            <?php }?>
            <button type="button" onclick="popOpen('log_confirm_pop')" class="fix_btn on" id="fix_btn" >승인</button>
        </div>
        <?php }?>
    </div>
</div>
<div class="cm_pop" id="log_confirm_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <p class="cm_pop_desc2">승인하기</p>
        <p class="cm_pop_desc4">일지를 승인하시겠습니까?</p>
        <div class="cm_pop_btn_box flex_ver">
            <button type="button" class="cm_pop_btn" onClick="popClose('log_confirm_pop');">취소</button>
			<button type="button" class="cm_pop_btn ver2" onClick="status_change('log_confirm_pop', 'Y');">확인</button>
		</div>
    </div>
</div>
<div class="cm_pop" id="log_reject_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <p class="cm_pop_desc2">재요청</p>
        <p class="cm_pop_desc4">재요청 하시겠습니까?</p>
        <p class="cm_pop_desc4">작성자에게 재요청 연락 하시기 바랍니다.</p>
        <div class="cm_pop_btn_box flex_ver">
            <button type="button" class="cm_pop_btn" onClick="popClose('log_reject_pop');">취소</button>
			<button type="button" class="cm_pop_btn ver2" onClick="status_change('log_reject_pop', 'R');">확인</button>
		</div>
    </div>
</div>
<div class="cm_pop" id="log_hold_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <p class="cm_pop_desc2">보류</p>
        <p class="cm_pop_desc4">보류 하시겠습니까?</p>
        <p class="cm_pop_desc4">입주민에게 해당 점검일지는 보여지지 않습니다.</p>
        <div class="cm_pop_btn_box flex_ver">
            <button type="button" class="cm_pop_btn" onClick="popClose('log_hold_pop');">취소</button>
			<button type="button" class="cm_pop_btn ver2" onClick="status_change('log_hold_pop', 'H');">확인</button>
		</div>
    </div>
</div>
<script>
let swiper = new Swiper(".insp_swp", {
    slidesPerView: "auto",
    pagination: {
        el: '.swiper-pagination',
        type: 'fraction',
    },
    autoHeight: true,
});

//상태변경
function status_change(pop, status){

    let sendData = {'inspection_idx': '<?php echo $inspection_idx; ?>', 'inspection_status':status};

    $.ajax({
        type: "POST",
        url: "/adm/inspection_status_change_ajax.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                showToast(data.msg);
                return false;
            }else{
                showToast(data.msg);

                popClose(pop);

                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        },
    });
    
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>
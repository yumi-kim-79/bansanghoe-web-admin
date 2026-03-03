<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');

$expense_sql = "SELECT * FROM a_expense_report WHERE ex_id = '{$ex_id}'";
$expense_row = sql_fetch($expense_sql);

$ex_approver1 = get_mng_team($expense_row['ex_approver1']);
$ex_approver2 = get_mng_team($expense_row['ex_approver2']);
$ex_approver3 = get_mng_team($expense_row['ex_approver3']);

$expense_file = "SELECT * FROM g5_board_file WHERE bo_table = 'expense' and wr_id = '{$ex_id}' ORDER BY bf_no asc ";
//echo $expense_file;
$expense_file_res = sql_query($expense_file); 


if($expense_row['building_id'] != $user_building['building_id']){
    alert('삭제되거나 변경된 품의서입니다.');
}


if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // print_r2($expense_row);
    // print_r2($user_building);
}
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="bbs_wrap">
                <div class="bbs_title_box">
                    <p class="bbs_title"><?php echo $expense_row['ex_title']; ?></p>
                </div>
                <div class="expense_img_box">
                    <?php echo $expense_row['ex_content']; ?>
                    <div class="expense_img_box_inner mgt10">
                        <div class="swiper expense_swp">
                            <div class="swiper-wrapper">
                                <?php for($i=0;$file_row = sql_fetch_array($expense_file_res);$i++){?>
                                <div class="swiper-slide">
                                    <div onclick="imgZoom('/data/file/expense/<?php echo $file_row['bf_file'];?>')">
                                        <img src="/data/file/expense/<?php echo $file_row['bf_file'];?>" alt="">
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
                </div>
            </div>
        </div>
        <input type="hidden" name="sign_boxs_img1_t" id="sign_boxs_img1_t">
        <input type="hidden" name="sign_boxs_img2_t" id="sign_boxs_img2_t">
        <input type="hidden" name="sign_boxs_img3_t" id="sign_boxs_img3_t">
        <div class="sign_wrap">
            <div class="inner">
                <p class="sign_titles">
                    <img src="/images/icon_chk_on2.svg" alt="">
                    승인 결재
                </p>
                <div class="sign_boxs_wrap">
                    <?php
                    //내 사인 있는지 확인
                    // $signature_check = "SELECT *, COUNT(*) as cnt FROM a_signature WHERE mb_id = '{$user_info['mb_id']}' ORDER BY sg_idx DESC LIMIT 0, 1";
                    
                    $signature_check = "SELECT s.*, t.cnt
                                        FROM (
                                            SELECT * 
                                            FROM a_signature 
                                            WHERE mb_id = '{$user_info['mb_id']}' 
                                            ORDER BY sg_idx DESC 
                                            LIMIT 1
                                        ) s
                                        JOIN (
                                            SELECT COUNT(*) AS cnt 
                                            FROM a_signature 
                                            WHERE mb_id = '{$user_info['mb_id']}'
                                        ) t ON 1";

                    // echo $signature_check;

                    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
                        // echo $signature_check.'<br>';
                    }
                    $signature_check_row = sql_fetch($signature_check);


                    if($ex_approver1['mt_type'] == 'OUT'){
                        $approval1_info = "외부인 (".$ex_approver1['mt_name'].") - ".$ex_approver1['gr_name'];
                    }else{
                        $approval1_info = $ex_approver1['dong_name']."동 ".$ex_approver1['ho_name']."호 (".$ex_approver1['mt_name'].") - ".$ex_approver1['gr_name'];
                    }
                    // print_r2($ex_approver1);
                    ?>
                    <div class="sign_boxs">
                        <div class="sign_label_box">
                            <p class="sign_label">
                            최초 결재자 <?php echo $approval1_info;?>
                            </p>
                            <?php if(!$expense_row['ex_apprval1_chk'] && $expense_row['ex_approver1'] == $user_info['mb_id']){?>
                                <?php if($signature_check_row['cnt'] > 0){?>
                                    <div class="sign_btn_wrap">
                                        <button type="button" onclick="signHandler('sign_boxs_img1', 'apprval1')" class="sign_buttons mgr10">
                                            <img src="/images/icon_chk_white.svg" alt="">
                                            다시 서명하기
                                        </button>
                                        <button type="button" onclick="signLoad('<?php echo $user_info['mb_id']; ?>', 'sign_boxs_img1', 'apprval1')" class="sign_buttons">
                                            <img src="/images/icon_chk_white.svg" alt="">
                                            서명 불러오기
                                        </button>
                                    </div>
                                <?php }else{?>
                                <button type="button" onclick="signHandler('sign_boxs_img1', 'apprval1')" class="sign_buttons">
                                    <img src="/images/icon_chk_white.svg" alt="">
                                    서명하기
                                </button>
                                <?php }?>
                            <?php }?>
                        </div>
                        <div class="sign_boxs_img sign_boxs_img1">
                            <?php if($expense_row['ex_apprval1_chk']){ 
                                $sign_img_sql = "SELECT * FROM a_expense_report_sign WHERE ex_id = '{$ex_id}' and apprval_type = 'apprval1'";
                                $sign_img_row = sql_fetch($sign_img_sql);
                            ?>
                                <?php if($sign_img_row){?>
                                    <img src="/data/file/approval_expense/<?php echo $sign_img_row['sign_img']; ?>" alt="">
                                <?php }else{ ?>
                                    <?php if($expense_row['ex_status_d'] == 'Y'){?>
                                        <p class="direct_sign">서면 서명 완료</p>
                                    <?php }?>
                                <?php }?>
                            <?php }?>
                        </div>
                    </div>
                    <?php if($expense_row['ex_approver2'] != ""){
                    
                    if($ex_approver2['mt_type'] == 'OUT'){
                        $approval2_info = "외부인 (".$ex_approver2['mt_name'].") - ".$ex_approver2['gr_name'];
                    }else{
                        $approval2_info = $ex_approver2['dong_name']."동 ".$ex_approver2['ho_name']."호 (".$ex_approver2['mt_name'].") - ".$ex_approver2['gr_name'];
                    }
                    ?>
                    <div class="sign_boxs">
                        <div class="sign_label_box">
                            <p class="sign_label">
                                중간 결재자 <?php echo $approval2_info;?>
                            </p>
                            <?php if($expense_row['ex_apprval1_chk'] && !$expense_row['ex_apprval2_chk'] && $expense_row['ex_approver2'] == $user_info['mb_id']){?>
                                <?php if($signature_check_row['cnt'] > 0){?>
                                <div class="sign_btn_wrap">
                                    <button type="button" onclick="signHandler('sign_boxs_img2', 'apprval2')" class="sign_buttons mgr10">
                                        <img src="/images/icon_chk_white.svg" alt="">
                                        다시 서명하기
                                    </button>
                                    <button type="button" onclick="signLoad('<?php echo $user_info['mb_id']; ?>', 'sign_boxs_img2', 'apprval2')" class="sign_buttons">
                                        <img src="/images/icon_chk_white.svg" alt="">
                                        서명 불러오기
                                    </button>
                                </div>
                                <?php }else{ ?>
                                <button type="button" onclick="signHandler('sign_boxs_img2', 'apprval2')" class="sign_buttons">
                                    <img src="/images/icon_chk_white.svg" alt="">
                                    서명하기
                                </button>
                                <?php }?>
                            <?php }?>
                        </div>
                        <div class="sign_boxs_img sign_boxs_img2">
                            <?php if($expense_row['ex_apprval2_chk']){ 
                                $sign_img_sql2 = "SELECT * FROM a_expense_report_sign WHERE ex_id = '{$ex_id}' and apprval_type = 'apprval2'";
                                $sign_img_row2 = sql_fetch($sign_img_sql2);
                            ?>
                                <?php if($sign_img_row2){?>
                                <img src="/data/file/approval_expense/<?php echo $sign_img_row2['sign_img']; ?>" alt="">
                                <?php }else{ ?>
                                    <?php if($expense_row['ex_status_d'] == 'Y'){?>
                                        <p class="direct_sign">서면 서명 완료</p>
                                    <?php }?>
                                <?php }?>
                            <?php }?>
                        </div>
                    </div>
                    <?php }?>
                    <?php if($expense_row['ex_approver3'] != ""){

                    if($ex_approver3['mt_type'] == 'OUT'){
                        $approval3_info = "외부인 (".$ex_approver3['mt_name'].") - ".$ex_approver3['gr_name'];
                    }else{
                        $approval3_info = $ex_approver3['dong_name']."동 ".$ex_approver3['ho_name']."호 (".$ex_approver3['mt_name'].") - ".$ex_approver3['gr_name'];
                    }    
                    ?>
                    <div class="sign_boxs">
                        <div class="sign_label_box">
                            <p class="sign_label">
                            최종 결재자 <?php echo $approval3_info;?>
                            </p>
                            <?php if($expense_row['ex_apprval2_chk'] && !$expense_row['ex_apprval3_chk'] && $expense_row['ex_approver3'] == $user_info['mb_id']){?>
                                <?php if($signature_check_row['cnt'] > 0){?>
                                <div class="sign_btn_wrap">
                                    <button type="button" onclick="signHandler('sign_boxs_img3', 'apprval3')" class="sign_buttons mgr10">
                                        <img src="/images/icon_chk_white.svg" alt="">
                                        다시 서명하기
                                    </button>
                                    <button type="button" onclick="signLoad('<?php echo $user_info['mb_id']; ?>', 'sign_boxs_img3', 'apprval3')" class="sign_buttons">
                                        <img src="/images/icon_chk_white.svg" alt="">
                                        서명 불러오기
                                    </button>
                                </div>
                                <?php }else{?>
                                <button type="button" onclick="signHandler('sign_boxs_img3', 'apprval3')" class="sign_buttons">
                                    <img src="/images/icon_chk_white.svg" alt="">
                                    서명하기
                                </button>
                                <?php }?>
                            <?php }?>
                        </div>
                        <div class="sign_boxs_img sign_boxs_img3">
                            <?php if($expense_row['ex_apprval3_chk']){ 
                                $sign_img_sql3 = "SELECT * FROM a_expense_report_sign WHERE ex_id = '{$ex_id}' and apprval_type = 'apprval3'";
                                $sign_img_row3 = sql_fetch($sign_img_sql3);
                            ?>
                                <?php if($sign_img_row3){?>
                                    <img src="/data/file/approval_expense/<?php echo $sign_img_row3['sign_img']; ?>" alt="">
                                <?php }else{ ?>
                                    <?php if($expense_row['ex_status_d'] == 'Y'){?>
                                        <p class="direct_sign">서면 서명 완료</p>
                                    <?php }?>
                                <?php }?>
                            <?php }?>
                        </div>
                    </div>
                    <?php }?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="cm_pop" id="sign_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <p class="cm_pop_title">전자서명</p>
        <div class="cm_pop_desc">
            <canvas id="signatureCanvas" width="600" height="150"></canvas>
        </div>
		<div class="cm_pop_btn_box flex_ver flex_ver2">
			<button type="button" class="cm_pop_btn" onClick="popClose('sign_pop');">취소</button>
			<button type="button" class="cm_pop_btn ver3" onClick="clearSign();">다시입력</button>
			<button type="button" class="cm_pop_btn ver2" onClick="saveSign();">서명</button>
		</div>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>

let ele = "";
let approval_cont = "";
function signHandler(a, datas){
    ele = a;
    approval_cont = datas;

    popOpen('sign_pop');
    resizeCanvas();
}


// Get canvas and buttons
const canvas = document.getElementById('signatureCanvas');

// Initialize SignaturePad
const signaturePad = new SignaturePad(canvas);

function clearSign(){
    signaturePad.clear();
}

function saveSign(){
    if (signaturePad.isEmpty()) {
        showToast('서명을 입력해주세요.');
        return false;
    } else {
        const dataURL = signaturePad.toDataURL("image/png");
        let mb_id = "<?php echo $user_info['mb_id']; ?>";
        let ex_id = "<?php echo $ex_id; ?>";

        $("#" + ele + "_t").val(dataURL);

        let sendData = {"mb_id":mb_id, "ex_id":ex_id, "signdata": dataURL, "data":approval_cont};

        $.ajax({
            type: "POST",
            url: "/expense_report_adm_apprval_ajax.php",
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

                    clearSign();

                    popClose('sign_pop');

                    setTimeout(() => {
                        window.location.reload();
                    }, 300);
                
                }
            },
        });
    }
}

const ratio =  Math.max(window.devicePixelRatio || 1, 1);
canvas.width = canvas.offsetWidth * ratio;
canvas.height = canvas.offsetHeight * ratio;
canvas.getContext("2d").scale(ratio, ratio);

function resizeCanvas() {
    const ratio =  Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);
    //signaturePad.clear(); // otherwise isEmpty() might return incorrect value
}

window.addEventListener("resize", resizeCanvas);
resizeCanvas();


function signLoad(id, ele, approval_cont){
    let sendData = {'mb_id': id};

    $.ajax({
        type: "POST",
        url: "/sign_load_ajax.php",
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

                let imgSRc = "/data/file/approval_expense/" + data.data.fil_name;
                let imgs = `<img src='${imgSRc}' />`;
                $("." + ele).html(imgs);

                let ex_id = "<?php echo $ex_id; ?>";
                let sign_dataURL = "<?php echo $signature_check_row['signature_data']; ?>";
                let sendData2 = {"mb_id":id, "ex_id":ex_id, "signdata": sign_dataURL, "data":approval_cont};

                $.ajax({
                    type: "POST",
                    url: "/expense_report_adm_apprval_ajax.php",
                    data: sendData2,
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

                            setTimeout(() => {
                                window.location.reload();
                            }, 700);
                        
                        }
                    },
                });
            }
        }
    });
}

let swiper = new Swiper(".expense_swp", {
    slidesPerView: "auto",
    pagination: {
        el: '.swiper-pagination',
        type: 'fraction',
    },
    autoHeight: true,
});
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>
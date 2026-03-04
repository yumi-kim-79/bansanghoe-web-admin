<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$sign_sql = "SELECT sign_off.*, cate.sign_cate_name FROM a_sign_off as sign_off
            LEFT JOIN a_sign_off_category AS cate ON sign_off.sign_off_category = cate.sign_cate_code
            WHERE sign_off.is_del = 0 and sign_off.sign_id = '{$sign_id}' ORDER BY sign_id desc";
// echo $sign_sql;
$sign_row = sql_fetch($sign_sql);

$sign_off_mng_id1 = get_manger($sign_row['sign_off_mng_id1']);
$sign_off_mng_id2 = get_manger($sign_row['sign_off_mng_id2']);
$sign_off_mng_id3 = get_manger($sign_row['sign_off_mng_id3']);

$sample_sql = "SELECT * FROM a_sign_off_sample WHERE sign_id = '{$sign_id}'";
// echo $sample_sql;
$sample_row = sql_fetch($sample_sql);
//print_r2($sign_off_mng_id1);

//echo $mng;
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="bbs_wrap">
                <div class="bbs_title_box">
                    <p class="bbs_title"><?php echo $sign_row['sign_cate_name']; ?></p>
                </div>
                <div class="bbs_content_box sign_off_info">
                    <!-- <p class="sign_off_title"><?php echo $headerTitle; ?></p> -->
                    <?php if($sample_row){?>
                        <div onclick="imgZoom('/data/file/signOffSample/<?php echo $sample_row['sample_img']; ?>')">
                        <img src="/data/file/signOffSample/<?php echo $sample_row['sample_img']; ?>" alt="">
                        </div>
                    <?php }?>
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
                    // $signature_check = "SELECT *, COUNT(*) as cnt FROM a_signature WHERE mb_id = '{$member['mb_id']}'";

                    $signature_check = "SELECT s.*, t.cnt
                                        FROM (
                                            SELECT * 
                                            FROM a_signature 
                                            WHERE mb_id = '{$member['mb_id']}' 
                                            ORDER BY sg_idx DESC 
                                            LIMIT 1
                                        ) s
                                        JOIN (
                                            SELECT COUNT(*) AS cnt 
                                            FROM a_signature 
                                            WHERE mb_id = '{$member['mb_id']}'
                                        ) t ON 1";
                    $signature_check_row = sql_fetch($signature_check);
                    // print_r2($signature_check_row);

                    $reject_btn = '';
                    ?>
                    <div class="sign_boxs">
                        <div class="sign_label_box">
                            <p class="sign_label">
                                <?php echo $sign_off_mng_id1['mg_name']; ?><br />
                                <?php echo $sign_off_mng_id1['mng_name'];?>
                            </p>
                            <?php if($sign_row['sign_status'] == 'R' && $sign_row['sign_reject_id'] == $sign_row['sign_off_mng_id1']){?>
                                <p class="red">결재 반려</p>
                            <?php }else{?>
                                <?php if($mng == "Y" && !$sign_row['sign_off_status'] && $sign_row['sign_off_mng_id1'] == $member['mb_id']){
                                    
                                    //반려를 위한
                                    $reject_btn = $sign_row['sign_off_mng_id1'];

                                    ?>
                                    <?php if($signature_check_row['cnt'] > 0){?>
                                    <div class="sign_btn_wrap">
                                        <button type="button" onclick="signHandler('sign_boxs_img1', 'sign_off_mng_id1')" class="sign_buttons mgr10">
                                            <img src="/images/icon_chk_white.svg" alt="">
                                            다시 서명하기
                                        </button>
                                        <button type="button" onclick="signLoad('<?php echo $member['mb_id']; ?>', 'sign_boxs_img1', 'sign_off_mng_id1')" class="sign_buttons">
                                            <img src="/images/icon_chk_white.svg" alt="">
                                            서명 불러오기
                                        </button>
                                    </div>
                                    
                                    <?php }else{ ?>
                                    <button type="button" onclick="signHandler('sign_boxs_img1', 'sign_off_mng_id1')" class="sign_buttons">
                                        <img src="/images/icon_chk_white.svg" alt="">
                                        서명하기
                                    </button>
                                    <?php }?>
                                <?php }?>
                            <?php }?>
                        </div>
                        <?php
                        ?>
                        <div class="sign_boxs_img sign_boxs_img1">
                            <?php if($sign_row['sign_off_status']){ 
                                //서명이미지
                                $sql_sign_off_img = "SELECT soi.*, sig.fil_name FROM a_sign_off_mng_sign as soi
                                LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                                WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}' and sign_mng_data = 'sign_off_mng_id1'";
                                $sign_img_row = sql_fetch($sql_sign_off_img);
                            ?>
                                <img src="/data/file/approval/<?php echo $sign_img_row['fil_name']; ?>" alt="">
                            <?php }?>
                        </div>
                    </div>
                    <?php if($sign_row['sign_off_mng_id2'] != ""){?>
                    <div class="sign_boxs">
                        <div class="sign_label_box ver2">
                            <p class="sign_label">
                                <?php echo $sign_off_mng_id2['mg_name']; ?><br>
                                <?php echo $sign_off_mng_id2['mng_name'];?>
                            </p>
                            <?php if($sign_row['sign_status'] == 'R' && $sign_row['sign_reject_id'] == $sign_row['sign_off_mng_id2']){?>
                                <p class="red">결재 반려</p>
                            <?php }else{?>
                                <?php if($mng == "Y" && $sign_row['sign_off_status'] && !$sign_row['sign_off_status2'] && $sign_row['sign_off_mng_id2'] == $member['mb_id']){
                                    
                                    //반려를 위한
                                    $reject_btn = $sign_row['sign_off_mng_id2'];
                                    ?>
                                    <?php if($signature_check_row['cnt'] > 0){?>

                                        <div class="sign_btn_wrap">
                                            <button type="button" onclick="signHandler('sign_boxs_img2', 'sign_off_mng_id2')" class="sign_buttons mgr10">
                                                <img src="/images/icon_chk_white.svg" alt="">
                                                다시 서명하기
                                            </button>
                                            <button type="button" onclick="signLoad('<?php echo $member['mb_id']; ?>', 'sign_boxs_img2', 'sign_off_mng_id2')" class="sign_buttons">
                                            <img src="/images/icon_chk_white.svg" alt="">
                                                서명 불러오기
                                            </button>
                                        </div>
                                    <?php }else{ ?>
                                        <button type="button" onclick="signHandler('sign_boxs_img2', 'sign_off_mng_id2')" class="sign_buttons">
                                            <img src="/images/icon_chk_white.svg" alt="">
                                            서명하기
                                        </button>
                                    <?php }?>
                                <?php }?>
                            <?php }?>
                        </div>
                        <div class="sign_boxs_img sign_boxs_img2">
                            <?php if($sign_row['sign_off_status2']){ 
                               //서명이미지
                               $sql_sign_off_img2 = "SELECT soi.*, sig.fil_name FROM a_sign_off_mng_sign as soi
                               LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                               WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}' and sign_mng_data = 'sign_off_mng_id2'";
                               $sign_img_row2 = sql_fetch($sql_sign_off_img2);
                            ?>
                                <img src="/data/file/approval/<?php echo $sign_img_row2['fil_name']; ?>" alt="">
                            <?php }?>
                        </div>
                    </div>
                    <?php }?>
                    <?php if($sign_row['sign_off_mng_id3'] != ""){?>
                    <div class="sign_boxs">
                        <div class="sign_label_box">
                            <p class="sign_label">
                                <?php echo $sign_off_mng_id3['mg_name']; ?><br>
                                <?php echo $sign_off_mng_id3['mng_name'];?>
                            </p>
                            <?php if($sign_row['sign_status'] == 'R' && $sign_row['sign_reject_id'] == $sign_row['sign_off_mng_id3']){?>
                                <p class="red">결재 반려</p>
                            <?php }else{?>
                                <?php if($mng == "Y" && $sign_row['sign_off_status'] && $sign_row['sign_off_status2'] && !$sign_row['sign_off_status3'] && $sign_row['sign_off_mng_id3'] == $member['mb_id']){
                                    
                                    //반려를 위한
                                    $reject_btn = $sign_row['sign_off_mng_id3'];
                                    ?>
                                    <?php if($signature_check_row['cnt'] > 0){?>

                                        <div class="sign_btn_wrap">
                                            <button type="button" onclick="signHandler('sign_boxs_img3', 'sign_off_mng_id3')" class="sign_buttons mgr10">
                                                <img src="/images/icon_chk_white.svg" alt="">
                                                다시 서명하기
                                            </button>
                                            <button type="button" onclick="signLoad('<?php echo $member['mb_id']; ?>', 'sign_boxs_img3', 'sign_off_mng_id3')" class="sign_buttons">
                                            <img src="/images/icon_chk_white.svg" alt="">
                                                서명 불러오기
                                            </button>
                                        </div>
                                    <?php }else{?>
                                    <button type="button" onclick="signHandler('sign_boxs_img3', 'sign_off_mng_id3')" class="sign_buttons">
                                        <img src="/images/icon_chk_white.svg" alt="">
                                        서명하기
                                    </button>
                                <?php }?>
                                <?php }?>
                            <?php }?>
                        </div>
                        <div class="sign_boxs_img sign_boxs_img3">
                            <?php if($sign_row['sign_off_status3']){ 
                               //서명이미지
                               $sql_sign_off_img3 = "SELECT soi.*, sig.fil_name FROM a_sign_off_mng_sign as soi
                               LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                               WHERE soi.is_del = 0 and soi.sign_id = '{$sign_id}' and sign_mng_data = 'sign_off_mng_id3'";
                               $sign_img_row3 = sql_fetch($sql_sign_off_img3);
                            ?>
                                <img src="/data/file/approval/<?php echo $sign_img_row3['fil_name']; ?>" alt="">
                            <?php }?>
                        </div>
                    </div>
                    <?php }?>
                    <?php if($mng == "Y" && $sign_row['sign_status'] != "E" && $sign_row['sign_status'] != "R" && $reject_btn == $member['mb_id']){?>
                    <div class="approval_reject_wrap mgt30">
                        <button type="button" onclick="popOpen('approval_reject_pop');">반려하기</button>
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

<div class="cm_pop" id="approval_del_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_desc2"><?php echo $headerTitle; ?>를 삭제하시겠습니까?</p>
		<div class="cm_pop_btn_box flex_ver">
			<button type="button" class="cm_pop_btn" onClick="popClose('approval_del_pop');">취소</button>
            <button type="button" class="cm_pop_btn ver2" onClick="signDelHandler();">삭제</button>
		</div>
	</div>
</div>

<div class="cm_pop" id="approval_reject_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_desc2">해당 서류를 반려 처리하시겠습니까?</p>
		<div class="cm_pop_btn_box flex_ver">
			<button type="button" class="cm_pop_btn" onClick="popClose('approval_reject_pop');">취소</button>
            <button type="button" class="cm_pop_btn ver2" onClick="singReject();">확인</button>
		</div>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
//반려하기
function singReject(){
    // if (!confirm("해당 결재내역을 반려 처리 하시겠습니까?")) {
    //     return false;
    // }

    let sign_id = "<?php echo $sign_id; ?>"; 
    let mb_id = "<?php echo $member['mb_id']; ?>";

    let sendData = {'sign_id': sign_id, 'mb_id':mb_id};

    $.ajax({
        type: "POST",
        url: "/adm/approval_form_reject.php",
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

               setTimeout(() => {
                    window.location.reload();
                }, 700);
            }
        }
    });
}


function imgZoom(imgPath){
    sendMessage('imgZoom', {"content":imgPath});
}

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
        let sign_dataURL = "";
        resizeImage(dataURL, 200, function(resizedDataURL) {
            //$("#approval_signature").val(resizedDataURL);
            $("#" + ele + "_t").val(resizedDataURL);
            sign_dataURL = resizedDataURL;
            
            // let imgs = `<img src='${resizedDataURL}' />`;
            // $("." + ele).html(imgs);

            // popClose('sign_pop');

            console.log('1',sign_dataURL);

            let mb_id = "<?php echo $member['mb_id']; ?>";
            let sign_id = "<?php echo $sign_id; ?>";
            //let sign_dataURL = $("#" + ele + "_t").val();
            
            let sendData = {"mb_id":mb_id, "sign_id":sign_id, "signdata": sign_dataURL, "data":approval_cont};

            console.log('2',sendData);

            $.ajax({
                type: "POST",
                url: "/holiday_reqeust_info_sign_ajax.php",
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

                        let imgs = `<img src='${dataURL}' />`;
                        $("." + ele).html(imgs);

                        setTimeout(() => {
                            // window.location.reload();

                            location.replace("/holiday_request_sample.php?mem_type=sign_user2&sign_id=" + sign_id);
                        }, 700);

                    
                    }
                },
            });
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
                // showToast(data.msg);
                // $("#approval_signature").val(data.data.signature_data);
    
                let imgSRc = "/data/file/approval/" + data.data.fil_name;
                let imgs = `<img src='${imgSRc}' />`;
                $("." + ele).html(imgs);

                let sign_id = "<?php echo $sign_id; ?>";
                let sign_dataURL = "<?php echo $signature_check_row['signature_data']; ?>";
                let sendData2 = {"mb_id":id, "sign_id":sign_id, "signdata": sign_dataURL, "data":approval_cont};

                $.ajax({
                    type: "POST",
                    url: "/holiday_reqeust_info_sign_ajax.php",
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
                                // window.location.reload();

                                location.replace("/holiday_request_sample.php?mem_type=sign_user2&sign_id=" + sign_id);

                            }, 700);
                        
                        }
                    },
                });
            }
        },
    });
}

function signDelHandler(){

    let mb_id = "<?php echo $member['mb_id']; ?>";
    let sign_id = "<?php echo $sign_id; ?>";
    let types = "<?php echo $types; ?>";

    let sendData = {'mb_id': mb_id, 'sign_id':sign_id};

    $.ajax({
        type: "POST",
        url: "/holiday_reqeust_del_ajax.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                showToast(data.msg);

                popClose('approval_del_pop')
            
                return false;
            }else{
                showToast(data.msg);

                popClose('approval_del_pop')

                setTimeout(() => {

                    location.replace('/holiday_reqeust.php?types='+types);
                    // window.location.reload();
                }, 700);
            
            }
        },
    });
}

// 이미지를 리사이징하는 함수
function resizeImage(base64Str, newWidth, callback) {
    let img = new Image();
    img.src = base64Str;
    img.onload = function() {
        let canvas = document.createElement("canvas");
        let ctx = canvas.getContext("2d");

        let scale = newWidth / img.width;
        canvas.width = newWidth;
        canvas.height = img.height * scale; // 높이 비율 유지

        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        let resizedBase64 = canvas.toDataURL("image/png");

        callback(resizedBase64);
    };
}

//툴팁
let mng = "<?php echo $mng; ?>";

if(mng != "Y"){
    const homebtn = document.querySelector('.home_btn');
    const tooltipBox = document.querySelector('.tooltip_btn');
    homebtn.addEventListener('click', () => {
    const dropdown = document.querySelector('.tooltip_box');
    dropdown.style.display = 'block';
    });

    homebtn.addEventListener('blur', () => {
    const dropdown = document.querySelector('.tooltip_box');

    setTimeout(() => {
        dropdown.style.display = '';
    }, 200);
    });
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>

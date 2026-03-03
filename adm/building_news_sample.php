<?php
include_once('./_common.php');

//auth_check($auth[$sub_menu], "w");

$building_bbs = "SELECT * FROM a_building_bbs WHERE bb_id = '{$bb_idx}'";
$building_bbs_row = sql_fetch($building_bbs);

$building_info = get_builiding_info($building_bbs_row['building_id']);

// print_r2($building_info);

$g5['title'] = $building_bbs_row['bb_title'];
include_once(G5_PATH.'/head.sub.php');

$bbs_code_name = "";
$redirect_url = "";
switch($building_bbs_row['bbs_type']){
    case "infomation":
        $bbs_code_name = "안내문";
        $redirect_url = "building_news_info_form.php";
        break;
    case "public":
        $bbs_code_name = "공문";
        $redirect_url = "building_news_public_form.php";
        break;
    case "event":
        $bbs_code_name = "이벤트";
        $redirect_url = "building_news_event_form.php";
        break;
}
//echo $building_bbs;

//print_r2($building_bbs_row);
$bb_number = $building_bbs_row['bb_number'];
// $bb_number = implode(" ", $bb_number);
//print_r2($bb_number);


$edate_str = substr($building_bbs_row['edate'], 0, 4);
?>
<style>
.building_news_sample_wrap {position: relative;max-width:210mm;margin: 0 auto;}
.news_content {
    width: 210mm;
    height: 297mm;
    margin: auto;
    padding: 45mm 10mm;
    background: url('/images/building_news_sample.jpg') no-repeat center center;
    background-size: cover;
    box-sizing: border-box;
    overflow: hidden;
    font-size: 16px;
    font-weight: normal;
}
.news_content table {border-collapse: collapse;margin-bottom: 20px;}
.news_content table tr td {border:1.5px solid #000;padding:5px 3px;}

.news_content img {margin: 0 auto;display: block;}
/* .preset_info {padding: 41mm 5mm 34mm !important;} */

.building_news_sample_hd {width: 100%;padding:15px;display: flex;justify-content:flex-end;}
.building_news_sample_hd button {padding:10px 15px;border-radius:6px;border:none;background: var(--colorMain);color: #fff;font-size: 14px;}

.news_tit_box {position: absolute;top:93px;font-size: 40px;font-weight: 600;text-align: center;width: 100%;max-width: 210mm;left: 50%;transform:translateX(-50%);}

.new_info_hd {
    display: table;
    width: 100%;
    max-width: 210mm;
    position: absolute;
    top: 40px;
    left: 0;
    padding: 0 5mm;
    font-size: 12px;
    table-layout: fixed;
}

.news_number,
.news_number_r {
    display: table-cell;
    vertical-align: top;
    width: 50%;
}

.news_number span {
    display: block;
}

.news_number .news_number_box1 {
    /* replace justify with left */
    text-align: left;
}

.news_number span.fill {
    display: inline-block;
    width: 100%;
}

.news_number_r span {
    text-align: right;
}

.news_number_box2 {margin-top: 5px;}

.building_name {position: absolute;bottom:60px;font-size: 24px;font-weight: 500;width: 100%;max-width: 210mm;text-align: right;left: 0;padding-right: 35mm;}
</style>
<input type="hidden" name="editorImage" id="editorImage">
<img id="preview" alt="이미지 미리보기" style="display:none">
<canvas id="canvas"  style="display:none"></canvas>
<!-- <button type="button" onclick="bbsToImg();">이미지 다운로드</button> -->
 <div class="building_news_sample_wrapper">
    <div class="building_news_sample_wrap">
        <div class="news_content">
            <?php echo $building_bbs_row['bb_content']; ?>
            <div class="new_info_hd">
                <?php if($building_bbs_row['bb_number'] != ""){?>
                <p class="news_number">
                    <span class="news_number_label news_number_box1">문 서 번 호</span>
                    <span class="news_number_box1 news_number_box2"><?php echo $bb_number;?></span>
                </p>
                <?php }?>
                <p class="news_number news_number2 news_number_r">
                    <span class="news_number_label">게 시 기 한</span>
                    <span class="news_number_box2"><?php echo $building_bbs_row['bbs_gigan'] ? '영 구 게 시' : $building_bbs_row['edate'].'까지';?></span>
                </p>
            </div>
            <p class="news_tit_box"><?php echo $building_bbs_row['bb_title']; ?></p>
            <p class="building_name"><?php echo $building_info['building_name']; ?></p>
        </div>
    </div>
</div>
<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p><?php echo $bbs_code_name; ?> 내용을 저장 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
function buildingInfoPopOpen(){
    $("#building_info_pop").show();
    bodyLock();
}

function buildingInfoPopClose(){
    $("#building_info_pop").hide();
    bodyUnlock();
}

const ip = "<?php echo $_SERVER['REMOTE_ADDR']; ?>";
$(function(){
    bbsToImg();
});


function bbsToImg(){

    buildingInfoPopOpen();

    let type = "<?php echo $type; ?>";
    let bb_id = "<?php echo $bb_idx; ?>";
    let redirect_url = "<?php echo $redirect_url; ?>";
    let editor = document.querySelector('.building_news_sample_wrap');
    editor.style.backgroundColor = "#fff"; // 배경색 추가 (투명 방지)

    html2canvas(editor, {
        scale: 3, 
        allowTaint: true, // 크로스오리진 이미지 허용
        useCORS: true     // CORS 이미지 캡처
    }).then(canvas => {
        let imgData = canvas.toDataURL("image/png");
        document.getElementById('editorImage').value = imgData;
        document.getElementById('preview').src = imgData;
        console.log("이미지 변환 성공!", imgData);


        let formData = new FormData();
        formData.append('editorImage', imgData); // Base64 이미지 데이터
        formData.append('bb_idx', "<?php echo $bb_idx; ?>");
        // jQuery의 $.ajax()를 사용한 POST 요청
        $.ajax({
            url: './building_news_image.php', // 요청할 URL
            type: 'POST', // HTTP 메서드
            data: formData,
            cache: false,
            async: false,
            dataType: "json",
            contentType: false,
            processData: false,
            success: function(data) {
                console.log('data:::', data);
                
                if(data.result == false) { 
                    alert(data.msg);
                    buildingInfoPopClose();
                    return false;
                }else{
                    alert(data.msg);

                    setTimeout(() => {
                        location.href = "./" + redirect_url + "?w=u&type=" + type + "&bb_id=" + bb_id
                    }, 1000);
                }
                
            },
            error:function(e){
                console.log('error', e);
                alert(e);
            }
        });
    });
}

</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>
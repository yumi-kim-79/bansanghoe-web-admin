<?php
include_once('./_common.php');

//auth_check($auth[$sub_menu], "w");

$building_bbs = "SELECT * FROM a_building_bbs WHERE bb_id = '{$bb_idx}'";
$building_bbs_row = sql_fetch($building_bbs);

$g5['title'] = $building_bbs_row['bb_title'];
include_once(G5_PATH.'/head.sub.php');


//echo $building_bbs;

//print_r2($building_bbs_row);
$bb_number = $building_bbs_row['bb_number'];
// $bb_number = implode(" ", $bb_number);
//print_r2($bb_number);
?>
<style>
.building_news_sample_wrap {position: relative;max-width:210mm;margin: 0 auto;}
.news_content {width: 100%;}
/* .preset_info {padding: 41mm 5mm 34mm !important;} */

.building_news_sample_hd {width: 100%;padding:15px;display: flex;justify-content:flex-end;}
.building_news_sample_hd button {padding:10px 15px;border-radius:6px;border:none;background: var(--colorMain);color: #fff;font-size: 14px;}

.news_tit_box {position: absolute;top:100px;font-size: 30px;font-weight: 600;text-align: center;width: 100%;max-width: 210mm;left: 50%;transform:translateX(-50%);}

.new_info_hd {width: 100%;max-width: 210mm;left: 50%;transform:translateX(-50%);display: flex;align-items:center;justify-content:space-between;position: absolute;top:25px;padding: 0 16px;}
.news_number {font-size: 12px;width: 50%;display: flex;flex-direction:column}
.news_number2 {text-align: right;}
/* text-align:justify; */
.news_number span {display: block;}
.news_number span:after {content:"";display:inline-block;width:100%;}
.news_number_label {position: relative;top:10px;}
.news_number span.news_number_box1 {width: auto;}
</style>
<input type="hidden" name="editorImage" id="editorImage">
<img id="preview" alt="이미지 미리보기" style="display:none">
<canvas id="canvas"  style="display:none"></canvas>
<button type="button" onclick="bbsToImg();">이미지 다운로드</button>
<div class="building_news_sample_wrap">
    <div class="news_content">
        <?php echo $building_bbs_row['bb_content']; ?>
        <div class="new_info_hd">
            <p class="news_number">
                <span class="news_number_label news_number_box1">문 서 번 호</span>
                <span class="news_number_box1"><?php echo $bb_number;?></span>
            </p>
            <?php if($building_bbs_row['edate'] != ''){?>
            <p class="news_number">
                <span class="news_number_label">게 시 기 한</span>
                <span><?php echo $building_bbs_row['edate'];?>까지</span>
            </p>
            <?php }?>
        </div>
        <p class="news_tit_box"><?php echo $building_bbs_row['bb_title']; ?></p>
    </div>
</div>
<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>저장 중입니다.</p>
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
    if(ip != '59.16.155.80'){
        bbsToImg();
    }
});


function bbsToImg(){

    buildingInfoPopOpen();

    let bb_id = "<?php echo $bb_idx; ?>";
    let editor = document.querySelector('.building_news_sample_wrap');
    editor.style.backgroundColor = "#fff"; // 배경색 추가 (투명 방지)

    html2canvas(editor, {
        scale: 2, 
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
                        location.href = "/building_news_info_form.php?w=u&&bb_id=" + bb_id
                    }, 1000);
                }
                
            },
            error:function(e){
                console.log('error', e);
                alert('오류가 발생했습니다.');
            }
        });
    });
}

</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>
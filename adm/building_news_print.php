<?php
include_once('./_common.php');

//auth_check($auth[$sub_menu], "w");
$building_bbs = "SELECT * FROM a_building_bbs WHERE bb_id = '{$bb_idx}'";
$building_bbs_row = sql_fetch($building_bbs);

$building_info = get_builiding_info($building_bbs_row['building_id']);

$g5['title'] = $building_bbs_row['bb_title'];
include_once(G5_PATH.'/head.sub.php');

$building_bbs = "SELECT * FROM a_building_bbs WHERE bb_id = '{$bb_idx}'";
$building_bbs_row = sql_fetch($building_bbs);
//echo $building_bbs;

// print_r2($building_bbs_row);
$bb_number = $building_bbs_row['bb_number'];

// 적용 예시
$content = $building_bbs_row['bb_content'];

?>
<style>
.building_news_sample_wrap {position: relative;min-width:210mm}
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
    position: relative;
}
.news_content table {border-collapse: collapse;margin-bottom: 20px;}
.news_content table tr td {border:1.5px solid #000;padding:5px 3px;}

.news_content img {margin: 0 auto;display: block;}
.preset_info {padding: 40mm 5mm 40mm !important;}

.building_news_sample_hd {width: 100%;padding:15px;display: flex;justify-content:flex-end;min-width:210mm}
.building_news_sample_hd button {padding:10px 15px;border-radius:6px;border:none;background: var(--colorMain);color: #fff;font-size: 14px;}

.news_tit_box {position: absolute;top:93px;font-size: 40px;font-weight: 600;text-align: center;width: 100%;max-width: 210mm;left: 50%;transform:translateX(-50%);}

.new_info_hd {display: flex;justify-content:space-between;align-items:center;position: absolute;top:40px;left:0;width: 100%;max-width:210mm;padding: 0 5mm;font-size: 12px;}
.news_number span {display: block;}
.news_number span:first-child:after {content:"";display:inline-block;width:100%;}
.news_number span:nth-child(2) {margin-top: -10px;}
.news_number_r span {text-align: right;}

.building_name {position: absolute;bottom:60px;font-size: 24px;font-weight: 500;width: 100%;max-width: 210mm;text-align: right;left: 0;padding-right: 35mm;}

</style>
<div class="building_news_sample_hd">
    <button type="button" onclick="printBuildingNews();">인쇄</button>
</div>
<div class="building_news_sample_wrap">
    <div class="news_content">
        <div class="new_info_hd">
            <p class="news_number">
                <span class="news_number_label news_number_box1">문 서 번 호</span>
                <span class="news_number_box1"><?php echo $bb_number;?></span>
            </p>
            <p class="news_number news_number2 news_number_r">
                <span class="news_number_label">게 시 기 한</span>
                <span class="news_number_box2"><?php echo $building_bbs_row['bbs_gigan'] ? '영 구 게 시' : $building_bbs_row['edate'].'까지';?></span>
            </p>
        </div>
      
        <p class="news_tit_box"><?php echo $building_bbs_row['bb_title']; ?></p>
        <p class="building_name"><?php echo $building_info['building_name']; ?></p>
        <div class="news_content_box">
        <?php echo $content; ?>
        </div>
    </div>
</div>
<script>
function printBuildingNews() {
    var printContent = document.querySelector(".building_news_sample_wrap").cloneNode(true);
    var originalContent = document.body.innerHTML;

    // 인쇄 전용 스타일 추가
    var printStyle = document.createElement("style");
    printStyle.innerHTML = `

        @page {
            size: A4 portrait; /* 세로 방향으로 고정 */
            margin: 0;
        }

        @media print {
            body { margin: 0; padding: 0; background: none;-webkit-print-color-adjust: exact; }
            .building_news_sample_hd { display: none !important; } /* 인쇄 버튼 숨김 */
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
            }
            .news_content table {border-collapse: collapse;}
            .news_content table tr td {border:1.5px solid #000;padding:5px 3px;}

            .news_content img {margin: 0 auto;display: block;}
            
            .news_tit_box {
                position: absolute;
                top: 100px;
                font-size: 30px;
                font-weight: 600;
                text-align: center;
                width: 100%;
                max-width: 210mm;
                left: 50%;
                transform:translateX(-50%);
                page-break-before: avoid; /* 제목이 밀려나는 것 방지 */
            }

            .new_info_hd {display: flex;justify-content:space-between;align-items:center;position: absolute;top:40px;left:0;width: 100%;max-width:210mm;padding: 0 5mm;font-size: 12px;}
            .news_number span {display: block;}
            .news_number span:first-child {text-align: justify;}
            .news_number span:first-child:after {content:"";display:inline-block;width:100%;}
            .news_number span:nth-child(2) {margin-top: -10px;}
            .news_number_r span {text-align: right;}

            .building_name {position: absolute;bottom:60px;font-size: 24px;font-weight: 500;width: 100%;max-width: 210mm;text-align: right;left: 0;padding-right: 35mm;}
        }
    `;

    document.head.appendChild(printStyle); // 스타일 적용
    document.body.innerHTML = "";
    document.body.appendChild(printContent);

    window.print(); // 인쇄 실행

    // 원래 페이지 복원
    document.body.innerHTML = originalContent;
    location.reload();
}
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>
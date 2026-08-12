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

/**
 * ★안내문 본문 안전 정리 (2026-08)
 *
 * 증상: 특정 안내문만 팝업은 정상인데 [인쇄] 버튼이 아무 반응이 없었다.
 * 원인: 본문이 엑셀/한글의 "웹페이지로 저장" 결과를 통째로 붙여넣은 것이라
 *       </body></html> 나 닫히지 않은 <!-- 주석이 섞여 있었다. 브라우저가 거기서
 *       문서를 끝내 버려 **뒤따르는 <script>가 아예 파싱되지 않았고**,
 *       printBuildingNews 가 정의되지 않아 버튼이 죽었다.
 *       (표는 그 앞에 있어서 화면에는 멀쩡히 보였다 — 그래서 원인 파악이 어려웠다)
 *
 * 대응: ①문서를 끝내는 태그 제거 ②주석 짝 맞추기 ③본문 내 <script> 제거.
 *       근본 방어는 아래 <script> 를 본문보다 **앞에** 두는 것이고, 이 함수는 이중 방어다.
 */
function news_content_safe($html){
    if ($html === null || $html === '') return '';

    // ① 문서 자체를 끝내거나 새로 여는 태그 제거 (오피스 HTML 붙여넣기 잔재)
    $html = preg_replace('#<\s*/?\s*(html|head|body)\b[^>]*>#i', '', $html);
    $html = preg_replace('#<!DOCTYPE[^>]*>#i', '', $html);

    // ② 본문 내 <script> 제거 — 안내문에 있을 이유가 없다
    $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html);
    $html = preg_replace('#</?script\b[^>]*>#i', '', $html);

    // ③ 닫히지 않은 HTML 주석 보정 — 여는 게 더 많으면 뒤에 닫아 준다
    $open  = preg_match_all('/<!--/', $html);
    $close = preg_match_all('/-->/', $html);
    if ($open > $close) $html .= str_repeat('-->', $open - $close);

    return $html;
}

$content = news_content_safe($content);

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

/* ★인쇄용 — 예전에는 JS가 body를 통째로 비우고 복제본을 넣었는데(그리고 location.reload()),
   본문이 조금만 이상해도 깨졌다. 이 팝업엔 안내문과 [인쇄] 버튼밖에 없으므로
   버튼만 숨기고 그대로 인쇄하면 된다. */
@page { size: A4 portrait; margin: 0; }
@media print {
    body { margin:0; padding:0; background:none; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .building_news_sample_hd, .toast_box { display:none !important; }
    .building_news_sample_wrap { min-width:0; }
    .news_content { width:210mm; height:297mm; margin:auto; padding:45mm 10mm;
        background:url('/images/building_news_sample.jpg') no-repeat center center;
        background-size:cover; box-sizing:border-box; overflow:hidden; font-size:16px; }
    .news_tit_box { top:100px; font-size:30px; page-break-before:avoid; }
}

</style>
<!-- ★인쇄 스크립트는 반드시 본문보다 **앞**에 둔다(2026-08).
     본문 HTML이 깨져 있으면 뒤에 있는 <script>가 파싱되지 않아 버튼이 죽는다. -->
<script>
function printBuildingNews() {
    window.print();
}
</script>
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

<?php
include_once(G5_PATH.'/tail.sub.php');
?>
<?php
include_once('./_common.php');

$g5['title'] = 'QR 종합 인쇄';
include_once(G5_PATH.'/head.sub.php');

$buidling_row = sql_fetch("SELECT * FROM a_building WHERE building_id = '{$building_id}'");
$dong_row = sql_fetch("SELECT * FROM a_building_dong WHERE dong_id = '{$dong_id}'");

//단지명
$building_title = $buidling_row['building_name'].' '.$dong_row['dong_name'];

//선택 업종
$ins_idx_arr = explode(",", $ins_idx); 
$chunked_arr = array_chunk($ins_idx_arr, 12);
//print_r2($ins_idx_arr);
?>
<style>
.building_news_sample_hd {width: 100%;padding:15px;display: flex;justify-content:flex-end;}
.building_news_sample_hd button {padding:10px 15px;border-radius:6px;border:none;background: var(--colorMain);color: #fff;font-size: 14px;}

.print_wrap {
    width: 210mm;
    height: 297mm;
    margin: auto;
    padding: 5mm;
    position: relative;
    background-color:#fff;
}

.building_title {font-size: 22px;font-weight: 700;text-align: center;padding-bottom: 20px;}
.qr_box_wrap {display: flex;flex-wrap:wrap;}
.qr_box_wrap .qr_box {width: calc(100% / 3);display: flex;flex-direction:column;align-items:center;justify-content:center;min-height:252px;border:1px solid #dfdfdf;border-right: none;border-top: none;}
.qr_box_wrap .qr_box:first-child {border-top: 1px solid #dfdfdf;}
.qr_box_wrap .qr_box:nth-child(2) {border-top: 1px solid #dfdfdf;}
.qr_box_wrap .qr_box:nth-child(3) {border-top: 1px solid #dfdfdf;}
.qr_box_wrap .qr_box:nth-child(3n) {border-right: 1px solid #dfdfdf;}
.qr_box_wrap .qr_box:last-child {border-right: 1px solid #dfdfdf;}
.qr_box .qr_img {margin-bottom: 20px;position:relative;display:inline-block;}
.qr_box .qr_img .qr_logo {position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:36px;height:36px;background:#fff;padding:2px;border-radius:4px;}
.qr_box .qr_box_tit {font-size: 16px;font-weight: 500;}
.pages {padding:10px;font-size: 14px;text-align: right;}
</style>
<div class="building_news_sample_hd">
    <button type="button" onclick="printBuildingNews();">인쇄</button>
</div>
<div class="print_wrapper">
<?php
foreach ($chunked_arr as $page => $items) {
?>
<?php
if($page >= 1){
?>
<div class="pages"><?php echo $page + 1?> Page</div>
<?php }?>
<div class="print_wrap">
    <div class="building_title">
        <?php echo $building_title; ?>
    </div>
    <div class="qr_box_wrap">
        <?php
         foreach ($items as $item) {
            $industry_row = sql_fetch("SELECT * FROM a_industry_list WHERE industry_idx = '{$item}'");

            $homepage = 'https://'.$_SERVER['HTTP_HOST'].'/inspection_form.php?bdi='.$building_id.'|'.$item;
        ?>
        <div class="qr_box">
            <div class="qr_img">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo $homepage; ?>" alt="">
                <img src="/images/aivex_logo_vertical.png" class="qr_logo" alt="AIVEX">
            </div>
            <div class="qr_box_tit">
                <?php echo $industry_row['industry_name']; ?>
            </div>
        </div>
        <?php }?>
    </div>
</div>
<?php }?>
</div>
<script>
function printBuildingNews() {
    var printContent = document.querySelector(".print_wrapper").cloneNode(true);
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
            .pages {display:none !important;}

            .print_wrap {
                width: 210mm;
                height: 297mm;
                margin: auto;
                padding: 30px;
                position: relative;
                background-color:#fff;
                page-break-after: always;
            }

            .building_title {font-size: 22px;font-weight: 700;text-align: center;padding-bottom: 20px;}
            .qr_box_wrap {display: flex;flex-wrap:wrap;}
            .qr_box_wrap .qr_box {width: calc(100% / 3);display: flex;flex-direction:column;align-items:center;justify-content:center;min-height:252px;border:1px solid #dfdfdf;border-right: none;border-top: none;}
            .qr_box_wrap .qr_box:first-child {border-top: 1px solid #dfdfdf;}
            .qr_box_wrap .qr_box:nth-child(2) {border-top: 1px solid #dfdfdf;}
            .qr_box_wrap .qr_box:nth-child(3) {border-top: 1px solid #dfdfdf;}
            .qr_box_wrap .qr_box:nth-child(3n) {border-right: 1px solid #dfdfdf;}
            .qr_box_wrap .qr_box:last-child {border-right: 1px solid #dfdfdf;}
            .qr_box .qr_img {margin-bottom: 20px;position:relative;display:inline-block;}
            .qr_box .qr_img .qr_logo {position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:36px;height:36px;background:#fff;padding:2px;border-radius:4px;}
            .qr_box .qr_box_tit {font-size: 16px;font-weight: 500;}
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
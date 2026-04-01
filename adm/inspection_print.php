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
$chunked_arr = array_chunk($ins_idx_arr, 9);
?>
<style>
/* 인쇄 버튼 헤더 */
.print_header {width:100%;padding:15px;display:flex;justify-content:flex-end;gap:10px;}
.print_header button {padding:10px 20px;border-radius:6px;border:none;background:#1a2e4a;color:#fff;font-size:14px;font-weight:600;cursor:pointer;transition:background 0.2s;}
.print_header button:hover {background:#2a4a6a;}

/* A4 용지 */
.print_wrap {
    width:210mm;
    min-height:297mm;
    margin:10px auto;
    padding:15mm 12mm 10mm;
    position:relative;
    background:#fff;
    box-shadow:0 2px 12px rgba(0,0,0,0.08);
}

/* 헤더 */
.print_title_bar {
    background:#1a2e4a;
    color:#fff;
    padding:14px 20px;
    border-radius:8px;
    margin-bottom:20px;
    display:flex;
    align-items:center;
    justify-content:space-between;
}
.print_title_bar .title_text {font-size:20px;font-weight:700;letter-spacing:0.5px;}
.print_title_bar .title_sub {font-size:12px;opacity:0.7;}

/* QR 그리드 */
.qr_grid {
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:14px;
    padding:0 4px;
}

/* QR 카드 */
.qr_card {
    border:1px solid #e8ecf0;
    border-radius:10px;
    padding:18px 10px 14px;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    background:#fff;
    box-shadow:0 1px 4px rgba(26,46,74,0.06);
    transition:box-shadow 0.2s;
}
.qr_card:hover {box-shadow:0 3px 12px rgba(26,46,74,0.12);}

/* QR 이미지 + 로고 */
.qr_img_wrap {
    position:relative;
    display:inline-block;
    margin-bottom:12px;
}
.qr_img_wrap img.qr_code {display:block;width:130px;height:130px;}
.qr_img_wrap .qr_logo {
    position:absolute;
    top:50%;left:50%;
    transform:translate(-50%,-50%);
    width:50px;height:50px;
    background:#fff;
    padding:3px;
    border-radius:6px;
    box-shadow:0 1px 3px rgba(0,0,0,0.1);
}

/* 업종명 */
.qr_label {
    font-size:14px;
    font-weight:700;
    color:#1a2e4a;
    text-align:center;
    line-height:1.3;
    word-break:keep-all;
}

/* 페이지 표시 */
.page_indicator {padding:8px 0;font-size:12px;text-align:right;color:#999;}

/* 푸터 */
.print_footer {
    position:absolute;
    bottom:10mm;
    left:0;
    right:0;
    text-align:center;
    font-size:10px;
    color:#aaa;
}
</style>

<div class="print_header">
    <button type="button" onclick="printBuildingNews();">인쇄</button>
</div>
<div class="print_wrapper">
<?php
foreach ($chunked_arr as $page => $items) {
?>
<?php if($page >= 1){ ?>
<div class="page_indicator"><?php echo $page + 1?> / <?php echo count($chunked_arr); ?> Page</div>
<?php } ?>
<div class="print_wrap">
    <div class="print_title_bar">
        <div>
            <div class="title_text"><?php echo $building_title; ?></div>
            <div class="title_sub">QR 점검 안내</div>
        </div>
        <img src="/images/aivex_logo_vertical.png" alt="AIVEX" style="height:32px;opacity:0.9;">
    </div>
    <div class="qr_grid">
        <?php
         foreach ($items as $item) {
            $industry_row = sql_fetch("SELECT * FROM a_industry_list WHERE industry_idx = '{$item}'");
            $homepage = 'https://'.$_SERVER['HTTP_HOST'].'/inspection_form.php?bdi='.$building_id.'|'.$item;
        ?>
        <div class="qr_card">
            <div class="qr_img_wrap">
                <img class="qr_code" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo $homepage; ?>" alt="QR">
                <img src="/images/aivex_logo_vertical.png" class="qr_logo" alt="AIVEX">
            </div>
            <div class="qr_label"><?php echo $industry_row['industry_name']; ?></div>
        </div>
        <?php } ?>
    </div>
    <div class="print_footer">AIVEX - Smart Building Management</div>
</div>
<?php } ?>
</div>

<script>
function printBuildingNews() {
    var printContent = document.querySelector(".print_wrapper").cloneNode(true);
    var originalContent = document.body.innerHTML;

    var printStyle = document.createElement("style");
    printStyle.innerHTML = `
        @page {
            size: A4 portrait;
            margin: 0;
        }
        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body { margin:0; padding:0; background:none; }
            .print_header { display:none !important; }
            .page_indicator { display:none !important; }

            .print_wrap {
                width:210mm;
                min-height:297mm;
                margin:0 auto;
                padding:15mm 12mm 10mm;
                position:relative;
                background:#fff;
                box-shadow:none;
                page-break-after:always;
            }

            .print_title_bar {
                background:#1a2e4a !important;
                color:#fff !important;
                padding:14px 20px;
                border-radius:8px;
                margin-bottom:20px;
                display:flex;
                align-items:center;
                justify-content:space-between;
            }
            .print_title_bar .title_text {font-size:20px;font-weight:700;letter-spacing:0.5px;}
            .print_title_bar .title_sub {font-size:12px;opacity:0.7;}

            .qr_grid {
                display:grid;
                grid-template-columns:repeat(3, 1fr);
                gap:14px;
                padding:0 4px;
            }

            .qr_card {
                border:1px solid #e8ecf0;
                border-radius:10px;
                padding:18px 10px 14px;
                display:flex;
                flex-direction:column;
                align-items:center;
                justify-content:center;
                background:#fff;
                box-shadow:none;
            }

            .qr_img_wrap {position:relative;display:inline-block;margin-bottom:12px;}
            .qr_img_wrap img.qr_code {display:block;width:130px;height:130px;}
            .qr_img_wrap .qr_logo {
                position:absolute;top:50%;left:50%;
                transform:translate(-50%,-50%);
                width:50px;height:50px;
                background:#fff;padding:3px;border-radius:6px;
            }

            .qr_label {font-size:14px;font-weight:700;color:#1a2e4a;text-align:center;line-height:1.3;}

            .print_footer {
                position:absolute;bottom:10mm;left:0;right:0;
                text-align:center;font-size:10px;color:#aaa;
            }
        }
    `;

    document.head.appendChild(printStyle);
    document.body.innerHTML = "";
    document.body.appendChild(printContent);

    window.print();

    document.body.innerHTML = originalContent;
    location.reload();
}
</script>

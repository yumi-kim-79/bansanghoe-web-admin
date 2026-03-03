<?php
require_once __DIR__ . '/tcpdf/tcpdf.php';

$pdf = new TCPDF();
$pdf->SetTitle($bb_title);

// 페이지 자동 줄바꿈 해제 (여백 제거)
$pdf->SetAutoPageBreak(false, 0);

// 페이지 추가
$pdf->AddPage();

// 🔹 배경 이미지 삽입 (A4 크기: 210mm x 297mm)
$pdf->Image(__DIR__ . '/images/building_news_sample.jpg', 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);

// 🔹 패딩을 적용한 내용 출력
$pdf->SetFont('cid0kr', '', 12); // 한글 폰트 설정
$pdf->SetXY(10, 45); // X: 10mm, Y: 45mm 위치로 이동

$html = $bb_content;

// 🔹 패딩을 적용한 영역에 출력
$pdf->writeHTMLCell(190, 207, 10, 45, $html, 0, 1, false, true, '');

// PDF 출력
$pdfFilePath = __DIR__ . '/output4.pdf';
$pdf->Output($pdfFilePath, 'F'); 
?>

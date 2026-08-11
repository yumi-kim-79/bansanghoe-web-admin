<?php
require_once './_common.php';

//$addr = "경기도 부천시 길주로 272";

$addr_info = get_location($addr);

//print_r2($addr_info);

// ★조용한 실패 금지(2026-08) — 예전에는 조회가 안 되면 아무것도 출력하지 않아
//   사용자도 담당자도 어디서 끊겼는지 알 수 없었다. 반드시 사유를 보여준다.
function building_api_notice($msg, $detail = ''){
    echo '<div class="building_api_error" style="padding:10px 12px;margin-bottom:10px;'
       . 'background:#fdecea;border:1px solid #f5c6cb;border-radius:4px;color:#a3252c;font-size:13px;line-height:1.6;">'
       . '<strong>' . htmlspecialchars($msg) . '</strong>'
       . ($detail !== '' ? '<br><span style="color:#7a5c5f;font-size:12px;">' . htmlspecialchars($detail) . '</span>' : '')
       . '<br>아래 건물 정보는 <strong>직접 입력</strong>해 주세요.</div>';
}

if (!empty($addr_info['error'])) {
    building_api_notice('주소를 찾지 못했습니다.', $addr_info['error']);
    return;
}

$tried = array();
$building_info = building_api(
    $addr_info['scode'], $addr_info['bcode'],
    $addr_info['main_building_no'], $addr_info['sub_building_no'],
    isset($addr_info['dong_name']) ? $addr_info['dong_name'] : '',
    $tried
);

$result_code = isset($building_info['response']['header']['resultCode'])
    ? $building_info['response']['header']['resultCode'] : '';
$total_found = isset($building_info['response']['body']['totalCount'])
    ? (int)$building_info['response']['body']['totalCount'] : 0;

if ($result_code !== '00' || $total_found < 1) {
    $msg = ($result_code === '00')
        ? '건축물대장에 등록된 건물 정보가 없습니다.'
        : '건축물대장 조회에 실패했습니다.';

    $lines = array();
    foreach ($tried as $t) {
        $lines[] = $t['sigunguCd'] . '-' . $t['bjdongCd'] . ($t['fallback'] ? '(개편 前)' : '(현행)')
                 . ' → ' . ($t['resultCode'] !== '' ? $t['resultCode'] : '응답없음')
                 . '/' . $t['totalCount'] . '건';
    }
    $detail = '주소: ' . $addr . ' · 법정동코드 ' . $addr_info['b_code']
            . '(' . (isset($addr_info['dong_name']) ? $addr_info['dong_name'] : '?') . ')'
            . ' · 본번 ' . $addr_info['main_building_no'] . ' 부번 ' . $addr_info['sub_building_no']
            . "\n시도: " . implode(' / ', $lines);

    // 인천 개편 지역 안내 — 담당자가 원인을 바로 알 수 있게
    if (substr($addr_info['scode'], 0, 2) === '28') {
        $detail .= "\n※ 2026-07 인천 행정구역 개편 지역입니다. 구청의 건축물대장 소재지 정정이 끝나기 전까지는 조회되지 않을 수 있습니다.";
    }

    building_api_notice($msg, $detail);
    return;
}

$body = $building_info['response']['body']['items']['item'][0]; // getBrTitleInfo 단지정보
{
?>
<input type="hidden" name="building_api" value="Y">
<div class="builiding_info_tr">
    <div class="building_info_th building_info_td">건물명</div>
    <div class="building_info_td">
        <input type="text" name="building_info_name" id="building_info_name" value="<?php echo $body['bldNm']; ?>">
    </div>
    <div class="building_info_th building_info_th2 building_info_td">용도</div>
    <div class="building_info_td"><input type="text" name="building_info_type" id="building_info_type" value="<?php echo $body['mainPurpsCdNm']; ?>"></div>
</div>
<div class="builiding_info_tr">
    <div class="building_info_th building_info_td">법정동 주소</div>
    <div class="building_info_td"><input type="text" name="building_info_addr1" id="building_info_addr1" value="<?php echo $body['platPlc'];?>"></div>
    <div class="building_info_th building_info_th2 building_info_td">도로명 주소</div>
    <div class="building_info_td"><input type="text" name="building_info_addr2" id="building_info_addr2" value="<?php echo $body['newPlatPlc'];?>"></div>
</div>
<div class="builiding_info_tr">
    <div class="building_info_th building_info_td">연면적(㎡)</div>
    <div class="building_info_td"><input type="text" name="building_info_size" id="building_info_size" value="<?php echo $body['totArea']; ?>"></div>
    <div class="building_info_th building_info_th2 building_info_td">사용승인일</div>
    <div class="building_info_td"><input type="text" name="building_info_use_date" id="building_info_use_date" value="<?php echo $body['useAprDay']; ?>"></div>
</div>
<div class="builiding_info_tr">
    <div class="building_info_th building_info_td">층수(지상/지하)</div>
    <div class="building_info_td"><input type="text" name="building_info_floor_up" id="building_info_floor_up" value="<?php echo $body['grndFlrCnt'].'/'.$body['ugrndFlrCnt'];?>"></div>
    <div class="building_info_th building_info_th2 building_info_td">승강기(승용/비상)</div>
    <div class="building_info_td"><input type="text" name="building_info_elevation" id="building_info_elevation" value="<?php echo $body['rideUseElvtCnt']; ?>"></div>
</div>
<div class="builiding_info_tr">
    <div class="building_info_th building_info_td">주차대수(옥내/옥외)</div>
    <div class="building_info_td"><input type="text" name="building_info_parking1" id="building_info_parking1" value="<?php echo $body['indrAutoUtcnt'].'/'.$body['oudrAutoUtcnt']; ?>"></div>
    <div class="building_info_th building_info_th2 building_info_td">구조</div>
    <div class="building_info_td"><input type="text" name="building_info_structure" id="building_info_structure" value="<?php echo $body['strctCdNm']; ?>"></div>
</div>
<div class="builiding_info_tr">
    <div class="building_info_th building_info_td">기계식주차(옥내/옥외)</div>
    <div class="building_info_td"><input type="text" name="building_info_parking2" id="building_info_parking2" value="<?php echo $body['indrMechUtcnt'].'/'.$body['oudrMechUtcnt'];?>"></div>
    <div class="building_info_th building_info_th2 building_info_td">호수(호)</div>
    <div class="building_info_td"><input type="text" name="building_info_ho" id="building_info_ho" value="<?php echo $body['hhldCnt'] + $body['hoCnt']; ?>"></div>
</div>
<?php }?>
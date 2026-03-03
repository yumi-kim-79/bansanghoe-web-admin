<?php
require_once './_common.php';

//$addr = "경기도 부천시 길주로 272";

$addr_info = get_location($addr);

//print_r2($addr_info);

$building_info = 
building_api($addr_info['scode'], $addr_info['bcode'], $addr_info['main_building_no'], $addr_info['sub_building_no']);

//$building_info2 = building_api2($addr_info['scode'], $addr_info['bcode'], $addr_info['main_building_no'], $addr_info['sub_building_no']);

$body = $building_info['response']['body']['items']['item'][0]; // getBrTitleInfo 단지정보
//$body2 = $building_info2['response']['body']['items']['item'][0]; // getBrRecapTitleInfo 주차 관련
if($_SERVER['REMOTE_ADDR'] == '59.16.155.80'){
    // print_r2($body);
}
//print_r2($body2);
if($building_info['response']['header']['resultCode'] == '00'){
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
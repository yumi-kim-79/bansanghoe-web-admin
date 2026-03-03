<?php
include_once("_common.php");

exit;

function get_location($addr){

    //좌표 가져오기 kakao rest api
    $apiKey = '836d2852627707821b0d5312bb082e71';
    //$apiKey = '156f26c55c8f303054817dc01d53b3d2';
    $address = $addr; // Replace with the address you want to convert

    $addressEncoded = urlencode($address);
    $apiUrl = "https://dapi.kakao.com/v2/local/search/address.json?query=$addressEncoded";

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: KakaoAK ' . $apiKey));
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response);

    //print_r2($data);

    if (isset($data->documents[0]->address)) {
        $latitude = $data->documents[0]->address->y;
        $longitude = $data->documents[0]->address->x;
        $b_code = $data->documents[0]->address->b_code;
        $main_address_no = $data->documents[0]->address->main_address_no;
        $sub_address_no = $data->documents[0]->address->sub_address_no;
        //echo "Latitude: $latitude<br>";
        //echo "Longitude: $longitude<br>";
    } else {
        echo "제공된 주소에 대한 단지 정보를 찾을 수 없습니다.";
    }

    //0014 형식으로 4자리로 만듬 빈자리는 0
    $main_address_no = str_pad($main_address_no, 4, "0", STR_PAD_LEFT);
    $sub_address_no = str_pad($sub_address_no, 4, "0", STR_PAD_LEFT);

    $addr_data['lat'] = $latitude;
    $addr_data['lng'] = $longitude;
    $addr_data['b_code'] = $b_code;
    $addr_data['scode'] = substr($b_code, 0, 5);
    $addr_data['bcode'] = substr($b_code, 5, 10);
    $addr_data['main_building_no'] = $main_address_no;
    $addr_data['sub_building_no'] = $sub_address_no;

    return $addr_data;
}

function building_api($scode, $bcode, $bun = '', $ji = ''){
    $apiKey = BUILDING_PUBLIC_KEY;

    //$apiUrl = "http://apis.data.go.kr/1613000/BldRgstHubService/getBrRecapTitleInfo?sigunguCd=".$scode."&bjdongCd=".$bcode."&bun=".$bun."&ji=".$ji."&_type=json&serviceKey=".$apiKey;

    $apiUrl = "https://apis.data.go.kr/1613000/BldRgstHubService/getBrTitleInfo?serviceKey=".$apiKey."&sigunguCd=".$scode."&bjdongCd=".$bcode."&platGbCd=0&bun=".$bun."&ji=".$ji."&_type=json&numOfRows=10&pageNo=1";

    //echo $apiUrl;

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    
}

$addr = "경기도 부천시 원미구 수도로206번길 66-4";
$addr_info = get_location($addr);

building_api($addr_info['scode'], $addr_info['bcode'], $addr_info['main_building_no'], $addr_info['sub_building_no']);
//print_r2($addr_info);
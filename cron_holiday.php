<?php
/*******************************************************************************
 * cron_holiday.php — 공공데이터포털 특일정보(getRestDeInfo) → a_holiday 적재
 *  - 루트에 위치(기존 cron 컨벤션). 실행 경로의 common.php(=경로별 dbconfig)로
 *    DB 자동 결정: /var/www/html → 운영(sinbansang) / /var/www/html_test → 테스트
 *  - 대상 연도: 올해 + 내년
 *  - getRestDeInfo 만 호출(국경일 자동 포함), _type=json
 *  - 연도별로 12개월 수집 → API 한 건이라도 성공하면 DELETE 후 재적재
 *    (전체 실패 시 기존 데이터 유지 = 폐지 공휴일 정확 반영 + 빈상태 방지)
 *  - CLI 실행 전용 (연 2회 cron: 1/1·6/1 03:00 + 배포 직후 1회 수동)
 *******************************************************************************/
include_once(__DIR__.'/common.php');                  // G5 부트스트랩 + sql_query
require_once(__DIR__.'/config/holiday_api_key.php');  // HOLIDAY_API_KEY 상수

if(!defined('HOLIDAY_API_KEY') || HOLIDAY_API_KEY === ''){
    echo "[holiday] ERROR: HOLIDAY_API_KEY 미설정\n";
    exit(1);
}

$today    = date('Y-m-d H:i:s');
$thisY    = (int)date('Y');
$years    = [$thisY, $thisY + 1];   // 올해 + 내년
$endpoint = 'https://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService/getRestDeInfo';

$api_ok = 0; $api_fail = 0; $upsert = 0;

foreach($years as $y){

    $collected   = [];   // [날짜 => ['name'=>..,'type'=>..]]
    $year_api_ok = 0;

    for($m = 1; $m <= 12; $m++){
        $params = [
            'solYear'    => $y,
            'solMonth'   => sprintf('%02d', $m),
            'numOfRows'  => 50,
            '_type'      => 'json',
            'ServiceKey' => HOLIDAY_API_KEY,   // Decoding 키 → http_build_query 가 자동 인코딩
        ];
        $url = $endpoint.'?'.http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr = curl_error($ch);
        curl_close($ch);

        if($resp === false || $http >= 400){
            $api_fail++;
            echo "[holiday] {$y}-".sprintf('%02d',$m)." API FAIL http={$http} err={$cerr}\n";
            continue;
        }

        $obj = json_decode($resp, true);
        if(!is_array($obj) || !isset($obj['response']['body'])){
            $api_fail++;
            echo "[holiday] {$y}-".sprintf('%02d',$m)." PARSE FAIL ".substr($resp,0,150)."\n";
            continue;
        }
        $api_ok++; $year_api_ok++;

        $body  = $obj['response']['body'];
        $total = isset($body['totalCount']) ? (int)$body['totalCount'] : 0;
        if($total === 0) continue;                          // 케이스1: 공휴일 없음

        $items = isset($body['items']['item']) ? $body['items']['item'] : [];
        if(isset($items['locdate'])) $items = [$items];     // 케이스2: 단일객체 → 배열화
        // 케이스3: 이미 배열

        foreach($items as $it){
            if((isset($it['isHoliday']) ? $it['isHoliday'] : 'N') !== 'Y') continue; // 공휴일만
            $loc  = (string)$it['locdate'];                 // YYYYMMDD
            if(!preg_match('/^\d{8}$/', $loc)) continue;
            $date = substr($loc,0,4).'-'.substr($loc,4,2).'-'.substr($loc,6,2);
            $name = isset($it['dateName']) ? trim($it['dateName']) : '공휴일';
            $type = (mb_strpos($name,'대체')!==false) ? 'substitute'
                  : ((mb_strpos($name,'임시')!==false) ? 'temporary' : 'public');
            $collected[$date] = ['name'=>$name, 'type'=>$type];
        }
    }

    // 그 해 API 한 건이라도 성공해야 교체(전부 실패면 기존 유지 = 빈상태 방지)
    if($year_api_ok > 0){
        sql_query("DELETE FROM a_holiday WHERE year = {$y}");
        foreach($collected as $date => $info){
            $d = sql_real_escape_string($date);
            $n = sql_real_escape_string($info['name']);
            $t = sql_real_escape_string($info['type']);
            sql_query("INSERT INTO a_holiday
                       (holiday_date, holiday_name, holiday_type, year, created_at, updated_at)
                       VALUES ('{$d}', '{$n}', '{$t}', {$y}, '{$today}', '{$today}')
                       ON DUPLICATE KEY UPDATE
                       holiday_name='{$n}', holiday_type='{$t}', year={$y}, updated_at='{$today}'");
            $upsert++;
            echo "[holiday] upsert {$date} {$info['name']} ({$info['type']})\n";
        }
        echo "[holiday] {$y} replaced: ".count($collected)." days (api_ok={$year_api_ok}/12)\n";
    }else{
        echo "[holiday] {$y} SKIP replace (all 12 months API failed) — 기존 데이터 유지\n";
    }
}

echo "[holiday] DONE years=".implode(',', $years)." api_ok={$api_ok} api_fail={$api_fail} upsert={$upsert}\n";

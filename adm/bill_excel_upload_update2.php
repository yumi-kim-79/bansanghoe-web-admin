<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once('./_common.php');
require_once(G5_PATH.'/lib/PhpSpreadsheet/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if($_FILES['excelfile']['size'] <= 0) alert("엑셀 파일이 없습니다.");



//파일 첨부
$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));

 // 엑셀 파일 업로드 및 데이터 확인
 if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelfile'])) {

    $file = $_FILES['excelfile'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = G5_DATA_PATH.'/file/bill_excel';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename  = $_FILES['excelfile']['name'];
        $filename  = get_safe_filename($filename);

        shuffle($chars_array);
        $shuffle = implode('', $chars_array);

        $filename = md5(sha1($_SERVER['REMOTE_ADDR'])).'_'.substr($shuffle,0,8).'_'.replace_filename($filename);

        $filePath = $uploadDir .'/'. $filename;

        
        move_uploaded_file($file['tmp_name'], $filePath);
       
        //$bill_file_sql = "INSERT INTO bill_file WHERE bill_id = '{$bill_id}'";

        try {

            // echo "저장은 완료".$filePath;
            // exit;

            // PhpSpreadsheet로 엑셀 파일 읽기
            $spreadsheet = IOFactory::load($filePath);

            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();           

            // 빈 행(row) 제거
            $data = array_filter($data, function ($row) {
                return array_filter($row); // 행에 데이터가 하나라도 있으면 유지
            });


            // 빈 열(col) 제거
            $transposedData = array_map(null, ...$data); // 행과 열을 뒤집음
            $transposedData = array_filter($transposedData, function ($col) {
                return array_filter($col); // 열에 데이터가 하나라도 있으면 유지
            });
            $data = array_map(null, ...$transposedData); // 다시 원래 형태로 복원

            $groupedData = [];
            foreach ($data as $row) {
                if (empty($row[0]) && !empty($row[1])) {
                    $groupKey = $row[1]; // 두 번째 셀의 값을 그룹 키로 사용
                    if (!isset($groupedData[$groupKey])) {
                        $groupedData[$groupKey] = [];
                    }
                } elseif (!empty($row[0])) {
                    // 그룹에 속하는 데이터 추가
                    if (isset($groupKey)) {
                        $groupedData[$groupKey][] = $row;
                    }
                }
            }

            foreach ($groupedData as $groupKey => $rows) {
                $mergedData = []; // 항목명을 기준으로 데이터를 병합할 배열
            
                foreach ($rows as $row) {
                    $key = $row[0]; // 첫 번째 값(항목명)을 키로 사용
            
                    if (!isset($mergedData[$key])) {
                        // 항목명이 처음 등장하면 초기화
                        $mergedData[$key] = $row;
                    } else {
                        // 이미 존재하는 항목명이 있으면 데이터를 병합
                        $mergedData[$key] = array_merge($mergedData[$key], array_slice($row, 1)); // 첫 번째 값을 제외하고 병합
                    }
                }
            
                // 병합된 데이터를 그룹에 다시 저장
                $groupedData[$groupKey] = array_values($mergedData); // 배열의 키를 재정렬
            }

            // echo "<pre>";
            // print_r($groupedData);
            // echo "</pre>";
            
           
            // 데이터 출력
            $html .= '<style>
                    h3, h4 {
                        text-align: center;
                        margin-top:20px;
                    }
                    .sub_table th, .sub_table td {
                        padding: 10px 5px; /* 셀 내부 여백 */
                        
                        text-align: center; /* 텍스트 가운데 정렬 */
                        background: #fff;
                    }
                    .sub_table th {
                        background-color: #f4f4f4; /* 헤더 배경색 */
                        font-weight: bold;
                    }


                    .sub_table_labels {margin-bottom: 20px;}

                    .tbl_frm01 td {border:none;}

                    .sub_table_inner {height: 600px;border-left: 1px solid #ddd;border-top:1px solid #ddd;}
                    .sub_table_inner table {border-collapse: separate; border-spacing: 0;width: max-content; min-width: 100%;table-layout: fixed;}
                    .sub_table_inner table th, .sub_table_inner table td {min-width: 100px;box-sizing: border-box;white-space: nowrap;background: #fff;width: 100px;padding: 5px 3px;border-right: 1px solid #ddd;border-bottom:1px solid #ddd;}
                    .sub_table_inner table tr:first-child {position: sticky;top: 0;z-index: 3; }
                    .sub_table_inner table td:first-child {
                        position: sticky;
                        left: 0;
                        z-index: 2; /* 헤더의 첫 셀(겹침) 처리 위해 조정 */
                        background: #fff; /* 배경색을 줘야 아래 콘텐츠가 보이지 않음 */
                        box-shadow: 2px 0 4px rgba(0,0,0,0.03);
                    }

                    .sub_table tr:first-child td:first-child {
                        z-index: 5;
                        /* background: #e9ecef; */
                    }
                </style>';

            // 열 기준으로 모든 셀이 빈값인 열 제거
            $dongIdx = 0;
            // $html = "<input type='hidden' name='excel_type' value='".$excel_type."' />";
            // $html = "<input type='hidden' name='file_name' value='".$filename."' />";
            // $html .= "<input type='hidden' name='groupedCnt' value='".count($groupedData)."' />";

            $count = array();
            $count_sum = 0;
            foreach ($groupedData as $groupKey => $rows) {
                // 열 제거를 위한 필터링
                $columnsToKeep = [];
                $rowCount = count($rows);

                // 열의 인덱스를 확인하여 모든 셀이 비어 있는 열을 찾음
                for ($colIndex = 0; $colIndex < count($rows[0]); $colIndex++) {
                    $isColumnEmpty = true;
                    foreach ($rows as $row) {
                        if (!empty($row[$colIndex])) {
                            $isColumnEmpty = false;
                            break;
                        }
                    }
                    if (!$isColumnEmpty) {
                        $columnsToKeep[] = $colIndex;
                    }
                }

                // 필터링된 열만 유지
                $filteredRows = [];
                foreach ($rows as $row) {
                   
                    $filteredRow = [];
                    foreach ($columnsToKeep as $colIndex) {
                        $filteredRow[] = $row[$colIndex];
                    }
                    $filteredRows[] = $filteredRow;
                }
                

                //개수 계산해보기
                foreach ($filteredRows as $idx => $row) {

                    $first_key = str_replace(" ", "", $row[0]);

                    if($first_key == '동호'){
                        //echo $groupKey.'<br>';

                        // print_r2($row);

                        $exclude = ["동 호", "동합계", "전체합계"];

                        $filtered = array_filter($row, function ($value) use ($exclude) {
                            return !in_array($value, $exclude);
                        });

                        $filtered = array_values($filtered);


                        // $count += count($onlyNumbers);
                        //print_r2($filtered);

                        $count_sum += count($filtered);
                        array_push($count, count($filtered));
                    }
                    // echo $row[0];
                }
            }

            //현재 빌딩의 동호수
            // $ho_sqls = "SELECT COUNT(*) as cnt FROM a_building_ho WHERE building_id = '{$building_id}' ORDER BY dong_id asc, ho_name + 1 asc";
           
            // $ho_rows = sql_fetch($ho_sqls);

            // if($_SERVER['REMOTE_ADDR'] != ADMIN_IP){
            //     if($ho_rows['cnt'] != $count_sum){
            //         echo "등록된 호수와 고지서의 호수의 갯수가 일치하지 않습니다.<br>확인 후 다시 업로드해주세요.<br><br>";
            //         echo "등록된 호수 : ".$ho_rows['cnt']."개<br>";
            //         echo "고지서 호수 : ".$count_sum."개<br>";
    
            //         exit;
         
            //     }
            // }

            

            // print_r2($groupKey);
            

            //exit;
           
             // 열 기준으로 모든 셀이 빈값인 열 제거
             $dongIdx = 0;
             $html .= "<input type='hidden' name='excel_type' value='".$excel_type."' />";
             $html .= "<input type='hidden' name='file_name' value='".$filename."' />";
             $html .= "<input type='hidden' name='groupedCnt' value='".count($groupedData)."' />";
             foreach ($groupedData as $groupKey => $rows) {
                 // 열 제거를 위한 필터링
                 $columnsToKeep = [];
                 $rowCount = count($rows);

                 $dongs = str_replace("동", "", $groupKey);
                $ho_sql_add = "SELECT ho.ho_name FROM 
                                a_building_ho as ho 
                                LEFT JOIN a_building_dong as dong on ho.dong_id = dong.dong_id
                                WHERE dong.building_id = '{$building_id}' and dong.dong_name = '{$dongs}' and ho.is_del = 0 ORDER BY ho.dong_id asc, ho.ho_name + 1 asc";
                echo $ho_sql_add.'<br>';
                $ho_res_add = sql_query($ho_sql_add);

                $ho_arr = array();
                while($ho_row_add = sql_fetch_array($ho_res_add)){
                    $ho_names = str_replace(" ", "", $ho_row_add['ho_name']);
                    array_push($ho_arr, $ho_names);
                }
 
                 // 열의 인덱스를 확인하여 모든 셀이 비어 있는 열을 찾음
                 for ($colIndex = 0; $colIndex < count($rows[0]); $colIndex++) {
                     $isColumnEmpty = true;
                     foreach ($rows as $row) {
                         if (!empty($row[$colIndex])) {
                             $isColumnEmpty = false;
                             break;
                         }
                     }
                     if (!$isColumnEmpty) {
                         $columnsToKeep[] = $colIndex;
                     }
                 }
 
                 // 필터링된 열만 유지
                 $filteredRows = [];
                 foreach ($rows as $rIndex => $row) {
                    $filteredRow = [];
                
                    foreach ($columnsToKeep as $colIndex) {
                        $value = $row[$colIndex];
                
                        // 첫 번째 행(동 호)일 때만 "(입)" 제거
                        if ($rIndex === 0) {
                            // 괄호 전까지만 추출
                            $value = preg_replace('/\(.*?\)/', '', $value);
                            // 공백 제거
                            $value = trim($value);
                        }
                
                        $filteredRow[] = $value;
                    }
                
                    $filteredRows[] = $filteredRow;
                }
 
                //  print_r2($ho_arr);
                //  print_r2($filteredRows[0]);

                 //엑셀에는 호수가 있지만 실제 등록된 호수가 없다면 삭제먼저
                 $toRemove = array_diff($filteredRows[0], $ho_arr);

                 // 삭제하면 안 되는 값 목록
                $excludeArr = ['동 호', '동합계', '전체합계'];

                // 제외 리스트에 포함된 값은 제거 대상에서 빼기
                $toRemove = array_diff($toRemove, $excludeArr);
                 
                //filteredRows 에서 해당 값들 삭제
                 foreach ($toRemove as $removeVal) {
                    $rm_idx = array_search($removeVal, $filteredRows[0]);
                  
                    if ($rm_idx !== false) {
                        for($hh = 0; $hh < count($filteredRows); $hh++){

                            unset($filteredRows[$hh][$rm_idx]);

                            // $filteredRows[$hh] = array_values($filteredRows[$hh]);
                            //echo $filteredRows[$hh][$rm_idx].'<br>';
                           
                            //echo '---<br>';
                        }
                    }
                    
                 }


                foreach ($filteredRows as &$row) {
                    $row = array_values($row);
                }
                unset($row);

                //  $otherArray = array_values($otherArray);
                
                 //등록된 호수는 있는데 엑셀에 없다면 추가해주기
                //  print_r2($filteredRows[0]);
                 //호수를 비교
                 $ho_diff = array_diff($ho_arr, $filteredRows[0]);

                
                // print_r2($ho_diff);
                //  exit;
                // print_r2($filteredRows[0]);
                // print_r2($ho_diff);

                 //다른 값이 있을 때..
                 if(count($ho_diff) > 0){
                    
                    $merged = array_merge($filteredRows[0], $ho_diff);

                    // print_r2($merged);
                    // sort($merged, SORT_NUMERIC); // 호수 기준 정렬

                    usort($merged, function($a, $b) {

                        $priority = [
                            '동 호'   => -1000,
                            '동합계'  => 1000,
                            '전체합계' => 1100,
                        ];
                    
                        $pa = $priority[$a] ?? 0;
                        $pb = $priority[$b] ?? 0;
                    
                        if ($pa !== $pb) return $pa - $pb;
                    
                        // B동 여부 (이제 - 붙은 것도 허용)
                        $a_is_b = preg_match('/^B\d+(-\d+)?$/', $a);
                        $b_is_b = preg_match('/^B\d+(-\d+)?$/', $b);
                    
                        if ($a_is_b && !$b_is_b) return -1;
                        if (!$a_is_b && $b_is_b) return 1;
                    
                        // 🔹 숫자 + 서브번호 분리
                        preg_match('/(\d+)(?:-(\d+))?/', $a, $ma);
                        preg_match('/(\d+)(?:-(\d+))?/', $b, $mb);
                    
                        $a_main = isset($ma[1]) ? (int)$ma[1] : 0;
                        $b_main = isset($mb[1]) ? (int)$mb[1] : 0;
                    
                        $a_sub = isset($ma[2]) ? (int)$ma[2] : 0;
                        $b_sub = isset($mb[2]) ? (int)$mb[2] : 0;
                    
                        $a_floor = intdiv($a_main, 100);
                        $b_floor = intdiv($b_main, 100);
                    
                        // B동은 층 내림차순
                        if ($a_is_b && $b_is_b) {
                            if ($a_floor !== $b_floor) return $b_floor - $a_floor;
                        } else {
                            // 일반 호수는 층 오름차순
                            if ($a_floor !== $b_floor) return $a_floor - $b_floor;
                        }
                    
                        // 같은 층이면 "호수" 비교
                        $a_room = $a_main % 100;
                        $b_room = $b_main % 100;
                    
                        if ($a_room !== $b_room) return $a_room - $b_room;
                    
                        // 🔥 서브호수 비교 (201 → 201-1 → 201-2)
                        return $a_sub - $b_sub;
                    });

                    print_r2($merged);

                    $newIndexes = [];

                    foreach ($ho_diff as $val) {
                        $insertIndex = array_search($val, $merged, true);
                        
                        $newIndexes[] = $insertIndex;
                        // echo $insertIndex.'-'.$val.'<br>';
                    }

                    // print_r2($newIndexes);

                    $filteredRows[0] = $merged;

                    for($hh = 1; $hh < count($filteredRows); $hh++){


                        foreach($newIndexes as $n_idx){
                            array_splice($filteredRows[$hh], $n_idx, 0, '');
                        }
                        //echo $filteredRows[$hh][$rm_idx].'<br>';
                       
                        //echo '---<br>';
                    }
                 }

                //  print_r2($filteredRows);

                //  exit;
 
                 // 필터링된 데이터를 출력
                 $groupKey = isset($groupKey) ? $groupKey : 'defaultGroup'; // $groupKey 초기화
                 $html .= "<div class='sub_table_inner_wrapper'>";
                 $html .= "<h4 class='sub_table_labels'>동: $groupKey</h4>";
                 $html .= "<input type='hidden' name='groupKey[]' value='" . htmlspecialchars($groupKey) . "' />";
                 $html .= "<div id='sub_table_inners${dongs}' class='sub_table_inner scroll-wrapper'>";
                 $html .= "<table class='sub_table sub_table_ver2'>";
                 $idx = 0;
                 foreach ($filteredRows as $row) {
                     
                     $html .= '<tr>';
 
                     $rowString = implode('|', array_map('htmlspecialchars', $row));
                     foreach ($row as $cell => $vals) {
                         $html .= '<td>' . htmlspecialchars($vals) . '</td>';
                     }
 
                     $html .= "<input type='hidden' name='row_data".$dongIdx."[]' value='" . $rowString . "' />";
 
                     $html .= '</tr>';
                     $idx++;
                 }
                 $html .= '</table>';
                 $html .= '</div>';
                 $html .= '</div>';
 
                 // 필터링된 데이터를 출력
                 // echo "<h4>Group: $groupKey</h4>";
                 // echo '<table>';
                 // foreach ($filteredRows as $row) {
                 //     echo '<tr>';
                 //     foreach ($row as $cell) {
                 //         echo '<td>' . htmlspecialchars($cell) . '</td>';
                 //     }
                 //     echo '</tr>';
                 // }
                 // echo '</table>';
 
                 $dongIdx++;
             }
             echo $html.'<br>';
            exit;

            // 부모 창으로 데이터 전달
            echo "<script>
            window.opener.showExcelData(" . json_encode($html) . ");
            window.close();
            
             var btn = window.opener.document.getElementById('btn_submit_bill');
            if (btn) {
                btn.style.display = 'inline-block'; // 또는 'block', 'inline' 등 적절히
            }
        </script>";
        exit;
        echo $html;

        } catch (Exception $e) {
            echo 'Error loading file: ' . $e->getMessage();
        }
    }
 }

 exit;

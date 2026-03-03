<?php
include_once('./common.php');
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>고정 헤더 + 고정 첫 컬럼 테이블</title>
<style>
  /* 래퍼: 가로/세로 스크롤 허용 */
  .table-wrapper {
    width: 95%;
    height: 500px; /* 예시 높이: 필요에 따라 조정 */
    margin: 20px auto;
    overflow: auto;
    border: 1px solid #ddd;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
  }

  table {
    border-collapse: separate; /* sticky와 함께 안전 */
    border-spacing: 0;
    width: max-content; /* 콘텐츠 너비에 따라 가로 스크롤 */
    min-width: 100%;
    table-layout: fixed; /* 열 고정 너비를 사용하려면 유지 */
    font-family: Arial, Helvetica, sans-serif;
  }

  th, td {
    padding: 8px 12px;
    border: 1px solid #eee;
    min-width: 120px; /* 각 열 최소 너비 (조정 가능) */
    box-sizing: border-box;
    background: white;
    white-space: nowrap; /* 텍스트가 줄바꿈 되는걸 막고 가로스크롤 유도 */
    text-align: left;
  }

  /* 헤더 고정 */
  thead th {
    position: sticky;
    top: 0;
    background: #f8f9fa;
    z-index: 3; /* 첫 컬럼보단 위에 있도록 적당한 값 */
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
  }

  /* 첫 컬럼 고정 (th 또는 td) */
  /* 첫 번째 셀에만 left 고정 적용 */
  /* nth-child는 컬럼 인덱스 기준 (1번째 컬럼) */
  th:first-child,
  td:first-child {
    position: sticky;
    left: 0;
    z-index: 2; /* 헤더의 첫 셀(겹침) 처리 위해 조정 */
    background: #fff; /* 배경색을 줘야 아래 콘텐츠가 보이지 않음 */
    box-shadow: 2px 0 4px rgba(0,0,0,0.03);
  }

  /* 좌상단 헤더(헤더 + 첫열 겹치는 셀)는 더 높은 z-index */
  thead th:first-child {
    z-index: 5;
    background: #e9ecef;
  }

  /* 가독성용 줄무늬 */
  tbody tr:nth-child(odd) td { background: #fff; }
  tbody tr:nth-child(even) td { background: #fbfbfb; }

  /* 반응형: 모바일에서 첫열을 작게 보이게 (옵션) */
  @media (max-width: 600px) {
    th, td { min-width: 100px; padding: 6px 8px; }
  }
</style>
</head>
<body>
<?php
if($_SERVER['REMOTE_ADDR'] != ADMIN_IP) {
    echo '접근불가';
    exit;
}
?>
<div class="table-wrapper" id="tableWrapper">
  <table>
    <thead>
      <tr>
        <th>종목</th>
        <th>현재가</th>
        <th>전일비</th>
        <th>등락률</th>
        <th>거래량</th>
        <th>시가총액</th>
        <th>PER</th>
        <th>PBR</th>
        <th>52주최고</th>
        <th>52주최저</th>
        <th>섹터</th>
        <th>메모</th>
      </tr>
    </thead>
    <tbody>
      <!-- 예시 데이터 여러 줄 -->
      <!-- 실제로는 서버에서 반복문으로 생성 -->
      <tr><td>삼성전자</td><td>74,000</td><td>-500</td><td>-0.67%</td><td>12,345,678</td><td>460조</td><td>8.4</td><td>1.5</td><td>80,000</td><td>60,000</td><td>반도체</td><td>-</td></tr>
      <tr><td>SK하이닉스</td><td>120,500</td><td>+2,000</td><td>+1.69%</td><td>8,765,432</td><td>80조</td><td>6.1</td><td>0.9</td><td>130,000</td><td>95,000</td><td>반도체</td><td>-</td></tr>
      <!-- 반복 예시로 다수 행 생성 -->
      <!-- 아래는 스크롤 테스트용으로 더 많은 행 추가 -->
      <script>
        const tbody = [];
      </script>
      <!-- JS로 행을 더 추가 (테스트 목적) -->
      <!-- 실제 사용에서는 서버에서 HTML 생성 or JS로 렌더링 -->
      <script>
        (function addRows(){
          const tBodyEl = document.currentScript.previousElementSibling; // 이전 tbody
          // 실제 DOM에서 tbody 노드를 찾기:
          const tbodyNode = document.querySelector('tbody');
          for(let i=0;i<40;i++){
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>종목${i+1}</td>
              <td>${(1000 + i*10).toLocaleString()}</td>
              <td>${(i%2===0?'+':'-')}${Math.floor(Math.random()*1000)}</td>
              <td>${(Math.random()*4-2).toFixed(2)}%</td>
              <td>${(Math.floor(Math.random()*1000000)).toLocaleString()}</td>
              <td>${(Math.floor(Math.random()*1000))}억</td>
              <td>${(Math.random()*20).toFixed(2)}</td>
              <td>${(Math.random()*3).toFixed(2)}</td>
              <td>${(1000 + Math.floor(Math.random()*500)).toLocaleString()}</td>
              <td>${(500 + Math.floor(Math.random()*400)).toLocaleString()}</td>
              <td>섹터${(i%5)+1}</td>
              <td>메모${i+1}</td>
            `;
            tbodyNode.appendChild(tr);
          }
        })();
      </script>
    </tbody>
  </table>
</div>

</body>
</html>

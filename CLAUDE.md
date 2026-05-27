# 반상회(Bansanghoe) 프로젝트 컨텍스트
> 이 파일은 Claude Code 작업 시작 전 반드시 읽고, 작업 완료 후 변경사항을 업데이트한다.

---

## 📁 GitHub 저장소 (3개)
| 저장소 | 설명 | URL |
|--------|------|-----|
| `bansanghoe-web-admin` | 반상회 **관리자 웹** (PHP/Gnuboard) | https://github.com/yumi-kim-79/bansanghoe-web-admin |
| `bansanghoe-manager-app` | 반상회 **매니저 앱** (React Native) | https://github.com/yumi-kim-79/bansanghoe-manager-app |
| `bansanghoe-app` | 반상회 **사용자 앱** (React Native WebView) | https://github.com/yumi-kim-79/bansanghoe-app |

> ### GitHub raw 파일 읽기 방법
> ```
> https://raw.githubusercontent.com/yumi-kim-79/{저장소명}/main/{경로}/{파일명}
> ```
> 예시:
> ```
> https://raw.githubusercontent.com/yumi-kim-79/bansanghoe-web-admin/main/mobile/css/style.css
> ```
> ⚠️ 작업 전 반드시 raw URL로 fetch하여 최신 파일 확인 후 수정할 것

---

## 🖥️ 서버 정보
| 항목 | 내용 |
|------|------|
| 서버 | NCloud, Rocky Linux, Apache, MariaDB/MySQL, PHP |
| 서버 IP | `223.130.156.223` |
| 프레임워크 | Gnuboard (PHP) |
| DNS | NCloud (`ns1-1.ns-ncloud.com`, `ns1-2.ns-ncloud.com`) |

### 환경별 경로
| 환경 | URL | 서버 경로 | DB |
|------|-----|-----------|-----|
| **운영** | `smtm2017.com` | `/var/www/html/` | `sinbansang` |
| **테스트** | `test.smtm2017.com` | `/var/www/html_test/` | `bansanghoe` |
| **어드민** | `smtm2017.com/adm/` | - | - |

---

## 📱 프로젝트 구조

### bansanghoe-web-admin (관리자 웹)
- PHP/Gnuboard 기반 백엔드
- 어드민 패널: `smtm2017.com/adm/`
- 모바일 UI: PHP (WebView에서 렌더링)

### bansanghoe-manager-app (매니저 앱)
- React Native WebView 래퍼
- SM매니저 앱 — 관리자/매니저용
- 동일 인프라 패턴

### bansanghoe-app (사용자 앱)
- React Native WebView 래퍼
- 입주민 사용자용
- UI 로직은 PHP (네이티브 스크린 없음)

---

## 🔑 핵심 규칙 & 주의사항

### DB 테이블명 (정확한 이름)
```
a_building_ho        ← ho_id 포함
a_building_dong
a_building_car
a_building_visit_car
a_member             ← mb_token (FCM 토큰) 포함
```
> ❌ `a_ho`, `a_dong` 등 축약형 사용 금지

### 세션/변수 스코프
- `$_SESSION['users']['ho_id']` → `head.sub.php` 에서 세팅됨
- `$user_building` → `head.sub.php` include된 페이지에서만 사용 가능
- ❌ AJAX 파일에서 `$user_building` 직접 사용 불가

### FCM 호출 패턴
```php
fcm_send($mb_token, $title, $content, $type, $idx, $link_prefix)
// FCM 토큰: a_member.mb_token
// ho_tenant_id: a_building_ho.ho_tenant_id 로 연결
// 키 파일: /var/www/html/sinbansang_fcm_key.json
//          (chmod 600, chown apache:apache)
// ⚠️ 키 파일 절대 GitHub에 커밋 금지
```

### Gnuboard 비밀번호 해싱
- 형식: `sha256:12000:salt:hash`
- `pbkdf2.compat.php` 사용, `_GNUBOARD_` 상수 필요

### AJAX 베스트 프랙티스
```php
header('Content-Type: application/json');
try {
    // FCM 로직 등
} catch (Exception $e) {
    // 에러 처리
}
echo json_encode($result);
exit;
```
- async AJAX + timeout + error handler 필수
- FCM 로직은 반드시 try-catch 감싸기

### 캘린더 UI
- 검색바는 `get_calendar2.php`의 `cal_header_new` div 안에 위치
- ❌ 밖에 배치하면 캘린더 렌더링 깨짐

### Android 빌드 (GitHub Actions)
- npm install: `--legacy-peer-deps` 플래그 필수
- Gradle 서명: env 변수 사용 (❌ `-P` 플래그 사용 금지)
- artifact 업로드: `actions/upload-artifact@v4`

---

## 🚀 개발 워크플로우
```
develop 브랜치 → 자동 배포 → test.smtm2017.com 검증
→ main 브랜치 merge → 자동 배포 → smtm2017.com (운영)
```

## 🖥️ 서버 배포 방식
- GitHub Actions 자동 배포 설정됨 (`.github/workflows/deploy.yml`)
- main 브랜치 push 시 → smtm2017.com `/var/www/html` 자동 배포
- develop 브랜치 push 시 → `/var/www/html_test` 자동 배포
- 배포 방식: `git reset --hard HEAD` → `git pull origin $BRANCH`
- 신규 파일도 자동 배포됨 (git pull 방식)
- 백업: 배포 전 변경파일 `/var/www/html/_backups/날짜시간/` 에 자동 백업
- 서버 직접 접속: `ssh root@smtm2017.com` (또는 IP: `223.130.156.223`)
- 서버 웹 경로: `/var/www/html` (운영), `/var/www/html_test` (테스트)

## ⚠️ 주의사항
- 서버에서 직접 파일 수정 금지 (`git reset --hard` 로 덮어씌워짐)
- 모든 수정은 로컬 → GitHub → 자동배포 순서로 진행
- DB 스키마 변경은 서버에서 직접 mysql 명령으로 실행 필요

## 💾 서버 백업 정책
- **대상**: `/var/www/html/data/` (업로드 파일 — 민원사진, 서명, 게시판 이미지 등)
- **주기**: 매일 새벽 3시 (크론잡)
- **위치**: `/var/backups/bansanghoe/data/YYYYMMDD/`
- **보관**: 30일 (이전 자동 삭제)
- **스크립트**: `/usr/local/bin/bansanghoe_backup.sh`
- **로그**: `/var/log/bansanghoe_backup.log`
- **복구**: `rsync -a /var/backups/bansanghoe/data/YYYYMMDD/ /var/www/html/data/`

## 🩺 디스크 모니터링 (매시간)
- **스크립트**: `scripts/disk_monitor.sh` (저장소) → `/usr/local/bin/disk_monitor.sh` (서버)
- **주기**: 매시간 (`0 * * * *`)
- **임계치**: 디스크 80% 초과 시 `/var/log/disk_alert.log` 기록
- **로그 정리**: `/var/log/httpd/ssl_request_log*`, `ssl_access_log*` 7일 이상 자동 삭제
- **설치 (서버 root, 1회)**:
  ```
  cp /var/www/html/scripts/disk_monitor.sh /usr/local/bin/disk_monitor.sh
  chmod 755 /usr/local/bin/disk_monitor.sh
  (crontab -l 2>/dev/null; echo "0 * * * * /usr/local/bin/disk_monitor.sh >/dev/null 2>&1") | crontab -
  ```

## ⚠️ 서버 실행 금지 명령어

서버에서 절대 실행하지 말 것 (업로드 파일 손실 위험):

- `git reset --hard` (서버에서)
- `git clean -fd` (서버에서)
- `rm -rf /var/www/html/data/`
- `find ... -delete` (data 디렉토리 대상)

이유: `/var/www/html/data/` 는 git 관리 대상이 아닌 실제 업로드 파일 저장소.
`git reset --hard` 실행 시 코드는 복구되지만 data/ 폴더가 삭제될 수 있음.
→ **2026-04-14 민원/서명 이미지 전체 소실 사고 발생 이력 있음.**

로컬(개발 PC)에서의 `git reset --hard`는 정상 사용 가능.

---

## 🎨 CSS 주요 변수
```css
:root {
    --colorMain: #388FCD;
    --colorSub:  #4E5E81;
    --fontColor:  #121212;
    --fontColor2: #666666;
    --fontColor6: #969696;
    --borderColor:  #E4E4E4;
    --borderColor2: #EDEDED;
    --boxColor:  #F7F9FA;
    --boxColor4: #fff;
}
```

### FAQ (온라인 민원) 관련 CSS 클래스
```css
.faq_info_box         /* FAQ 항목 전체 래퍼 */
.faq_span             /* 카테고리 라벨 (font-size: 13px) */
.faq_info_question    /* 질문 클릭 영역 */
.faq_question         /* 질문 텍스트 (font-size: 18px, font-weight: 600) */
.faq_arr              /* 화살표 아이콘 */
.faq_info_answer      /* 답변 텍스트 박스 */
```

---

## ✅ 작업 이력

### 완료된 작업
- [x] Dev/Prod 서버 분리 (test.smtm2017.com / smtm2017.com)
- [x] FCM 푸시 알림 정상화 (Firebase 서비스 계정 키 교체)
- [x] 주차관리 기능 구현 (차량번호 마스킹, 입주민/방문객 뱃지, FCM 요청, 검색 게이팅)
- [x] 캘린더 단지명 검색 구현 (어드민, `building_stx` 파라미터)
- [x] Android 자동 빌드 GitHub Actions 설정 (반상회, SM매니저)

### 진행 중 / 예정 작업
- [ ] 토스페이먼츠 실제 키 교체 (test_ck_placeholder / test_sk_placeholder → 운영키)
- [ ] a_billing_card 테이블 생성 (서버 DB)
- [ ] 운영 서버 서명 이미지 복원 필요 (`/data/file/approval/` 파일 없음, 테스트 서버에는 존재)
  - 원인: 2026-04-14 data/ 소실 사고로 추정
  - 복구: `rsync -av /var/www/html_test/data/file/approval/ /var/www/html/data/file/approval/`

### 최근 완료
- [x] **결재 도장 박스 타임스탬프 한 줄 표시 + 서명 영역 분리 (8 파일 24 span)** (2026-05-28)
  - 문제: 도장 박스 셀 폭(`.sign_boxs_img` width:50% × td) 보다 14px `Y.m.d H:i` 텍스트가 길어 두 줄로 줄바꿈, 서명 이미지와 타임스탬프 영역이 겹침
  - PHP (8 파일 × 3 결재자 = 24 span):
    - `adm/approval_info.php`, `adm/approval_form_ajax1~5.php`, `holiday_reqeust_info.php`, `holiday_request_sample.php`
    - 인라인 `style="position:absolute;bottom:5px;right:5px;color:red;font-size:14px;line-height:1;"` 제거
    - 날짜 포맷 `date('Y.m.d H:i', ...)` → `date('y.m.d H:i', ...)` (2자리 연도 — `26.05.27 17:23`)
  - CSS:
    - `adm/css/admin.css` 에 `.sign_boxs_img, .mng_sign_img_box` 공용 + `.sign_timestamp` 규칙 추가 (11px)
    - `css/default.css` 에 `.sign_boxs_img, .sign_img_box` 공용 + `.sign_timestamp` 규칙 추가 (12px 모바일)
    - 외곽 wrapper: `position:relative; padding-bottom:18px;`
    - img: `max-height: calc(100% - 18px); object-fit: contain;` (서명이 18px 보호 영역 침범 차단)
    - `.sign_timestamp`: `position:absolute; left/right:0; bottom:0; text-align:right; padding:2px 5px; white-space:nowrap; background:#fff; border-top:1px dashed #ddd` — 사인란/날짜란 시각적 분리 (점선 + 흰 배경)
  - 기존 `.sign_boxs_img {width:50%}`, `.mng_sign_img_box {width:50%}` 보존 (다른 의존성 가능)
  - 추가 wrapper 클래스(`.sign_img_box` for `holiday_request_sample.php`) 도 함께 그룹화 — 사용자 사양상 `.sign_boxs_img` 만 명시되었으나 8 파일 일관 UI 의도에 맞춰 grouped selector 로 확장
  - test 검증 → 운영 배포 완료. 결재내역 도장 박스 "26.XX.XX HH:MM" 한 줄 + 점선 분리 정상 동작 확인
  - **디버그 우선 원칙 적용 (회귀 오인 케이스)**: 사용자 보고된 "우상단 도장 박스 + 본문 사라짐" 회귀가 실제로는 **test 환경의 `signOffSample` 파일 동기화 누락(404)** 이었음을 Phase A/B/C 정독 + 사용자 Network/DB 검증으로 확정. 코드 변경 `b1675405` 와 무관 → revert 회피, main 머지 진행. 교훈: 회귀 보고 시 즉시 revert 보다 PHP diff + CSS cascade + 환경 데이터 격리 점검을 먼저 수행하면 잘못된 revert + 재작업 비용 절감
  - 별개 작업 (이번 범위 밖, 기록): test 환경 `/var/www/html_test/data/file/` 전체 동기화 + test DB 2026-04-15 이후 동기화 필요 — 운영팀 별도 작업

- [x] **점검일지 저장 시 활성 계약 서버 재검증 추가 (option 1)** (2026-05-20)
  - 배경: 직전 작업(`e7061674`) 이후에도 오염 데이터 생성 (`inspection_idx=2025`, 2026-05-20 10:34) — 화면 표시 쿼리는 수정되었지만 저장 경로가 클라이언트 hidden 값을 그대로 신뢰. 페이지 캐시 / OPcache lag / 사전 로드 + 지연 제출 / 악의적 수정 등 어떤 경로로든 stale `company_idx` 가 POST 되면 그대로 저장됨
  - `inspection_form_update.php` L8-9 직후 활성 계약 서버 재조회 블록 추가:
    - `inspection_form.php:16` 과 동일 활성 계약 필터 (`is_del=0 AND is_temp=0 AND ct_status=0 AND ct_sdate<=today<=ct_edate`)
    - 결과 0건이면 `die("현재 유효한 계약이 없습니다.")` — 명시적 에러
    - 클라이언트 `$inspection_cmp` 와 서버 활성 계약이 다르면 `error_log("[INSP_CMP_OVERRIDE] ...")` 로깅 후 서버 값으로 덮어씀
  - `$inspection_cmp` 변수가 이후 INSERT (L40) 에서 그대로 사용되므로 in-place override 만으로 모든 흐름 차단
  - UPDATE 분기는 `inspection_cmp` 컬럼을 변경하지 않으므로 영향 없음
  - `sql/inspection_cmp_audit_2026.sql` 신규 — 2026년 오염 데이터 전수 감사 (`suggested_correction`/`suggested_company_idx` 동시 노출, UPDATE 보정 시 사용)
  - ⚠️ **서버 작업 대기**: 운영 DB 에서 `sql/inspection_cmp_audit_2026.sql` 실행 → 결과 보고 → 보정 가능 건만 UPDATE (UPDATE 는 사용자 승인 후 별도 진행)
- [x] **QR 점검 어드민 경로 추가 조사 — 변경 없음** (2026-05-20) — 어드민에 별도 QR 진입 코드 경로 없음(`adm/inspection_print.php` 의 QR 인코딩 URL 이 사용자측 `/inspection_form.php` 가리킴). 운영 DB(`building_id=5, industry_idx=1`) 시뮬레이션: 활성 계약 1건(경성방재 `ct_idx=1405`) 매칭 — 직전 작업(`e7061674`)으로 이미 해결, 사용자 피드백 원인은 캐시/배포 이전 시점 추정. `adm/inspection_missing.php` 의 a_contract 정합성 보강은 별도 작업으로 분리(필요 시 진행).
- [x] **QR 점검 진입 시 활성 계약만 매칭하도록 수정 (해지 업체 표시 차단)** (2026-05-20)
  - `inspection_form.php:16` — `a_contract` 단순 매칭(`building_id + industry_idx` 만 필터)이 만료·해지·임시저장 계약까지 잡아 잘못된 업체가 점검 화면에 노출되던 문제 수정
  - 변경 후 쿼리:
    ```sql
    SELECT * FROM a_contract
    WHERE building_id = ? AND industry_idx = ?
      AND is_del = 0 AND is_temp = 0 AND ct_status = 0
      AND ct_sdate <= CURDATE() AND ct_edate >= CURDATE()
    ORDER BY ct_sdate DESC, ct_idx DESC LIMIT 1
    ```
  - QR 자체는 고정이라 수정 불가 → 서버 측에서 동적으로 활성 계약을 선택하는 방식
  - 매칭 0건일 경우 기존 `if($contract_info)` 가드가 안내 화면으로 분기 (별도 안내 메시지 추가는 별도 작업)
- [x] **결재 타임스탬프 오버레이를 어드민·인쇄 화면에도 적용 (옵션 B)** (2026-05-20)
  - 매니저앱 `holiday_reqeust_info.php` 와 동일 패턴(외곽 div `position:relative` + 자식 `<span class="sign_timestamp">` 인라인 absolute, 빨간 14px, 우측 5px/하단 5px) 을 7개 파일에 일괄 적용
  - 적용 파일 (3 결재자 × 7 파일 = 21개 오버레이):
    - `adm/approval_info.php` — 결재 상세보기 (Type A: `mng_sign_img_box` 래퍼)
    - `adm/approval_form_ajax1.php` (paid_holiday) ~ `adm/approval_form_ajax5.php` (overtime) — 5개 ajax 부분 렌더. ajax1/2/3/5#1 은 기존 `mng_sign_img_box` 래퍼에 `position:relative;display:inline-block;` 추가. ajax4 전부 + ajax5#2/#3 은 래퍼가 없어 `<div class="mng_sign_img_box" style="position:relative;display:inline-block;">` 으로 감쌈
    - `holiday_request_sample.php` — 인쇄 샘플 뷰 (`sign_img_box` 래퍼)
  - 데이터 소스: 각 결재자 SQL `SELECT soi.* FROM a_sign_off_mng_sign as soi ...` 에 `created_at` 이미 포함 → SQL 변경 불필요. `date('Y.m.d H:i', strtotime(...))` + `!empty(...)` 가드
  - **범위 외 (별도 작업 권장)**: `holiday_reqeust_form.php` 와 `holiday_request_sample.php` L445 (신청자 서명 영역) 는 `a_sign_off_img` 라는 **다른 테이블**을 사용 — 결재자(`a_sign_off_mng_sign`) 와 별개. 신청자 서명에도 타임스탬프가 필요하면 해당 테이블의 created_at 컬럼 존재 여부 확인 후 별도 처리 필요
- [x] **품의서 첨부 이미지 리사이즈 한도 720→1920** (2026-05-13)
  - `expense_report_form_update.php`: `resizeImage($dest_file, 720, 720)` (2개소: L203 이미지 변환 분기 + L208 일반 업로드 분기) → `1920, 1920`
  - 모바일 화면에서 영수증/서류 글씨 가독성 개선
- [x] **서명 이미지 해상도 + 포맷 개선 (200→800px, PNG→JPEG 0.92)** (2026-05-13)
  - `holiday_reqeust_info.php`:
    - `saveSign()` L346: `resizeImage(dataURL, 200, ...)` → `resizeImage(dataURL, 800, ...)` — 결재서류 인쇄/확대 시 픽셀화 완화
    - `resizeImage()` L533: `canvas.toDataURL("image/png")` → `canvas.toDataURL("image/jpeg", 0.92)` — 고화질 유지하면서 PNG 대비 용량 절감
    - JPEG 는 투명 비지원이므로 `drawImage` 전에 `ctx.fillStyle="#ffffff"; ctx.fillRect(0,0,w,h)` 로 흰색 배경 prefill (서명 투명 영역이 검정으로 렌더되는 문제 방지)
  - 사용자 요청 중 `signLoad()` 의 200→800 변경 부분은 **변경 대상 없음**: 직전 리팩토링(2026-04-28) 에서 signLoad 가 `resizeImage` 호출 없이 `data.data.signature_data` 원본을 그대로 서버 전송하는 구조로 바뀌어 있음
- [x] **전출 정산 등록 폼 내용(cal_content) 기본 템플릿 자동 입력** (2026-05-12)
  - 대상: `adm/calendar_form.php` (어드민 웹) + `adm/calendar_form2.php` (sm 매니저 모바일)
  - 조건: `$w != 'u'` (신규 등록) AND `$cal_code == 'move_out_settlement'` AND `cal_content` 가 비어있을 때만 적용 → 수정(`w=u`)·기타 카테고리·기존 입력 시 영향 없음
  - 템플릿: 접수일/전출자/전입자/소유주/부동산 연락처 → (빈줄) → 승강기/이사수수료/입금/기타 (▶ 마커, `\n` 줄바꿈)
  - 적용 위치: `<textarea name="cal_content">` 직전에서 PHP 분기로 `$cal_content_val` 결정 후 echo
- [x] **엑셀 처리완료 데이터 소스를 `a_calendar_process` 로 전환** (2026-05-11)
  - `adm/calendar_excel_download.php`:
    - SELECT 에 `LEFT JOIN a_calendar_process AS proc ON proc.cal_idx = cal.cal_idx AND proc.process_date = cal.cal_date` 추가
    - `proc.process_id AS proc_process_id` 컬럼 선택
    - 처리완료 여부: `cal.is_process` → `!empty($row['proc_process_id'])` 로 변경
    - 처리자: `cal.process_id` → `proc.process_id` 기반 `_excel_writer_label()` 호출
  - 사유: 반복일정은 occurrence 별로 `a_calendar_process` 에 개별 row 가 쌓이므로(`cal_idx` + `process_date` 매칭), `a_calendar.is_process` (단일 row 플래그) 보다 정확한 처리 상태를 반영
- [x] **전출 정산 엑셀에 "내용" 컬럼 추가** (2026-05-11)
  - `adm/calendar_excel_download.php`: I 컬럼 "내용" 추가 (헤더 + 데이터 + 너비 50 + WrapText + 좌측 정렬)
  - 데이터: `cal_content` 를 `strip_tags()` + `html_entity_decode(..., ENT_QUOTES, 'UTF-8')` 로 정제 (에디터 HTML 저장 케이스 대비)
  - 모든 범위(`A:H` → `A:I`, `A1:H1` → `A1:I1`, 헤더 스타일, 본문 정렬, 빈 데이터 안내 행) 일관되게 I 까지 확장
  - 사용자 요청 항목 중 (2) is_process "완료/미처리" 표시, (3) is_process=1 시 process_id 매니저 표시는 직전 작업(엑셀 다운로드 신규)에서 이미 동일 사양으로 작성되어 있어 변경 없음
- [x] **전출 정산 엑셀 버튼 URL 을 월 이동에 동기화** (2026-05-11)
  - `adm/calendar_list.php`: 엑셀 다운로드 anchor 에 `id="excel_download_btn"` 부여
  - `moveCal(year, month, type, calcode)` 진입 시점에 `excel_download_btn.href` 를 현재 년/월로 재작성 (`encodeURIComponent` 안전화)
  - 모든 월 이동 경로(년/월 드롭다운, 캘린더 prev/next, 초기 로드)가 `moveCal()` 단일 진입점을 거치므로 한 곳 수정으로 전 경로 커버
- [x] **전출 정산 캘린더 월별 엑셀 다운로드 추가** (2026-05-11)
  - `adm/calendar_list.php`: `cal_code='move_out_settlement'` 일 때 상단 액션바에 "엑셀 다운로드" 버튼 노출 (현재 선택된 `year`/`month` 그대로 전달)
  - `adm/calendar_excel_download.php` 신규: `GET cal_code, year, month` 파라미터, `a_calendar` JOIN `a_mng` + `a_building` 으로 월 단위 조회 후 PhpSpreadsheet 로 `.xlsx` 출력
  - 컬럼: 번호 / 날짜 / 단지명 / 제목 / 작성자 / 담당자(부서+이름) / 처리자 / 처리완료
  - 작성자·처리자 표시 규칙은 `inc/get_schedule.php` 와 동일: `wid='admin'` → "신반상회", 그 외 매니저는 `get_manger()` → 부서 + 이름
  - `mng_department = '-1'` → "전체", 그 외 → `get_department_name()` 으로 부서명 lookup
  - 파일명: `신반상회_{카테고리명}_YYYYMM.xlsx` (UTF-8 filename* 인코딩, IE/Edge 폴백 포함)
  - 일정 0건이면 안내 행 출력
- [x] **단지 담당자 설정 화면에 전체선택 체크박스 추가** (2026-05-04)
  - `adm/building_mng_add.php` 담당자 설정 섹션:
    - 미배정 담당자 / 배정된 담당자 패널 헤더 각각에 "전체선택" 체크박스 추가 (`#select_all_unassigned`, `#select_all_assigned`)
    - JS `toggleSelectAll(listId, isChecked)`: 해당 리스트의 `.manager_item` 체크박스를 일괄 토글, **검색 필터로 가려진 항목(`style.display="none"`)은 제외**
    - `moveManagers()` 호출 후 양쪽 전체선택 체크박스 자동 해제 (이동된 항목 처리 후 일관 상태)
    - CSS: `.manager_panel_title` flex 레이아웃 (제목 좌측, 전체선택 우측), `.select_all_label` 스타일 신규
- [x] **검침/단지/세대 통합 API 재작성 — `api/meter_api.php`** (2026-04-29)
  - 배경: 서버에만 존재하던 `meter_api.php` 가 git clean 으로 소실 → 본 저장소에 신규 커밋
  - 3 액션 지원:
    - `?action=buildings`: `a_building` (`is_del=0 AND is_use=1`) → 응답 `[{building_id, building_name, address, total_units}]` (`address` 는 `building_addr` + `building_addr2` concat, `total_units` 는 `a_building_ho` COUNT 서브쿼리)
    - `?action=units&building_id=N`: `units_api.php` 와 동일 페이로드 (`move_in_date` 포함)
    - `?action=meter&building_id=N&year=YYYY&month=M[&type=electro|water]`: 당월·전월 `a_meter_building.mr_idx` 조회 후 `a_meter_reading` LEFT JOIN 으로 `prev_value/curr_value/usage` 산출. 1월 요청 시 전월은 전년 12월로 자동 처리. 타입 미지정 시 `electro` 기본
  - PDO 직접 연결 + prepared statement, CORS 헤더 동일 패턴
  - `docs/CONTEXT.md` API 표 + `CHANGELOG.md` 갱신
- [x] **세대(Units) 조회 API 신규 — `api/units_api.php`** (2026-04-29)
  - 신규 엔드포인트 `GET /api/units_api.php?action=units&building_id=N`
  - 응답: `[{ ho_id, dong_id, dong_name, ho_name, ho_owner, ho_size, move_in_date }, ...]`
  - `move_in_date` 는 `a_building_ho.ho_tenant_at` 매핑. 빈값 또는 `'0000-00-00'` 은 JSON `null` 로 변환
  - 정렬: `dong_name ASC, ho_name ASC`
  - `is_del = 0` 필터, `LEFT JOIN a_building_dong` 으로 동명 조인
  - 기존 `api/building_settings_api.php` 와 동일 PDO 직접 연결 패턴 (Gnuboard `_common.php` 의존 없음, prepared statement)
  - 본 저장소에는 `meter_api.php` 가 없어 (서버 전용) 합치는 대신 신규 파일로 분리
  - `docs/CONTEXT.md` API 표 + `CHANGELOG.md` 갱신
- [x] **noti8(점검일지) 신규 카테고리 + 잔여 게이트 정책 결정** (2026-04-28)
  - 매핑 정책 결정:
    - 점검일지 작성 → 매니저 = **noti8 신규 컬럼** (전용 카테고리)
    - 점검일지 승인 → 입주민 = noti2 (공문/공지 기존 카테고리에 흡수)
    - 이동 주차 요청 → 입주민 = noti5 (민원 카테고리에 흡수, 일대일 알림 성격)
  - `sql/add_noti8_column.sql` 신규: `ALTER TABLE a_member ADD COLUMN noti8 tinyint(2) NOT NULL DEFAULT 1;` + 동일 ALTER on `g5_member`
  - `notification_setting.php`:
    - `$noti8` 변수 초기화 + sm 타입 분기에서 `$member['noti8']` 읽어오기
    - sm 전용 "점검일지" 토글 `<li>` 추가 (`name="noti8"`, `id="switch8"`, 품의서 다음 위치)
  - `notification_setting_ajax.php` 화이트리스트에 `noti8` 추가
  - `inspection_form_update.php`: SELECT 에 `mb.noti8` 추가, 게이트 `&& $mng_row['noti8']`
  - `adm/inspection_status_change_ajax.php`: SELECT 에 `mem.noti2` 추가, 게이트 `&& $ho_row['noti2']`
  - `parking_move_request_ajax.php`: SELECT 에 `mem.noti5` 추가, 게이트 `&& $noti5`
  - ⚠️ **서버 DB 작업 필요**: `sql/add_noti8_column.sql` 을 운영(`sinbansang`) + 테스트(`bansanghoe`) DB 양쪽에서 실행 필요
- [x] **FCM noti 카테고리 매핑 정합성 수정 (5개 파일)** (2026-04-28)
  - 직전 감사에서 발견된 잘못된/누락 게이트 일괄 정정. 매핑 표(2026-04-23 정책): noti1 사내결재 / noti2 게시판(사용자) / noti3 캘린더 / noti4 전출 / noti5 민원(사용자) / noti6 품의서·민원(매니저)·게시판(매니저).
  - `sm_complain_info_answer_update.php:37`: `$mem_row['noti3']` → **`noti5`** (민원답변→사용자, 카테고리 정정)
  - `adm/approval_form_check.php:186, 214`: 다음 결재자 cascade 푸시 게이트에 `&& $sign_off_id_info['noti1']` 추가 — `holiday_reqeust_info_sign_ajax.php` 와 동일 패턴 정합화
  - `adm/expense_enforce_change.php:47`: `&& $enforce_info['noti6']` 추가 (품의서 시행자 등록)
  - `expense_report_form_update.php:96`: `&& $ex_approver1_info['noti6']` 추가 (품의서 결재자 푸시)
  - `expense_report_info_enforce_change.php:46`: `&& $enforce_info['noti6']` 추가 (시행자 변경)
  - `cron_calendar.php:59`: `&& $mem['noti3']` 추가 (반복일정 cron 알림)
  - 잔여 정책 결정 필요 항목(점검·주차이동요청·신규가입 등)은 별도 작업
- [x] **민감 키 파일 git 트래킹 제거 + main → develop 백머지** (2026-04-28)
  - `.gitignore` (develop): `sinbansang_fcm_key.json`, `*firebase-adminsdk*.json`, `sinbansang-key.pem` 패턴 추가 (main 에는 앞 두 개만 있던 상태에서 동기화)
  - `git rm --cached sinbansang_fcm_key.json sinbansang-key.pem` → develop 인덱스에서 제거 (로컬/서버 파일은 유지)
  - main 단독 커밋 백머지: `a90e6abb` (FCM 키 트래킹 제거), `02120ff2` (FCM gitignore), `2d3eab27` (복구 이미지 매칭 스크립트 — `adm/file_recover_match.php` 신규 진입) 등이 develop 에 병합됨
  - 머지 충돌은 `.gitignore` 한 곳 (develop 의 `sinbansang-key.pem` 라인 vs main 미보유) → develop superset 으로 해결
  - ⚠️ **남은 보안 작업**: git 히스토리에는 키 파일이 그대로 남아있음. FCM 서비스 계정 키 + Apache SSL 키 즉시 로테이션 + (필요 시) `git filter-repo`/BFG 로 히스토리 재작성 권장
- [x] **서명 타임스탬프 오버레이 위치/색상 표준화** (2026-04-28)
  - `holiday_reqeust_info.php` 세 결재자 영역(`.sign_boxs_img1/2/3`) 의 `<span class="sign_timestamp">` 인라인 스타일:
    - `right:8px;bottom:4px;color:#ff0000;` → `bottom:5px;right:5px;color:red;`
    - `font-size:14px;line-height:1;` 유지
  - `addTimestampToSignature()` 함수 / saveSign·signLoad 의 호출은 직전 작업(2026-04-28)에서 이미 제거됨
- [x] **서명 타임스탬프를 canvas 합성에서 HTML 오버레이로 전환** (2026-04-28)
  - 배경: 합성 방식은 (a) DB created_at 이 아닌 클라이언트 현재 시각이 박혀 일관성 부족, (b) 동일 서명을 재불러올 때 시간이 매번 갱신되어 변조 우려, (c) 합성/마스킹 시각적 부자연스러움
  - `holiday_reqeust_info.php`:
    - `saveSign()`: `addTimestampToSignature()` 제거 → 원본 `signaturePad.toDataURL()` → `resizeImage()` 만 거쳐 서버 전송 (타임스탬프 미합성)
    - `signLoad()`: `addTimestampToSignature()` 제거 → `imgSRc` 직접 `<img>` 미리보기, `data.data.signature_data` 를 그대로 서버 전송 (PHP 인라인 echo 폐기)
    - `addTimestampToSignature()` 함수 정의 자체 삭제
    - 세 결재자 서명 표시 영역(`.sign_boxs_img1/2/3`):
      - 외곽 div 에 `style="position:relative;"` 부여
      - `<img>` 다음에 `<span class="sign_timestamp" style="position:absolute;right:8px;bottom:4px;color:#ff0000;font-size:14px;line-height:1;">` 로 `a_sign_off_mng_sign.created_at` 을 `Y.m.d H:i` 포맷으로 오버레이
      - SQL 은 기존 `SELECT soi.*` 그대로(이미 created_at 포함) → 추가 쿼리 변경 없음
  - 결과: 서버 저장본은 깨끗한 원본 서명, 화면에는 결재 시점(DB 기록)이 표시되어 변조 불가 + 페이지 새로고침 시 동일 시각 유지
- [x] **서명 워터마크 합성 방식을 별도 하단 띠로 변경** (2026-04-28)
  - `holiday_reqeust_info.php` `addTimestampToSignature()`:
    - 캔버스 크기를 `width = img.width`, `height = img.height + 30` 으로 확장
    - 원본 서명은 `(0, 0)` 위치에 그대로 그려서 잘림 없음
    - 추가된 하단 30px 띠는 흰색 배경 (`fillRect(0, img.height, width, 30)`)
    - 그 위에 24px 빨간색 타임스탬프를 우측 정렬로 합성 (우측 8px / 하단 4px 패딩)
    - 이미지 위에 덮어씌우는 방식 폐기 → 서명 글씨가 하단에 길게 내려가도 가려지지 않음
- [x] **서명 워터마크 흰색 마스킹을 텍스트 영역으로 한정** (2026-04-28)
  - `holiday_reqeust_info.php` `addTimestampToSignature()`:
    - 하단 30px 전체 흰색 띠 → `ctx.measureText(stamp).width` 로 텍스트 너비 측정 후 해당 영역(가로 textWidth + 좌우 4px 여유, 세로 fontSize + 상하 4px 여유)만 흰색 사각형으로 마스킹
    - 우측 하단 8px 패딩 유지, 24px 빨간색 타임스탬프 그대로 합성
    - 서명 본체 글씨가 하단 끝까지 길게 내려가도 텍스트 영역 외에는 가리지 않음
- [x] **서명 워터마크 합성 시 하단 30px 흰색 덮어씌움** (2026-04-28)
  - `holiday_reqeust_info.php` `addTimestampToSignature()`:
    - `ctx.drawImage()` 직후 `ctx.fillRect(0, canvas.height - 30, canvas.width, 30)` (흰색)으로 하단 30px 띠 마스킹 추가
    - 그 위에 24px 빨간색 타임스탬프 합성
  - 사유: signLoad 로 불러온 이미지에 이미 박혀있던 작은 타임스탬프와 새 타임스탬프 중복 표시 방지
- [x] **서명 워터마크 폰트 크기 12px → 24px** (2026-04-28)
  - `holiday_reqeust_info.php` `addTimestampToSignature()`: `ctx.font = "12px sans-serif"` → `"24px sans-serif"`
  - 사유: 200px 리사이즈 후 가독성 부족으로 키움
- [x] **저장된 서명 불러오기에도 타임스탬프 워터마크 적용** (2026-04-28)
  - `holiday_reqeust_info.php` `signLoad()`:
    - `/data/file/approval/{fil_name}` 경로(`imgSRc`)를 `addTimestampToSignature(imgSRc, callback)` 에 그대로 전달 → 임시 캔버스에 그려서 우측 하단에 `YYYY.MM.DD HH:MM` 합성한 dataURL 생성
    - 서버 전송용 `sign_dataURL` 을 PHP 변수(`$signature_check_row['signature_data']`) 대신 워터마크 합성된 dataURL 로 교체
    - `$("." + ele).html()` 미리보기도 합성된 dataURL 의 `<img>` 로 표시 (URL 직접 표시 폐기)
    - `addTimestampToSignature()` 의 `img.src` 는 base64 와 동일 출처 URL 양쪽 모두 허용 — 동일 출처 이미지는 캔버스 오염 없이 `toDataURL()` 가능
- [x] **결재 서명 이미지에 타임스탬프 워터마크 추가** (2026-04-28)
  - `holiday_reqeust_info.php` `saveSign()`:
    - `signaturePad.toDataURL()` 결과를 `addTimestampToSignature()` 로 한 번 처리한 뒤 기존 `resizeImage()` 호출
    - 신규 헬퍼 `addTimestampToSignature(base64Str, callback)`: 서명 PNG 를 임시 캔버스에 다시 그린 뒤 우측 하단에 `YYYY.MM.DD HH:MM` 텍스트(12px sans-serif, `#ff0000`, padding 8px)를 합성하여 dataURL 재생성
    - 합성된 dataURL 이 변수 `dataURL` 로 전달되어 미리보기(`$("." + ele).html(...)`) 와 서버 저장(resize 후 200px) 모두에 반영됨
    - `img.onerror` 폴백: 이미지 로드 실패 시 원본 dataURL 그대로 콜백 호출
- [x] **FCM 무효 토큰 자동 정리 + 디스크 모니터링 크론** (2026-04-27)
  - `lib/common.lib.php` `fcm_send()`:
    - `CURLOPT_HEADER, true` → `false` 변경 (응답에 헤더가 섞여 `json_decode` 파싱 실패하던 문제 해결)
    - 실패 시 `error_log()`로 HTTP code + status + errorCode + 응답 일부 기록
    - 응답 `error.details[].errorCode == UNREGISTERED|INVALID_ARGUMENT` 또는 `error.status == NOT_FOUND` 이면 해당 토큰을 `a_member.mb_token`, `g5_member.mb_token` 양쪽에서 `''`로 자동 클리어
    - `curl_getinfo` HTTP code 추적, `curl_error` 메시지 로깅
  - `scripts/disk_monitor.sh` 신규:
    - 매시간 실행, `df -PT` 기준 사용률 80% 초과 마운트 발견 시 `/var/log/disk_alert.log` 기록
    - `/var/log/httpd/ssl_request_log*`, `ssl_access_log*` 중 mtime 7일 초과 파일 자동 삭제
    - 설치: 스크립트를 `/usr/local/bin/`에 복사 후 `0 * * * *` 크론 등록 (CLAUDE.md 디스크 모니터링 섹션 참고)
  - ⚠️ 서버 작업 필요: 크론 1회 등록 (CLAUDE.md "디스크 모니터링" 섹션의 설치 명령 참고)
- [x] **FCM 푸시 noti 매핑 정비 + 결재 순차 발송 통합 배포** (2026-04-23)
  - `adm/bbs_form_update.php`: 게시판 등록 알림 체크 컬럼을 `noti2` → `noti6` 으로 변경 (SELECT 컬럼 + `is_send` 분기 조건 두 곳)
  - `adm/complain_form_update.php` L81: 민원처리 완료 후 **사용자**에게 발송하는 푸시 수신 여부 체크를 `noti3` → `noti5` 로 변경 (매니저에게 발송하는 L151 `noti6` 은 유지)
  - `holiday_reqeust_form_update.php` L277~: 등록 시 1차 결재자(`sign_off_mng_id1`)에게만 푸시 발송 (2026-04-15 의 전체 발송 복원)
  - 순차 cascade(`holiday_reqeust_info_sign_ajax.php` L119-169)는 이미 구현되어 있어 변경 불필요
  - develop → main 머지, 양쪽 원격 push → 운영 자동배포
- [x] **결재 푸시 순차 발송으로 재변경 (매니저앱)** (2026-04-23)
  - 배경: 2026-04-15 에 1/2/3차 전체 발송으로 변경했으나, 순차 결재 워크플로우 일관성을 위해 순차 발송으로 복원
  - `holiday_reqeust_form_update.php` L277~: 등록 시 1차 결재자(`sign_off_mng_id1`)에게만 푸시 발송
  - 2→3차 cascade 는 `holiday_reqeust_info_sign_ajax.php` L119-169 에 이미 구현되어 있음 (변경 불필요)
    - 1차 서명(`sign_off_status=1`) 완료 시 → `sign_off_mng_id2` 에게 푸시
    - 2차 서명(`sign_off_status2=1`) 완료 시 → `sign_off_mng_id3` 에게 푸시
    - 2차/3차 결재자가 비어있으면 `sum_sign >= total_approver` 조건으로 cascade 스킵되어 status='E'(완료)
  - 어드민(`adm/approval_form_update.php`, `adm/approval_form_update2.php`)은 이번 작업 범위 밖
- [x] **전체 알림(noti_all) 토글 develop→main 배포** (2026-04-22)
  - develop 커밋 후 main 머지, 양쪽 원격 push → 자동배포 트리거 (test/운영)
- [x] **전체 알림(noti_all) 토글을 개별 noti와 독립시킴** (2026-04-22)
  - 기존: 전체 알림 ON/OFF 시 개별 noti1~7 값이 일괄 변경됨 → 사용자 개별 설정 덮어씀
  - 개편: noti_all 컬럼을 독립적으로 저장 (개별 noti 값은 건드리지 않음)
  - `notification_setting.php`:
    - `$all_chks` 판정을 `noti_all` 컬럼 기준으로 변경 (기본 ON, `'0'`일 때만 OFF)
    - `switch_all` 클릭 시 `saveNotiSetting('noti_all', ...)` 로 서버 전송
    - `confirmAllOff()`: 개별 `.switch_chk2.change()` 트리거 제거 → noti_all 만 0 저장
    - 전체 알림 ON 시에도 noti_all만 1 저장 (개별 값 유지)
    - `.switch_chk2` click 핸들러(switch_all UI 동기화) 제거 — noti_all 독립
    - `.switch_chk` 광역 change 핸들러 → `.switch_chk2` change 로 한정
    - `saveNotiSetting()` 공통 함수 추출, `switchCancle()` `.attr→.prop` 수정
  - `notification_setting_ajax.php`:
    - 허용 noti 컬럼 화이트리스트(`noti_all, noti1~7`) 추가 — SQL injection 방지 + noti_all 저장 허용
    - 기존 동적 UPDATE 그대로 활용, noti_all 컬럼 추가 필요 (`a_member`, `g5_member`)
  - FCM 발송 파일: 기존 코드가 이미 개별 `notiN` 만 체크하므로 수정 불필요 (검증 완료)
  - ⚠️ 서버 DB 작업 필요: `ALTER TABLE a_member ADD COLUMN noti_all TINYINT(1) DEFAULT 1;`
    `ALTER TABLE g5_member ADD COLUMN noti_all TINYINT(1) DEFAULT 1;`
- [x] **로그인 세션 유지 문제 근본 원인 수정** (2026-04-20)
  - **근본 원인**: `head_sm.php` 자동 로그인에서 `update_auth_session_token()` 미호출
    → `ss_mb_token_key` 세션 미설정 → 다음 요청에서 `check_auth_session_token()` 실패 → 세션 초기화
  - `head_sm.php`: `update_auth_session_token($sm_mb['mb_datetime'])` 추가
  - `head_sm.php`: SQL 문법 오류 수정 (`left join a_mng on mng` → `as mng on`)
  - `head_sm.php`: `$is_member`/`$member` 재로드 추가
  - `lib/common.lib.php`: `goto_url()`에 `session_write_close()` 추가
  - 디버깅 로그 전체 제거 완료 (`[LOGIN_DEBUG]`, `[SESSION_DEBUG]`, `[SM_DEBUG]`, `[SIGN_FCM]`)
- [x] **매니저앱 헤더 반응형 개선 + 아이콘 이모지 변경** (2026-04-20)
  - `head_sm.php`: SMS/검침/점검 버튼 아이콘을 SVG → 이모지(💬📊📋)로 변경, "점검일지" → "점검" 축약
  - `css/default.css`: sm_hd_lnb 패딩/갭 축소, 이모지 스타일 추가, 작은 화면 대응
- [x] **어드민 SMS 발송 방법 카드형 UI 개선** (2026-04-17)
  - `adm/sms_send.php`: API 단체 발송(초록 카드) + 수동 복사 발송(회색 카드) 분리 배치
  - 각 카드에 제목/설명/비용/버튼 포함
- [x] **어드민 SMS 2단계 UI로 전면 개편** (2026-04-17, 운영 배포 완료)
  - `adm/sms_send.php`: 1단계(단지 검색/선택) → 2단계(입주민 목록) 2단계 UI
  - 1단계: 단지명 검색 + 카드형 목록 (단지명, 지역, 입주민 수), 클릭 시 2단계 전환
  - 2단계: 선택 단지 표시 + "단지 다시 선택" 버튼 + 동 필터 + 입주민 검색 + 체크박스 목록
  - `api/sms_recipient_api.php`: `action=buildings` 추가 (단지 목록 검색, 입주민 수 포함)
- [x] **네이버 클라우드 SENS SMS 단체 발송 기능 추가** (2026-04-16, 운영 배포 04-17)
  - `api/ncloud_sms_send.php`: SENS API 연동 (HMAC SHA256 서명, SMS/LMS 자동 구분, 발송 이력 DB 저장)
  - `sql/create_sms_history.sql`: `a_sms_history` 발송 이력 테이블 (서버에서 실행 필요)
  - `sms_send_sm.php`: 발송 방식 선택 UI (개별 순차 발송 / SMS API 단체 발송)
  - `adm/sms_send.php`: 어드민에도 API 단체 발송 버튼 추가
  - `config/ncloud_config.example.php`: 설정 예시 파일 (실제 키는 ncloud_config.php에 별도 생성)
  - `docs/NCLOUD_SENS_SETUP.md`: 설정 가이드
  - `.gitignore`: `config/ncloud_config.php` 추가 (API 키 보호)
  - 사용 전 필요: NCloud SENS 가입 + API 키 발급 + config 파일 생성 + DB 테이블 생성
- [x] **SM매니저앱 applicationId 기반 서버 분기로 전환** (2026-04-15)
  - `bansanghoe-manager-app`: `__DEV__` 제거 → `react-native-device-info` bundleId 기반 분기
  - `.test` 접미사 → `test.smtm2017.com`, 그 외 → `smtm2017.com`
  - Release 빌드에서도 테스트앱이면 테스트 서버 사용
  - develop → main 머지 완료, GitHub Actions 빌드 트리거됨
- [x] **SMS visibilitychange 롤백 → setTimeout 3초 복구** (2026-04-15)
  - visibilitychange 방식 제거 (WebView 환경에서 불안정)
  - setTimeout 3초 간격 안정적 방식으로 복구
  - 발송 중단 버튼, 프로그레스바, 하이라이트, 자동 스크롤 유지
- [x] **SMS 단체문자 전면 개편: 자동 순차 개별 발송** (2026-04-15)
  - 개편 배경: 기존 그룹 분할 방식에서 수신자끼리 전화번호 노출 문제 (개인정보 보호 위반)
  - 제거: 30명 그룹 분할 UI, 번호 복사 버튼, 발송 모드 탭 전체 제거
  - 신규: "자동 순차 발송 시작" 버튼 1개, 개별 sms: URI 순차 호출
  - UX: 실시간 프로그레스바(N/M명+퍼센트) + 현재 발송 대상 표시 + 목록 하이라이트
  - 개인정보: 완전한 개별 SMS 발송, 수신자 비노출, 그룹 메시지 아님
  - 사용법: 대상 체크 → 문자 입력 → "자동 순차 발송 시작" → 문자앱에서 발송만 클릭 → 자동 반복
  - 소요시간: 50명 약 2~3분, 100명 약 5분 (3초 간격)
  - 테스트/운영 서버: 2026-04-15 배포 완료
- [x] **SM매니저앱 환경별 서버 URL 자동 분기** (2026-04-15)
  - `bansanghoe-manager-app` 저장소 develop 브랜치
  - `src/utils/APIConstant.js`: `__DEV__` 기반 test/운영 URL 자동 분기
  - `src/screen/Home.js`: mainURL도 동일하게 자동 분기
  - 개발/테스트 빌드 → `test.smtm2017.com`, 운영 Release → `smtm2017.com`
  - main/develop 브랜치 코드 동일, 수동 URL 변경 불필요
- [x] **테스트 서버 board_info.php URL 수정** (2026-04-15)
  - 문제: 테스트 서버 `board_info.php`가 운영 URL(`https://smtm2017.com`)을 참조 → 모바일 앱이 운영 DB에 연결
  - 해결: 서버에서 `sed -i 's|https://smtm2017.com|https://test.smtm2017.com|g' /var/www/html_test/board_info.php`
  - 영향: 사용자앱/SM매니저앱(테스트)이 `test.smtm2017.com` → `sinbansang_test` DB 정상 연결
  - 주의: 서버 직접 수정 (git 관리 외), 운영 서버는 수정 안 함
- [x] **SMS 번호복사 개선: 문자앱 자동실행 제거 + 30명 초과 그룹 분할** (2026-04-15)
  - `sms_send_sm.php`: 번호 복사 시 sms: URI 자동실행 제거 (복사만 수행)
  - 30명 초과 시 30명씩 자동 그룹 분할 UI + 그룹별 복사 버튼
  - 30명 이하는 단일 그룹 표시, 하단 "전체 번호 복사" 버튼
- [x] **결재서류함 관리자 일괄 삭제 기능 추가** (2026-04-15)
  - `adm/approval_document_list.php`: 관리자(mb_level>=10) 전용 체크박스 + "선택 삭제" 버튼
  - `adm/approval_del_update.php`: 신규 — AJAX 일괄 삭제 (soft delete is_del=1 + 첨부파일 서버삭제)
- [x] **결재 등록 시 1/2/3차 결재자 전체에게 FCM 푸시 발송** (2026-04-15)
  - 기존: 1차 결재자에게만 푸시 발송 → 수정: 모든 결재자에게 발송
  - `holiday_reqeust_form_update.php`: 매니저앱 결재 등록 (+ `$_SERVER['REMOTE']` 오타 수정)
  - `adm/approval_form_update.php`: 어드민 결재 등록
  - `adm/approval_form_update2.php`: 어드민 근무일지 등록
  - 모든 파일에 `noti1` 알림 설정 체크 추가
- [x] **SMS 단지검색 자동완성 + 단지관리 메뉴 SMS 추가** (2026-04-15)
  - `sms_send_sm.php`: select 드롭다운 → 검색 가능한 자동완성 입력창 (단지명/지역명 필터링, 키보드 네비게이션, 선택 뱃지)
  - `sms_send_sm.php`: `building_id` URL 파라미터로 단지 자동 선택 지원
  - `building_mng.php`: 단지 상세 메뉴에 "SMS 단체문자" 항목 추가 → `sms_send_sm.php?building_id=N`
- [x] **SMS 매니저앱 2가지 발송 옵션으로 재작성** (2026-04-15)
  - 문제: 다중 수신자 sms: URI가 iOS/Android에서 첫 번째 번호만 전달됨
  - 옵션1(번호 복사): 전화번호 클립보드 복사 → alert 안내 → 문자앱 열기 (body만 전달)
  - 옵션2(개별 발송): 체크된 대상 리스트에서 한 명씩 "문자 보내기" 버튼으로 개별 sms: URI 호출
  - `sms_send_sm.php`: 모바일 전용 UI 완전 재작성
  - `head_sm.php`: SM매니저 홈 헤더에 SMS 메뉴 아이콘 추가
  - `adm/sms_send.php`: 세미콜론 구분자 변경
- [x] **SMS URI iOS/Android 호환 + 버그 수정** (2026-04-15)
  - iOS `sms:번호&body=내용`, Android `sms:번호?body=내용` 자동 분기
  - `adm/sms_send.php`, `sms_send_sm.php` 양쪽 적용
- [x] **SMS 단체문자 발송 기능 완료** (2026-04-15)
  - `api/sms_recipient_api.php`: mysqli 직접 연결, 환경별 DB 자동 선택 (test→bansanghoe, 운영→sinbansang)
  - `adm/sms_send.php`: 어드민 웹 UI (단지/동 필터, 체크박스, 번호복사, 문자앱 호출)
  - `sms_send_sm.php`: 매니저앱 UI (담당 단지만/관리자 전체)
  - `adm/admin.head.php`: 단지관리 > SMS 단체문자 메뉴 추가
- [x] **SMS 단체문자 발송 기능 추가** (2026-04-15)
  - `api/sms_recipient_api.php`: 단지별/동별 입주민 전화번호 조회 (ho_tenant_hp 우선, ho_owner_hp 폴백)
  - `adm/sms_send.php`: 어드민 웹 UI (단지/동 필터, 체크박스, 문자내용, 번호복사, 문자앱 호출)
  - `sms_send_sm.php`: 매니저앱 UI (모바일 최적화, 담당 단지만/관리자 전체)
  - `adm/admin.head.php`: 단지관리 메뉴에 "SMS 단체문자" 추가 (sub_menu=200900)
  - `head.tit.php`: 매니저앱 타이틀 등록
- [x] **사내용 게시판 목록 일괄 삭제 기능 추가** (2026-04-14)
  - `adm/bbs_list.php`: 관리자만 체크박스 + "선택 삭제" 버튼
  - `adm/bbs_del_update.php`: 신규 — 이미지(bbs_img) + PDF(bbs_pdf) 서버파일/DB 삭제 + soft delete
- [x] **민원 목록 일괄 삭제 기능 추가** (2026-04-14)
  - `adm/complain_list.php`: 관리자(super/mb_level>=10)만 "선택 삭제" 버튼 표시
  - `adm/complain_del_update.php`: 신규 — AJAX 일괄 삭제 (soft delete + 첨부파일 삭제)
  - 첨부파일(complain/complain_answer/complain_add) 서버 파일 + DB 레코드 함께 삭제
- [x] **서버 data 폴더 자동 백업 크론잡 설정** (2026-04-14)
  - 매일 새벽 3시, `/var/www/html/data/` → `/var/backups/bansanghoe/data/YYYYMMDD/`, 30일 보관
- [x] **서버 실행 금지 정책 + deploy.yml 수정** (2026-04-14)
  - `git reset --hard` 서버 실행 금지 문서화 (data/ 손실 사고 이력)
  - deploy.yml: `git reset --hard HEAD` → `git checkout -- .` 변경
- [x] **결재서류 서명 이미지 복원** (2026-04-14)
  - 원인: mkdir recursive 미적용 시절 file_put_contents 실패 → 파일 없음
  - `signature_recover.php` 1회 실행 → base64에서 PNG 복원 → 스크립트 삭제 완료
- [x] **카드 등록/관리 메뉴 "개발 중" 차단** (2026-04-14)
  - `mypage.php`: 링크 → alert("개발 중") + 이동 차단
  - `card_register.php`, `card_register_callback.php`: 직접 접근 시 alert + history.back()
- [x] **첨부파일 이미지 404 문제 해결** (2026-04-13~14)
  - 원인: `@mkdir($path, 0755)` 중간 디렉토리 미생성 → 업로드 실패 → DB 레코드만 생성
  - 수정: 24개 파일 `@mkdir($path, G5_DIR_PERMISSION, true)` recursive 추가
  - 민원/게시판/서명 이미지 저장 경로 전체 수정
- [x] **adm/file_check.php 진단 도구 삭제** (2026-04-13)
- [x] **main→develop 동기화 완료** (2026-04-13)
  - mkdir recursive, alert→die, or die→error_log, FCM try-catch, alert(e)→안전메시지 등 35개 파일
- [x] **민원 저장 500 에러 + [object Object] alert 수정** (2026-04-13)
  - `adm/complain_form_update.php`: `alert()` HTML 함수 → `die(result_data())` JSON 변환 (3개소)
  - `adm/complain_form_update.php`: `move_uploaded_file or die(0)` → `error_log` 변환 (3개소)
  - `adm/complain_form.php` + 9개 어드민 파일: `alert(e)` → `alert("저장 중 오류가 발생했습니다.")`
- [x] **첨부파일 업로드 실패 수정: mkdir recursive 추가** (2026-04-13)
  - 원인: `@mkdir($path, 0755)` — 중간 디렉토리(`/file/`) 없으면 하위 생성 실패, `@`로 에러 무시
  - 수정: 24개 파일에서 `@mkdir($path, G5_DIR_PERMISSION, true)` recursive 플래그 추가
  - 파일 저장 실패해도 DB 레코드만 생성되던 문제 해결
- [x] **첨부파일 이미지 404 원인 파악 및 해결** (2026-04-13)
  - 원인: 서버에서 data 디렉토리 중복 (`/var/www/html/data/data/file/`)
  - 웹 경로 `/data/file/complain/` → 실제 파일 `/data/data/file/complain/` 불일치
  - 해결: 서버에서 심볼릭 링크 `ln -s /var/www/html/data/data/file /var/www/html/data/file`
  - `adm/file_check.php`: 진단 도구 (사용 후 삭제 권장)
  - 경로 구조: 업로드 `G5_DATA_PATH/file/{bo_table}/` → 웹 `/data/file/{bo_table}/`
  - `smtm2017.com/adm/file_check.php` 에서 확인 후 원인 파악 가능
- [x] **민원/게시판 이미지 표시 + 결재 탭 레이아웃 수정** (2026-04-13)
  - `css/default.css`: `.bbs_img_box`에 `min-height:80px` 추가, img `display:block !important`
  - `css/default.css`: `.tab_lnb.ver4 li`를 `calc(100%/3)` → `flex:1`로 변경 (4탭 지원)
- [x] **adm/bbs_form_update.php alert("0") 근본 원인 수정** (2026-04-13)
  - 원인: `move_uploaded_file() or die(result_data(false, 0))` — 빈 Blob 파일 이동 실패 시 에러코드 0 반환
  - 수정: `or die` 제거 → `if(!move_uploaded_file)` + error_log로 변경 (실패해도 계속 진행)
  - 빈 파일 방어: `$filesize > 0 && $filename != 'blob'` 조건 추가
- [x] **adm/bbs_form.php AJAX 성공/에러 콜백 안전화** (2026-04-13)
  - `data.msg` undefined 시 "저장되었습니다." 폴백 메시지
  - error 콜백에 사용자 알림 추가 + btn_submit 재활성화
  - building_info_pop 숨김을 success/error 양쪽에서 보장
- [x] **adm/bbs_form_update.php JSON 응답 오염 수정** (2026-04-13)
  - 원인: `alert()` 함수가 HTML 페이지 출력 → AJAX dataType:json 파싱 실패
  - 수정: `alert("파일 유형")` → `die(result_data(false, "...", []))` (2개소)
- [x] **FCM 호출 안전화: 프로젝트 전체 45개소 try-catch 적용** (2026-04-13)
  - `lib/common.lib.php fcm_send()`: 함수 전체 try-catch, printf 제거, 빈 토큰 조기 반환
  - 루트 14개 파일 + adm/ 16개 파일: 모든 fcm_send 호출에 try-catch 감쌈
  - 원인: FCM 예외/에러 출력이 JSON 응답 앞에 붙어 AJAX 파싱 실패 → alert("0")
- [x] **단지 추가/수정 시 담당자 일괄 선택 기능** (2026-04-13)
  - `adm/building_mng_add.php`: 담당자 설정 섹션 (미배정↔배정 이동 UI, 검색, 체크박스)
  - `adm/building_mng_add_update.php`: manager_ids[] 배열로 a_mng_building 일괄 저장 (soft delete + INSERT/복원)
- [x] **building_settings_api DB 연결 PDO 직접 연결로 변경** (2026-04-13)
  - `api/building_settings_api.php`: `require_once _common.php` 제거 → PDO 직접 연결
  - 모든 SQL을 prepared statement로 변환 (SQL injection 방지)
  - Gnuboard 의존성 완전 제거 → 독립 API 파일
- [x] **단지 담당자 조회 API (a_mng_building 활용)** (2026-04-13)
  - `api/building_settings_api.php`: building_managers(단지별)/building_managers_all(전체) API
  - 기존 `a_mng_building` JOIN `a_mng` + 부서/직급 테이블 활용 (DB 변경 최소화)
  - 연체요율: `a_building.late_fee_rate/late_fee_base` 컬럼만 추가
  - manager_name/phone/email 컬럼 추가 방식 폐기
- [x] **배포 스크립트 git pull 방식으로 수정** (2026-04-13)
  - `.github/workflows/deploy.yml`: curl 개별 파일 다운로드 → `git pull origin $BRANCH`
  - 문제: 머지 커밋 시 신규 파일 누락 (git diff에 있어도 curl로 받을 때 실패)
  - 수정: `git reset --hard HEAD` → `git pull origin $BRANCH` 한 번으로 전체 동기화
  - 백업 기능 유지 (변경 파일 사전 백업)
- [x] **토스페이먼츠 카드 등록 화면 추가 (테스트 모드)** (2026-04-02)
  - `card_register.php`: 카드 등록/관리 화면 (등록카드 표시, 변경, 삭제)
  - `card_register_callback.php`: 토스페이먼츠 빌링키 발급 콜백 (curl로 API 확정)
  - `api/billing_api.php`: 카드 저장/삭제 API (a_billing_card 테이블)
  - `mypage.php`: "카드 등록/관리" 메뉴 추가 (입주민만 표시)
  - `head.tit.php`: 페이지 타이틀 등록
  - 테스트 클라이언트키/시크릿키 사용 (placeholder)
- [x] **CKEditor 5 테스트 페이지 생성** (2026-04-02)
  - 파일: `adm/editor_test.php` (신규), `plugin/editor/cheditor5/imageUpload/upload_ckeditor5.php` (신규)
  - CKEditor 5 v43.3.1 CDN, 한글 언어팩, 이미지 업로드/리사이즈, 글자크기/색상/굵기/기울임/표 삽입
  - 이미지 업로드: `upload_ckeditor5.php` (기존 cheditor5 nonce/보안 인프라 재사용, SimpleUploadAdapter 호환)
  - 기본 글씨체 Arial Black, 기본 글씨 크기 16px, line-height 1.6
  - CSS `!important`로 에디터 영역 기본 스타일 강제 적용
  - `window.ck5SetDataWithStyle(html)`: font-family/font-size 인라인 스타일 제거 후 기본 스타일 div로 감싸서 입력
  - "저장 테스트" 버튼으로 에디터 HTML 내용 alert 출력
- [x] **온라인 투표 템플릿 선택 UI를 인라인 검색 드롭다운으로 변경** (2026-04-02)
  - 파일: `adm/online_vote_form.php`
  - 기존 팝업 + 우측 상단 버튼 제거
  - '관리단 정보' 테이블의 '지역' 행 위에 '투표 템플릿' 행 추가
  - 대분류 드롭다운(전체/의무관리/비의무관리) + 검색 가능한 템플릿 드롭다운 배치
  - 대분류 필터링, label 키워드 검색, 선택 시 title+content 에디터 자동 입력
  - JS에서 에디터 입력 시 `[SM 오프닝]`, `[제안 사유 및 기대효과]` 라벨 제거 + 연속 `<br>` 3개 이상 정리 (`[확인 사항 및 첨부파일]`은 유지)
  - 검색 박스 너비 450px 고정, ✕ 버튼은 검색 박스 우측 상단 모서리에 배치 (right:-10px, top:-10px)
  - 선택 후 ✕ 버튼 표시 → 클릭 시 검색창·투표주제·에디터 내용 모두 초기화, 검색 가능 상태로 복귀
  - 템플릿 선택 시에만 font-family/font-size 인라인 스타일 제거 후 Arial Black/16px div 래핑 (빈 에디터는 기본 설정 유지)
  - CHEditor5 공식 API 사용: `ed_vt_content.replaceContents()` (내용 삽입), `ed_vt_content.outputBodyHTML()` (저장 시 추출)
  - tplClearSelection(): `ed_vt_content.replaceContents('')`
  - fonlinevote_submit(): `ed_vt_content.outputBodyHTML()` → textarea 동기화
- [x] **온라인 투표 템플릿 content에서 [SM 오프닝] 및 [제안 사유 및 기대효과] 제목 텍스트 제거** (2026-04-02)
  - 파일: `adm/online_vote_template_data.php`
  - 209건 모두 제거, `[확인 사항 및 첨부파일]` 제목은 유지
- [x] **QR 인쇄 페이지 전체 디자인 리뉴얼** (2026-04-01)
  - 파일: `adm/inspection_print.php`
  - 헤더: #1a2e4a 배경 + 단지명 + AIVEX 로고, QR 180x180px, 중앙 로고 30x30px (~2.8%)
  - 카드형 레이아웃: CSS Grid 3열, border-radius:10px, box-shadow
  - 3열x3행=9개/페이지로 변경 (A4 여백 최적화), 푸터 추가
  - @media print 동일 적용, print-color-adjust:exact
- [x] **온라인 투표 템플릿 선택 기능 추가** (2026-04-01)
  - `adm/online_vote_form.php`: "투표 템플릿 선택" 버튼 + 팝업 (의무관리/비의무관리 탭)
  - `adm/online_vote_template_data.php`: 의무 104건, 비의무 105건 (label/title/content 구조)
  - 팝업 목록에 `label`(안건명)만 간결하게 표시, 목록 영역 스크롤 지원
  - 선택 시 `title`(투표주제) + `content`(HTML) 에디터 자동 입력
  - JSON으로 데이터 전달 (HTML 특수문자/따옴표 안전 처리), smarteditor/ckeditor/summernote 호환
- [x] **매니저앱 일정 수정 화면에서 삭제 버튼 미표시 수정** (2026-04-01)
  - `head_sm.php`: 삭제 버튼 조건 `$w == 'i'` → `$w == 'i' || $w == 'u'`로 변경
  - 원인: 반복일정은 `get_schedule2.php`에서 `w=u`로 접근하는데, head_sm.php에서 `w=i`만 허용
  - 권한: 본인 등록(wid)/담당자(mng_id)/관리자(mb_level>=10) + 미처리 또는 관리자
- [x] **매니저앱 반복설정 "안함" 저장 안 되는 버그 수정** (2026-04-01)
  - `schedule_add2.php`: JS calendar_submit에서 수정 모드 시 hidden input만 읽던 로직 → 라디오 버튼 우선 읽도록 수정
  - `schedule_add_update2.php`: 반복일정 전체 수정 UPDATE에 `noti_repeat` 누락 → 추가
  - 원인: 수정 모드에서 `$("#noti_repeat").val()`이 hidden input만 참조 + UPDATE 쿼리에 noti_repeat 미포함
- [x] **캘린더 빨간 dot(●) 미표시 수정** (2026-03-30)
  - 파일: `adm/get_calendar2.php`
  - 원인: 월간반복 dot 조건 `$r['cal_date'] <= $startDate` → 같은 달 생성 일정 차단
  - 수정: `$r['cal_date'] <= $endDate`로 변경 (월 마지막일까지 허용)
  - cal_edate 체크에 `!== null` 추가 (PHP DB NULL 안전 처리)
- [x] **캘린더 반복일정 표시 로직 검증 및 중복 제거** (2026-03-30)
  - `calendar_schedule_list2.php`: 중복 제거 로직 추가 (같은 날짜+단지+종류+제목 → 최신 cal_idx 유지)
  - 코드 로직 자체는 cal_edate=NULL인 반복일정이 모든 미래 월에 정상 표시됨을 확인
  - 3월 이후 미표시 원인: DB에 is_del=1 또는 cal_edate가 설정된 데이터 문제 가능성 → DB 확인 필요
- [x] **캘린더 반복일정 this_only 삭제 시 전체 삭제 버그 수정** (2026-03-30)
  - 근본 원인: `cal_idx`가 PK이므로 `WHERE cal_idx=X AND cal_date=Y`는 항상 매칭 → 원본 soft delete → 모든 월 사라짐
  - 수정: 반복일정 this_only는 원본 건드리지 않고 항상 예외 레코드(is_del=1) INSERT
  - `calendar_del_update2.php`, `schedule_add_del2.php` 양쪽 수정
  - `get_calendar2.php`: 캘린더 dot에서도 예외 레코드 제외, cal_edate 체크 추가
- [x] **캘린더 반복일정 삭제 3가지 옵션 (어드민+사용자 양쪽)** (2026-03-30)
  - 어드민: `adm/calendar_form2.php` + `adm/calendar_del_update2.php`
  - 사용자: `schedule_add2.php` + `schedule_add_del2.php` + `head_sm.php`
  - 커스텀 팝업 3가지 옵션: "이 날짜만" / "이 날짜 이후 전체" / "반복 전체" (비반복은 기존 confirm)
  - 삭제 PHP: `del_mode` 분기, 권한 체크, soft delete(is_del=1)
  - 관리자(`mb_level >= 10`) 처리완료 건도 삭제 가능
- [x] **반상회 vs XpERP 항목 비교표 엑셀 생성** (2026-03-30)
  - 파일: `반상회_XpERP_항목비교표.xlsx`
  - 시트1: 항목 비교표 (단지관리 35항목 + 세대관리 20항목)
  - 초록=반상회있음, 노랑=XpERP만있음, XpERP 컬럼은 수동 입력 필요
- [x] **단지관리/세대관리 입력 항목 전체 목록 파악** (2026-03-30)
  - 단지관리(`adm/building_mng_add.php`): 기본정보, 주소(Daum API), 건물정보(공공API 자동조회), 계좌, 보안, 건설사, 메모, PDF첨부(최대4), 관리규약
  - 세대관리(`adm/house_hold_form.php`): 지역/단지/동/호수, 면적, 소유자(+매매일), 입주자(+비밀번호), 세대구성원(최대5명), 등록차량(최대3대), 입퇴실, 메모
- [x] **검침 저장 시 오프라인 체크 추가** (2026-03-30)
  - 파일: `meter_reading_info.php`
  - `navigator.onLine` 체크, 오프라인 시 alert 후 저장 중단
- [x] **검침 페이지 경고 문구 변경** (2026-03-30)
  - 파일: `meter_reading.php`
  - 임시저장 기능 안내 문구로 변경
- [x] **검침 입력 localStorage 임시저장 기능** (2026-03-30)
  - 파일: `meter_reading_info.php`
  - 키: `meter_draft_{building_id}_{year}_{month}_{type}`, 입력 시 실시간 저장
  - 페이지 로드 시 임시데이터 있으면 확인 팝업 후 자동 복원
  - 정상 저장 완료 시(`meter_save` 성공) localStorage 삭제
- [x] **검침(전기/수도) PHP 파일 구조 파악** (2026-03-30)
  - 관리자 10개 + 사용자 5개 = 총 15개 PHP 파일
  - DB: `a_meter_building`(단지별 월별 메타), `a_meter_reading`(세대별 검침값, mr_type: electro/water)
  - 엑셀 업로드/다운로드 PhpSpreadsheet, 이전월값 자동 조회 로직
- [x] **1차 검색 시 PHP→JSON→JS 드롭다운 자동 재세팅** (2026-03-30)
  - 파일: `adm/house_hold_list.php`
  - JSON에 post_id/post_name/building_id/building_name/is_use/dongs 포함
  - 1개 지역+1개 단지: 지역/단지 자동 선택, 동 채움
  - 1개 지역+여러 단지: 지역 자동 선택, 단지 드롭다운에 매칭 목록, 동 합산
  - 여러 지역: 지역 "전체", 단지 드롭다운에 매칭 목록, 동 합산
- [x] **1차 검색 시 지역/단지 드롭다운 자동 선택 및 SQL 필터 반영** (2026-03-30)
  - 1개 단지 매칭: post_id/building_id PHP 변수 자동 설정 → SQL 필터 + 드롭다운 + qstr 모두 반영
  - 여러 단지 매칭: `building_id IN()` 조건으로 필터, 단지 드롭다운에 매칭 단지만 표시
  - 동/호수 컬럼 숫자만 표기
- [x] **해지 단지 "(해지)" 표시 추가** (2026-03-30)
  - 파일: `adm/house_hold_list.php`, `adm/house_hold_list_sch_text.php`
  - 목록 테이블 단지명 컬럼에 `is_use=0`이면 빨간색 "(해지)" 표시
  - 1차 검색 자동완성 드롭다운에도 "(해지)" 표시, 운영 단지 우선 정렬
  - WHERE절 `is_use=1` 필터 제거하여 해지 단지도 목록에 표시
- [x] **세대관리 검색 로직 개선** (2026-03-30)
  - 파일: `adm/house_hold_list.php`, `adm/house_hold_list_sch_text.php`
  - 2차 검색 항상 활성화, 1차=단지명, 2차=상세, 1차+2차=필터링
  - 1차 자동완성 선택 시 동 드롭다운 자동 업데이트 (`building_dong_ajax.php` 활용)
  - 1차 검색 결과 단지의 동 목록을 서버에서 미리 채움 (building_id 미선택 시)
- [x] **세대관리 엑셀 다운로드 양식 개선** (2026-03-30)
  - 파일: `adm/house_hold_list_excel.php`
  - 컬럼 순서를 화면과 동일하게 (번호/지역/단지명/동/호수/면적/소유자/소유자연락처/입주자/입주자연락처/입주일/등록차량/세대구성원/상태)
  - 등록차량/세대구성원: 줄바꿈(`\n`)으로 상세 나열 (기존 건수만 표시 → 실제 내용)
  - 검색 로직 1차/2차 동기화, 정렬순서 화면과 동일하게 통일
- [x] **세대관리 1차/2차 검색 분리** (2026-03-30)
  - 파일: `adm/house_hold_list.php`
  - 1차 검색: 전체/단지명 드롭다운 (기존 지역/단지/동 필터 유지)
  - 2차 검색: 1차 검색 또는 단지 선택 후 활성화, 소유자/입주자/연락처/호수/차량번호 통합 검색 (`stx2` 파라미터)
- [x] **어드민 세대관리 PHP 파일 구조 파악** (2026-03-30)
  - adm/ 세대관리 파일 17개 (목록/검색/엑셀/AJAX), 사용자측 8개, 연동 29개
  - DB: `a_building_ho`, `a_building_household`, `a_building_car`, `a_building_household_history`
  - 엑셀: PhpSpreadsheet 사용 (`house_hold_list_excel.php`, `household_member_list_excel.php`)
- [x] **FAQ 답변 폰트 크기/굵기 수정** (2026-03-25)
  - 파일: `css/default.css`
  - 변경: `.faq_info_answer { font-size: 13px → 18px, font-weight: 400 추가 }`

---

## 💬 작업 요청 템플릿
```
CLAUDE.md 읽고, [작업내용], 완료 후 CLAUDE.md 작업이력 업데이트해줘
```

---

## 📋 Claude Code 작업 시작 체크리스트
```bash
# 1. 이 파일 읽기
cat BANSANGHOE_CONTEXT.md

# 2. 수정 대상 파일 GitHub에서 fetch
curl https://raw.githubusercontent.com/yumi-kim-79/{저장소}/main/{경로}/{파일}

# 3. 수정 적용

# 4. 이 파일 작업이력 업데이트 (완료 항목 [x] 체크, 새 항목 추가)

# 5. 커밋 전 민감정보 확인 (FCM 키 파일 등)
```

---

*최종 업데이트: 2026-04-28*

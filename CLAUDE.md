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
- (없음)

### 최근 완료
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
  - PHP에서 매칭 단지 정보(post_id, building_id, building_name, dongs)를 JSON으로 JS에 전달
  - JS 페이지 로드 시 JSON 데이터로 지역/단지/동 드롭다운 자동 채움+선택
  - 1개 매칭: 지역/단지 자동 선택, 동 목록 채움 / 여러 매칭: 단지 목록+동 합산 표시
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

*최종 업데이트: 2026-03-30*

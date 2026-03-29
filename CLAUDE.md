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
- [x] **세대관리 검색 영역 레이아웃 개선** (2026-03-30)
  - 파일: `adm/house_hold_list.php`
  - 1차/2차 검색을 같은 행에 나란히 배치, 각 검색창 옆에 개별 검색 버튼
  - 라벨 너비 70px 고정으로 상태/입주일/지역/검색 세로 정렬 통일
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

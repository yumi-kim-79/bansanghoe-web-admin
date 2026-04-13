# 반상회 프로젝트 컨텍스트

---

## 서버 / 인프라 구조

| 항목 | 내용 |
|------|------|
| 클라우드 | 네이버 클라우드 플랫폼 (NCP) |
| 서버명 | s1984f14d98f |
| OS | Rocky Linux 9 |
| 웹서버 | Apache / PHP |
| 도메인 | smtm2017.com (운영), test.smtm2017.com (테스트) |
| DB | MySQL, 데이터베이스명: sinbansang |
| 주요 테이블 | a_building (단지) |
| API | https://smtm2017.com/api/building_settings_api.php |

---

## 고지서 ↔ 반상회 연동 구조

- 반상회 DB (MySQL sinbansang) 가 원본 데이터
- 고지서 앱은 반상회 API를 통해 데이터 읽기/쓰기
- 고지서 자체 데이터는 Firebase Firestore (billing-aivex) 에 저장
- 단지 동기화: 반상회 API → Firestore complexes 컬렉션

### API 엔드포인트

| 메서드 | 엔드포인트 | 설명 |
|--------|-----------|------|
| GET | `/api/building_settings_api.php?action=building_settings&building_id=N` | 단지별 담당자/연체요율 조회 |
| GET | `/api/building_settings_api.php?action=building_settings_all` | 전체 단지 담당자/연체요율 목록 |
| POST | `/api/building_settings_api.php?action=update_building_settings` | 단지 담당자/연체요율 수정 |

### 주요 테이블 (sinbansang DB)

| 테이블 | 역할 |
|--------|------|
| `a_building` | 단지 정보 (담당자, 연체요율 포함) |
| `a_building_ho` | 세대(호수) 정보 |
| `a_building_dong` | 동 정보 |
| `a_member` | 입주민 회원 |
| `a_billing_card` | 자동결제 카드 (토스페이먼츠) |

---

## 개발 환경

| 환경 | URL | 서버 경로 | DB |
|------|-----|-----------|-----|
| **운영** | `smtm2017.com` | `/var/www/html/` | `sinbansang` |
| **테스트** | `test.smtm2017.com` | `/var/www/html_test/` | `bansanghoe` |
| **어드민** | `smtm2017.com/adm/` | - | - |

### 배포 워크플로우

```
develop 브랜치 push → GitHub Actions → SSH → git pull → test.smtm2017.com
main 브랜치 push → GitHub Actions → SSH → git pull → smtm2017.com (운영)
```

### 배포 스크립트 (.github/workflows/deploy.yml)

- 방식: `git reset --hard HEAD` → `git pull origin $BRANCH` (전체 동기화)
- 백업: 변경 파일을 `_backups/YYYYMMDD_HHMMSS/` 에 사전 복사
- 30일 이상 백업 자동 삭제
- 배포 후 `systemctl restart httpd`
- **서버에서 직접 파일 수정 금지** (`git reset --hard`로 덮어씌워짐)

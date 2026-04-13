# Changelog

## 2026-04-13
### Changed
- building_settings_api.php DB 연결: Gnuboard `_common.php` → PDO 직접 연결 + prepared statement

### Added (단지 관리)
- 단지 추가/수정 폼에 담당자 일괄 선택 기능 (`adm/building_mng_add.php`)
- 미배정↔배정 이동 UI, 이름/부서 검색, a_mng_building 일괄 저장

### Added
- 단지 담당자 조회 API — 기존 `a_mng_building` 활용 (`api/building_settings_api.php`)
- 연체요율 조회/수정 API (`building_settings`, `update_building_settings`)
- 점검일지 누락업체 조회 (`adm/inspection_missing.php`)

### Fixed
- 배포 스크립트(deploy.yml) git pull 방식으로 변경 — 머지 커밋 시 신규 파일 누락 해결
- 결재관리 '내결재' 탭 (`approval_document.php`)
- 단지 담당자/연체요율 API (`api/building_settings_api.php`)
- 서버/인프라 컨텍스트 문서 (`docs/CONTEXT.md`)

## 2026-04-09
### Fixed
- 투표 상세 이미지 공백/마크다운/**텍스트** 렌더링 수정

## 2026-04-02
### Added
- CKEditor 5 테스트 페이지
- 토스페이먼츠 카드 등록 화면 (테스트 모드)
- 온라인 투표 템플릿 선택 기능

## 2026-04-01
### Fixed
- 캘린더 반복일정 삭제/처리완료/dot 표시 버그 수정
- 매니저앱 점세개 버튼/수정 저장 오류 수정

## 2026-03-30
### Added
- 세대관리 1차/2차 검색 분리, 엑셀 양식 개선
- 검침 localStorage 임시저장, 오프라인 체크

# 네이버 클라우드 SENS SMS 설정 가이드

## 1. 네이버 클라우드 콘솔 접속
https://console.ncloud.com

## 2. SENS 프로젝트 생성
Services > SENS > SMS > 프로젝트 생성

## 3. 발신번호 등록
발신번호 관리 > 번호 등록 (사업자등록증 필요)

## 4. API 키 발급
마이페이지 > 인증키 관리 > API 인증키 생성
- Access Key ID
- Secret Key

## 5. 서비스 ID 확인
SENS > 프로젝트 > 서비스 ID 복사

## 6. 설정 파일 생성
```bash
cp config/ncloud_config.example.php config/ncloud_config.php
```
실제 값 입력

## 7. DB 테이블 생성
```bash
mysql -u sm_user1 -p sinbansang < sql/create_sms_history.sql
```

## 8. 테스트
소액 충전 후 테스트 발송

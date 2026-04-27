#!/bin/bash
#
# 반상회 서버 디스크 모니터링 스크립트
# - 디스크 사용률 80% 초과 시 /var/log/disk_alert.log 에 경고 기록
# - /var/log/httpd/ssl_request_log, ssl_access_log 7일 이상 자동 정리
#
# 설치 (서버에서 root):
#   cp /var/www/html/scripts/disk_monitor.sh /usr/local/bin/disk_monitor.sh
#   chmod 755 /usr/local/bin/disk_monitor.sh
#   crontab -e
#     0 * * * * /usr/local/bin/disk_monitor.sh >/dev/null 2>&1
#

ALERT_LOG="/var/log/disk_alert.log"
THRESHOLD=80
HTTPD_LOG_DIR="/var/log/httpd"
RETENTION_DAYS=7
NOW=$(date '+%Y-%m-%d %H:%M:%S')

touch "$ALERT_LOG" 2>/dev/null

# 1) 디스크 사용률 체크 (로컬 파일시스템만)
df -PT -x tmpfs -x devtmpfs -x squashfs 2>/dev/null | awk 'NR>1 {print $7" "$6}' | while read mount usage; do
    pct=${usage%\%}
    if [ -n "$pct" ] && [ "$pct" -ge "$THRESHOLD" ] 2>/dev/null; then
        echo "[$NOW] WARNING mount=${mount} usage=${usage} threshold=${THRESHOLD}%" >> "$ALERT_LOG"
    fi
done

# 2) Apache SSL 로그 7일 이상 정리
if [ -d "$HTTPD_LOG_DIR" ]; then
    deleted_count=0
    for pattern in "ssl_request_log*" "ssl_access_log*"; do
        while IFS= read -r f; do
            [ -z "$f" ] && continue
            rm -f "$f" && deleted_count=$((deleted_count + 1))
        done < <(find "$HTTPD_LOG_DIR" -maxdepth 1 -type f -name "$pattern" -mtime +${RETENTION_DAYS} 2>/dev/null)
    done
    if [ "$deleted_count" -gt 0 ]; then
        echo "[$NOW] CLEANUP httpd ssl logs deleted=${deleted_count} (>${RETENTION_DAYS}d)" >> "$ALERT_LOG"
    fi
fi

exit 0

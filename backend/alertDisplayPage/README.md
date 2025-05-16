# 주의사항

1. logs.db 파일의 권한을 주의!
    - 정상 작동: www-data www-data 777  (아마 664로도 정상 작동할 지 모름.)
2. alertDisplayPage 디렉터리의 권한 주의! 
    - 정상 작동: www-data www-data 755

### 수정용 코드

```bash
sudo chown www-data:www-data /var/www/LogAnalyzer/backend/alertDisplayPage
sudo chmod 755 /var/www/LogAnalyzer/backend/alertDisplayPage

sudo chown www-data:www-data /var/www/LogAnalyzer/backend/alertDisplayPage/logs.db
sudo chmod 664 /var/www/LogAnalyzer/backend/alertDisplayPage/logs.db
```


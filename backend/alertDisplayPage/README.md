# 주의사항

1. logs.db 파일의 권한을 주의!
    - 정상 작동: www-data www-data 777  (아마 664로도 정상 작동할 지 모름.)
2. alertDisplayPage 디렉터리의 권한 주의! 
    - 정상 작동: www-data www-data 755
3. index.php 파일은 다음 메타태그를 통해 특정 시간마다 새로고침 되도록 되어 있다.
    - ```<meta http-equiv="refresh" content="5">```

### 수정용 코드

```bash
sudo chown www-data:www-data /var/www/LogAnalyzer/backend/alertDisplayPage
sudo chmod 755 /var/www/LogAnalyzer/backend/alertDisplayPage

sudo chown www-data:www-data /var/www/LogAnalyzer/backend/alertDisplayPage/logs.db
sudo chmod 664 /var/www/LogAnalyzer/backend/alertDisplayPage/logs.db
```


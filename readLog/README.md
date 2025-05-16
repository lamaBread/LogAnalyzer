# LogAnalyzer 시스템 설명서

이 문서는 LogAnalyzer의 로그 수집 및 모니터링 기능에 대한 설명을 담고 있습니다.

## 시스템 구성

LogAnalyzer는 웹 서버의 로그 파일을 실시간으로 모니터링하고 분석하는 시스템입니다. 다음과 같은 주요 파일로 구성되어 있습니다:

1. `init.php`: 로그 모니터링 시스템의 초기화 및 실행 스크립트
2. `functions.php`: 로그 처리를 위한 다양한 기능 함수들을 포함한 라이브러리

## 기능 설명

### init.php

`init.php`는 로그 모니터링 시스템의 시작점으로, 다음과 같은 역할을 수행합니다:

- CLI(Command Line Interface) 환경에서만 실행되도록 제한
- 로그 디렉토리(`/webLogs`) 및 출력 디렉토리(`/var/www/html/LOG`) 설정
- 출력 파일(`combine_access.log`, `combine_error.log`) 초기화
- 로그 디렉토리 존재 여부 확인 및 필요시 생성
- `monitorLogFiles` 함수를 호출하여 로그 파일 모니터링 시작

### functions.php

`functions.php`는 로그 처리를 위한 다양한 유틸리티 함수를 제공합니다:

#### 주요 함수

1. **aggregateLogs**
   - 지정된 패턴에 맞는 로그 파일들을 읽어 하나의 출력 파일로 병합
   - 새로운 로그 항목을 식별하여 반환
   - 일반 텍스트 및 gzip 압축 로그 파일 모두 처리 가능

2. **gzfile_get_contents**
   - gzip 압축된 로그 파일의 내용을 읽는 유틸리티 함수

3. **readLogs_log**
   - 로그 처리 작업의 성공/실패 여부를 기록

4. **monitorLogFiles**
   - 로그 파일 변경 사항을 지속적으로 모니터링
   - 10초 간격으로 로그 파일 변경 여부 확인
   - 변경 감지 시 로그 파일 병합 및 새로운 로그 항목 식별
   - 새로운 로그 항목을 `sendLogsForEvaluation` 함수로 전송하여 평가

5. **sendLogsForEvaluation**
   - 식별된 새로운 로그 항목들을 평가 API로 전송
   - HTTP POST 요청을 통해 JSON 형식으로 로그 데이터 전송
   - 응답 결과 처리 및 오류 보고

## 동작 방식

1. `init.php`가 CLI에서 실행되면 로그 모니터링 시스템이 시작됩니다.
2. 시스템은 `/webLogs` 디렉토리의 access.log와 error.log 파일들을 지속적으로 모니터링합니다.
3. 로그 파일에 변경이 감지되면 새로운 로그 항목을 식별하고 병합합니다.
4. 식별된 새로운 로그 항목은 평가 API(`http://php-apache/alertDisplayPage/log_evaluate.php`)로 전송되어 분석됩니다.
5. 분석 결과는 콘솔에 출력되며, 의심스러운 로그 항목의 수를 보고합니다.

## 사용 방법

로그 모니터링 시스템은 다음 명령으로 시작할 수 있습니다:

```
php /var/www/LogAnalyzer/readLog/init.php
```

이 명령은 백그라운드에서 실행되어야 하며, 로그 모니터링이 지속적으로 이루어지도록 해야 합니다.
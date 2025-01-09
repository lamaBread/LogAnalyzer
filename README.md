# log-analyzer.lama.pe.kr 서브도메인 서비스 개요.

### 디렉터리 구조
* /var/www/log-analyzer
    * docker-compose.yml
    * frontend/
        * Next.js + React 작업공간. (Next.js의 /app 경로 내부로 마운트 됨)
    * backend/
        * PHP-FPM 정적파일(API) 경로. (Nginx에 의해 Fast-CGI 처리. Nginx의 웹루트(/var/www/html)에 마운트 됨)
    * nginx/
        * default.conf
            * (nginx 의 설정파일이다.)

### 파일 생성 규칙
1. 모든 파일 소유 그룹: log-analyzer-devgroup
    * 모든 작업자는 해당 그룹에 소속되어야, 파일에 접근 가능.
    * 파일과 관련한 권한 문제 발생시 바로 연락할 것.
2. 모든 파일의 권한 설정 권장: 664 ~ 660
    * 절대로 '기타'사용자 권한을 4 초과로 설정하지 말것.

### Nginx 와 PHP 컨테이너 설명
1. Next.js는 프록시로 nginx:80 을 가지고 있음.
2. nginx는 정적 PHP 파일을, php-fpm 컨테이너에 접근하여 실행함. php-fpm:9000 컨테이너는 외부 접근 불가. (expose로 설정됨)

### PHP API 접근 방법
1. /frontend/next.config.js 파일에 프록시 설정 가능.
2. 위 파일에 현재 두 경로로의 접근을 프록시 하도록 설정해 두었다. 
    * (Next.js로 들어온 요청 => Nginx로 넘어간 요청)
    * /APIs/* => http://nginx:80/*
3. 모든 API는 호스트 서버의 /backend/ 경로에 존재한다.
    * 이 경로는 Nginx 컨테이너의 /var/www/html/ 경로에 마운트 된다. (정적 웹루트)

### 서브도메인 프록시 설정 개요
1. 서브도메인 'log-analyzer'는 localhost:8445번 포트로 리버스 프록시 설정해 두었다.
2. Docker의 Next.js 컨테이너는 localhost:8445:3000 리슨 중. (apache=8445 -> Next.js=3000)
3. 즉, Next.js가 로그 해석기의 메인페이지를 서비스 함. SSR 방식으로 바로 웹페이지를 전송할 것.

### 새로 생성한 파일의 접근 권한 문제 발생시를 위한 기록
```bash
sudo chown -R :log-analyzer-devgroup /var/www/log-analyzer
```

###### 최종 수정일: 2025-01-09
## GitHub 에서 가져온 프로젝트를 실행하는 방법

여러분은 Docker를 설치했을 것입니다.
우선, GitHub에서 프로젝트를 가져와야 합니다.

``` bash
git clone https://github.com/lamaBread/LogAnalyzer.git
```
위 명령어로 GitHub에 저장된 현재 프로젝트를 다운로드 가능합니다.

최초 생성되는 폴더는 'LogAnalyzer'라는 이름을 가집니다. 그러므로 해당 디렉터리로 이동합니다.
``` bash
cd LogAnalyzer
```
이제 작업 공간으로 진입했습니다. 여러분이 설치한 Docker for windows 를 실행하고, 터미널로 돌아옵니다.

Docker 프로그램이 꼭 실행중인지 확인하고, 다음 명령어로 docker-compose.yml 파일을 실행합니다.
``` bash
docker-compose up -d
```
빌드가 완료되고, 모든 컨테이너가 실행될 때 까지 기다립니다. 

우리의 프로젝트는 4개의 독립 컨테이너를 실행합니다. 즉, 터미널에 4개의 컨테이너에 대하여 'Pulled' 라고 뜬다면 성공입니다.

이제 터미널을 꺼도 됩니다. Docker Container는 터미널과 상관 없이 동작중입니다. 이제 localhot:8445로 접속합시다.

``` bash
localhost:8445
```

## Docker Compose 실행 중단

작업을 마쳤다면, Docker Container를 모두 제거해야 합니다.

먼저 작업 폴더에서 터미널을 엽니다.

터미널에서 다음 명령어를 입력합니다.

``` bash
docker-compose down
```

4개의 컨테이너에 대하여 'Removed'가 뜨면 제거에 성공한 것입니다.

## 작업 내용 업로드

작업을 마쳤다면, GitHub에 공유를 해야 합니다. 공유 방법은 현재 디렉터리의 'how to use Git & GitHub' 문서를 참고하세요.

공유를 마쳤다면, 제가 병합 여부를 결정합니다. 병합이 완료되었다는 이메일이나 문자를 받았다면, 자신의 작업물이 잘 반영되었는지 꼭 확인해 주시길 바랍니다.
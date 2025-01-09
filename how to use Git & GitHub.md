# Git 설치 및 사용 방법

## 1. Git 설치

### Windows
1. [Git 공식 웹사이트](https://git-scm.com/)로 이동하여 설치 파일을 다운로드합니다.
2. 설치 파일을 실행하고 설치 마법사를 따라 진행합니다.
   - **옵션 설정**: 기본 설정을 사용하는 것이 좋습니다.
   - **PATH 설정**: "Git from the command line and also from 3rd-party software"를 선택합니다.
3. 설치 완료 후, 명령 프롬프트(CMD)나 Git Bash를 열어 `git --version` 명령어로 설치 확인을 합니다.

### macOS
1. 터미널을 열고 다음 명령어를 실행합니다:
   ```bash
   xcode-select --install
   ```
2. 명령어가 실패하면 [Git 공식 웹사이트](https://git-scm.com/)에서 다운로드 및 설치합니다.
3. 설치 완료 후, 터미널에서 `git --version` 명령어로 설치를 확인합니다.

### Linux (Ubuntu 기준)
1. 터미널을 열고 다음 명령어를 실행합니다:
   ```bash
   sudo apt update
   sudo apt install git
   ```
2. 설치 완료 후, 터미널에서 `git --version` 명령어로 설치를 확인합니다.

## 2. Git 초기 설정
1. 사용자 이름과 이메일 설정:
   ```bash
   // GitHub의 Profile을 참고하세요. 사용자 이름과, GitHub 계정 이메일입니다.
   git config --global user.name "사용자이름"
   git config --global user.email "사용자이메일"
   ```
2. 설정 확인:
   ```bash
   git config --list
   ```

## 3. Git 사용 방법

### 3.1. 저장소 복제
1. 작업할 저장소 URL을 확인합니다 (예: `https://github.com/사용자/저장소.git`).
2. 터미널에서 저장소를 복제합니다:
   ```bash
   git clone <저장소 URL>
   ```
   예(실제로 logAnalyzer 프로젝트가 저장됨 GitHub 경로입니다):
   ```bash
   git clone https://github.com/lamaBread/LogAnalyzer.git
   ```
3. 복제된 디렉터리로 이동합니다:
   ```bash
   cd project
   ```

### 3.2. 변경사항 확인 및 작업
1. 작업 전 최신 상태로 업데이트:
   ```bash
   git pull
   ```
2. 파일을 수정하거나 새 파일을 추가합니다.
3. 변경된 파일 확인:
   ```bash
   git status
   ```
4. 변경 내용을 단계 영역에 추가:
   ```bash
   git add <파일이름>
   ```
   모든 파일을 추가하려면 (새로운 파일과 수정된 파일만 반영):
   ```bash
   git add .
   ```
5. 삭제된 파일도 반영하려면 (생성 수정 삭제 모두 반영):
    ```bash
    git add -u .
    ```
6. 변경사항 커밋 (설명을 충실히, 그러나 너무 길지 않게 작성해 주세요):
   ```bash
   git commit -m "변경내용 설명"
   ```

### 3.3. 특정 범위의 변경사항만 Pull 및 Push

#### 특정 파일만 Pull
1. 저장소의 특정 파일만 업데이트하려면 다음 명령어를 사용합니다:
   ```bash
   git fetch origin <브랜치이름>
   git checkout origin/<브랜치이름> -- <파일경로>
   ```
   예:
   ```bash
   git fetch origin main
   git checkout origin/main -- src/example.py
   ```

#### 특정 파일만 Push
1. 특정 파일만 원격 저장소로 푸시하려면 해당 파일만 단계 영역에 추가하고 커밋한 뒤 푸시합니다:
   ```bash
   git add <파일경로>
   git commit -m "특정 파일 업데이트"
   git push origin <브랜치이름>
   ```
   예:
   ```bash
   git add src/example.py
   git commit -m "example.py 수정"
   git push origin main
   ```

### 3.4. 변경사항 업로드
1. 원격 저장소로 변경사항 푸시:
   ```bash
   git push
   ```

### 3.5. 충돌 해결
1. 푸시 시 충돌이 발생하면, 다음 명령어로 최신 변경사항을 병합합니다:
   ```bash
   git pull --rebase
   ```
2. 충돌이 난 파일을 수정한 뒤, 다시 추가 및 커밋합니다.
   ```bash
   git add <파일이름>
   git rebase --continue
   ```
3. 수정 완료 후 푸시:
   ```bash
   git push
   ```

## 4. 기타 유용한 명령어
1. 브랜치 확인:
   ```bash
   git branch
   ```
2. 새로운 브랜치 생성 및 이동:
   ```bash
   git checkout -b <브랜치이름>
   ```
3. 브랜치 삭제:
   ```bash
   git branch -d <브랜치이름>
   ```
4. 로그 확인:
   ```bash
   git log
   ```

## 5. node 환경 준비
1. /frontend 경로 내부에서 필요한 패키지 설치 (package.json 파일에 요구사항이 기록됨): 
   ```bash
   npm install
   ```
   
## 5. 참고사항
- 작업 전 항상 `git pull`로 최신 상태를 유지하세요.
- 주기적으로 `git status`를 확인하여 변경사항을 관리하세요.
- 팀 작업 시 명확한 커밋 메시지를 작성하세요.

## 6. 핵심 요약
- 최초 작업 과정
   ```sh
   git clone https://github.com/lamaBread/LogAnalyzer.git
   cd LogAnalyzer
   ```

- Docker 환경 구축 이후 작업 과정
   ```sh
   git pull  //최신 자료 다운로드 (새롭게 clone 해도 된다.)
   docker compose up -d  //-d 옵션은 백그라운드 실행이다.

   //작업 시작 및 종료.

   git add .  //생성 및 변경사항 반영

   // git status   <-- 이 명령 실행 결과 모두 녹색이 되었는지 확인. 붉은 글씨로 삭제 사항을 반영하라고 띄워주는 경우 'git add -u .' 명령으로 삭제사항 반영할 것.

   git commit -m "작업내용 설명"
   git push
   ```

이 문서를 따라 Git을 설치하고 사용할 수 있습니다. 문제가 발생하면 관리자나 Git 공식 문서를 참고하세요.
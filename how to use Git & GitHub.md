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
   # GitHub의 Profile을 참고하세요. 사용자 이름과, GitHub 계정 이메일입니다.
   git config --global user.name "사용자이름"
   git config --global user.email "사용자이메일"
   ```
2. 설정 확인:
   ```bash
   git config --list
   ```

## 3. Git 사용 방법

### 3.1. 저장소 복제 (최초 1회만 수행)
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

### 3.2. 변경사항 확인 & 변경사항 업로드 (매번 작업시 수행)
#### 주의! 이 섹션은 Git의 기본 사용법을 설명합니다. main 브랜치만 있는 상태를 전제하므로, 실제 작업시에는 [항목 6]을 참고하십시오!
1. 작업 전 최신 상태로 업데이트:
   ``` bash
   # 선택: git fetch  <-- Repository의 변경사항만을 가져옵니다. 
   
   # fetch를 수행하지 않으면, GitHub의 변경사항을 git status 명령이 확인할 수 없습니다.
   # git status 명령은 항상 로컬 git을 기준으로 동작하기 때문이지요. 
   # 현재 Repository가 로컬 Git과 얼마나 차이가 나는지를 확인하고 싶다면, 
   # git fetch 명령을 수행하고, git status 명령을 수행하십시오.

   # git fetch 와 git status를 수행한 결과, 변경된 사항이 없다면 pull을 할 필요가 없습니다.
   # 하지만 확인 없이 항상 pull을 수행해도 좋습니다. 취향것 하십시오!

   git pull
   ```
2. 파일을 수정하거나 새 파일을 추가합니다. (작업 수행)
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
    # git add 이후, git status의 결과에 붉은 글씨가 남아있다면 수행하십시오.
    git add -u .
    ```
6. 변경사항 커밋 (설명을 충실히, 그러나 너무 길지 않게 작성해 주세요):
   ```bash
   git commit -m "변경내용 설명"
   ```

7. 변경사항 push
   ```bash
   git push
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
   git push origin siheon_v5
   ```

### 3.4. 충돌 해결
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

## 4. 브랜치 관련 명령어
1. 현재 작업 브랜치 확인:
   ```bash
   git branch
   ```
2. 새로운 브랜치 생성 및 이동:
   ```bash
   git checkout -b <브랜치이름>
   ```
3. 이미 존재하는 브랜치로 이동:
   ```bash
   git checkout <브랜치이름>
   ```
4. 브랜치 삭제:
   ```bash
   git branch -d <브랜치이름>
   ```
5. 로그 확인:
   ```bash
   git log
   ```
   
## 5. 주의사항
- 작업 전 항상 `git pull`로 최신 상태를 유지하세요.
- 주기적으로 `git status`를 확인하여 변경사항을 관리하세요.
- 팀 작업 시 명확한 커밋 메시지를 작성하세요.
- main 브랜치로 바로 push하지 말아주세요.
   - 모든 작업자는 자신의 이름으로 된 브랜치를 유지합시다.
   - 자신의 브랜치에서 main의 현재 상태를 pull해서 작업을 수행하십시오.
   - 작업을 완료하고 자신의 브랜치로 push 합시다.
   - 큰 단위의 작업이 완료되었다면, main 브랜치로 pull request를 남겨주세요.

## 6. Branch를 사용한 실제 작업 과정
- 최초 작업 과정 (repository clone: 모든 브랜치를 가져온다.)
   ``` bash
   git clone https://github.com/lamaBread/LogAnalyzer.git
   cd LogAnalyzer

   git checkout -b <생성할 브랜치 이름>  # 브랜치를 생성하고, 즉시 이동합니다.
   ```

- Docker 환경 구축 이후 작업 과정
   ``` bash
   git checkout <자신의 브랜치 이름>  # 자신의 브랜치로 이동.

   git branch  # 자신의 브랜치로 이동되었는지 확인하세요..

   git pull origin main # main 브랜치의 최신 자료 다운로드. (정확히는 main의 commit들을 현재 브랜치에 merge 하는 것입니다.)
   # 만일 main의 commit과 당신의 현재 branch가 동일한 파일을 수정했다면, 어떤 파일을 유지할지 선택해야 합니다.

   docker compose up -d  # -d 옵션은 백그라운드 실행입니다.

   # 작업 시작 및 종료.

   docker compose down  # docker 컨테이너들을 종료합니다.

   git add .  #생성 및 변경사항을 반영합니다. (최상위 작업 경로에서 하십시오!)

   git status  # 이 명령 실행 결과 모두 녹색이 되었는지 확인하세요. 
   # 붉은 글씨로 삭제 사항을 반영하라고 띄워주는 경우,
   # => 'git add -u .' 명령으로 삭제사항을 반영하세요.

   git commit -m "작업내용 설명"  # 반영한 내용을 로컬 git에 저장합니다.

   git push origin <자신의 브랜치 이름>  # commit을 GitHub에 업로드 합니다. (자신의 브랜치로.)

   # 큰 단위의 작업이 완료되면, 자신의 브랜치를 main으로 병합하도록 요청하세요.
   # => GitHub 웹페이지에서 Pull Request 작성!
   ```

- 이 문서를 따라 Git을 설치하고 사용할 수 있습니다.
- 문제가 발생하면 먼저 Git 공식 문서를 참고하세요.
- 문제가 해결되지 않는다면 관리자에게 도움을 요청하세요! 본인은 잘때 항상 수면모드를 사용하므로, 아무때나 연락을 남겨도 괜찮습니다.
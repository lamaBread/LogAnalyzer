// 3개의 로그 관련 PHP POST API는 모두 로그 파일 경로를 입력받는다.
// getLogs 함수는 적절한 기준으로 분류된 로그를 가져온다. (상태코드별, IP별, 가공되지 않은 로그)

// 25-02-13 <- 현재 임시로 로그를 그대로 출력하도록 해 놓았다. 추후 access, error 로그를 통합하여 출력하도록 수정해야 한다.
export async function getLogs(type: string) {
    try {

      if (type === 'statusCode') {
        const response = await callLogAPI('group_by_statusCode_array.php');  // 상태코드별로 묶인 로그를 가져온다.
        return response;  // 배열이 반환됨.
  
      } else if (type === 'normal' || type === 'probable' || type === 'suspicious') {
        const response = await callLogAPI('log_array.php');  // 가공되지 않은 로그를 가져온다. (하나의 행에 하나의 로그.)
        return response;  // 배열이 반환됨.


      } else if (type === 'IP') {
        const response = await callLogAPI('group_by_IP_array.php');  // IP별로 묶인 로그를 가져온다.
        return response;  // 배열이 반환됨.

      }
  
    } catch (error) {
      console.error('Failed to fetch data:', error);
      throw error;
    }
  }

async function callLogAPI(module: string = 'log_array.php', filePath: string = './LOG/test_log_access') {
    try {
        const logFilePath = filePath; 
        const logArrayResponse = await fetch('http://localhost:8445/APIs/' + module, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({ filePath: logFilePath }),
        });

        if (!logArrayResponse.ok) {
            throw new Error('Failed to fetch log array');
        }

        const responseText = await logArrayResponse.text();
        return JSON.parse(responseText);  // JSON 파싱
    } catch (error) {
        console.error('Failed to fetch data:', error);
        throw error;
    }
}
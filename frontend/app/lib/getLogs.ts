// 3개의 로그 관련 PHP POST API는 모두 로그 파일 경로를 입력받는다.
// getLogs 함수는 적절한 기준으로 분류된 로그를 가져온다. (상태코드별, IP별, 가공되지 않은 로그)

export async function getLogs(type: string) {
    try {

      if (type === '100' || type === '200' || type === '300' || type === '400' || type === '500') {
        const response = await callLogAPI('group_by_statusCode_array.php');  // 상태코드별로 묶인 로그를 가져온다.
        const logs = JSON.parse(response);
        if (type === '100') {
          return logs.filter((log: any) => log.statusCode === '100');
        } else if (type === '200') {
          return logs.filter((log: any) => log.statusCode === '200');
        } else if (type === '300') {
          return logs.filter((log: any) => log.statusCode === '300');
        } else if (type === '400') {
          return logs.filter((log: any) => log.statusCode === '400');
        } else if (type === '500') {
          return logs.filter((log: any) => log.statusCode === '500');
        }
  
      } else if (type === 'normal' || type === 'probable' || type === 'suspicious') {
        const response = await callLogAPI('log_array.php');  // 가공되지 않은 로그를 가져온다. (하나의 행에 하나의 로그.)
        const logs = JSON.parse(response);
        return logs;

      } else if (type === 'IP') {
        const response = await callLogAPI('group_by_IP_array.php');  // IP별로 묶인 로그를 가져온다.
        const logs = JSON.parse(response);
        return logs;
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

        return await logArrayResponse.json();
    } catch (error) {
        console.error('Failed to fetch data:', error);
        throw error;
    }
}
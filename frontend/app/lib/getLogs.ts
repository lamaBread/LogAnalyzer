export async function getLogs(type: string) {
    try {

      if (type === 'statusCode') {
        const response = await callLogAPI('group_by_statusCode_array.php');
        return response;
  
      } else if (type === 'classifying') {
        const response = await callLogAPI('log_array.php', './LOG/test_log');
        return response;


      } else if (type === 'IP') {
        const response = await callLogAPI('group_by_IP_array.php');
        return response; 

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
        return JSON.parse(responseText);
    } catch (error) {
        console.error('Failed to fetch data:', error);
        throw error;
    }
}
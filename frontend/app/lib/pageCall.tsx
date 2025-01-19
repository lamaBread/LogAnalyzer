export async function PageCall(page: string) {
  try {
    // lama.pe.kr 에서 사용할 코드: https://log-analyzer.com/APIs/page_APIs/${page}.php
    // 서브도메인 서비스에서 Origin은 https://log-analyzer.com 로 인식되는 것을 확인했다.
    // 즉, http://localhost:8445 에서는 CORS 에러가 발생한다.

    // 다만, local 환경에서 docker-compsoe로 실행한 시스템에서는 http://localhost:8445 경로의 접근도 오류 없음.
    // 실제 서비스시 => docker-compose의 .env 파일에 API 호출 오리진 저장할 것. (사용자가 직접 서브도메인을 입력하도록 함. 예: "PAGE_CALL_PATH=https://example-subdoman.example.com")
    const response = await fetch(`http://localhost:8445/APIs/page_APIs/${page}.php`, {
      method: 'POST',
      headers: {
      'Content-Type': 'application/json',
      'X-Auth-Key': 'A?5Ql1qpU9MQA?r',
      },
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    return response.json();
  } catch (error) {
    console.error('Failed to fetch data:', error);
    throw error;
  }
}

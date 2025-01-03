
# PHP API 호출 설명서

## 1. 서론
PHP API는 정적 파일입니다. nginx에 의해 해석되고 전송되는 정적 파일입니다.

여러분은 **Next.js**에서 PHP API를 호출할 때, `fetch()` 함수를 사용해야 합니다.

- `fetch()` 함수는 특정 경로로 POST, GET 등의 요청을 보낼 수 있습니다.
- 각 API마다 적절한 패스워드를 설정하며, 해당 패스워드는 **헤더(Header)**에 기입하도록 합니다.

**Next.js**에서 `/APIs`로 시작하는 모든 경로는 nginx로 프록시되도록 설정되어 있습니다.  
nginx는 php-fpm 서버로 해석을 요청하므로, 여러분은 적절한 API 키를 포함한 POST 요청을 `fetch()`로 작성해 호출하면 됩니다.

PHP API의 반환값은 JSON으로 압축된 HTML 파일입니다.  
(적어도 이번에 만든 더미 API는 그렇습니다.)

---

## 2. 예시
`fetch`를 수행하는 예시 코드는 다음과 같습니다.

```javascript
const response = await fetch('http://log-analyzer.lama.pe.kr/APIs/page_APIs/mainPage.php', {
  method: 'POST',
  headers: {
    'X-Auth-Key': 'A?5Ql1qpU9MQA?r', // 인증 키 포함
  },
});
```

- **주의사항**: 적절한 인증키를 넣지 않으면 `403 Forbidden` 오류가 발생합니다.
- 인증 키는 항상 **헤더 영역**에 포함되어야 하며, 위 예시의 키와 값은 실제로 동작하는 값입니다.

---

## 3. 작업 방법
- 다른 API에 대해서도 별도의 패스워드를 등록하고 공유할 예정입니다.
- 위의 예시 코드를 동작시키기 위한 별도의 함수를 `log-analyzer/frontend/lib/` 경로 내부에 작성하세요.
- 이후 필요한 `page.tsx` 파일 내부에서 `import`하여 호출하면 됩니다.

**참고**: 모든 API는 `log-analyzer/backend/` 경로 내부에 위치합니다.

---

## 4. 이해 향상을 위한 AI 답변 첨부

### 4.1 API를 특정 페이지에서 바로 호출하는 예제
```javascript
import { useEffect, useState } from 'react';

export default function HomePage() {
  const [mainText, setMainText] = useState('');

  useEffect(() => {
    async function fetchData() {
      const response = await fetch('http://your-server-address/mainPage.php', {
        method: 'POST',
        headers: {
          'X-Auth-Key': 'your-secret-key', // 인증 키 포함
        },
      });
      if (response.ok) {
        const data = await response.json();
        setMainText(data.mainText);
      } else {
        console.error('Failed to fetch data');
      }
    }

    fetchData();
  }, []);

  return (
    <div dangerouslySetInnerHTML={{ __html: mainText }} />
  );
}
```

---

### 4.2 API를 호출하는 함수를 만든 예제

#### `/frontend/lib/api.js`
```javascript
export async function fetchFromPhpApi(endpoint) {
  const response = await fetch(`http://your-php-api-endpoint/${endpoint}`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
    },
  });

  if (!response.ok) {
    throw new Error('Network response was not ok');
  }

  return response.json();
}
```

#### `/frontend/pages/api/callPhpApi.js`
```javascript
import { fetchFromPhpApi } from '../../lib/api';

export default async function handler(req, res) {
  try {
    const data = await fetchFromPhpApi('your-endpoint');
    res.status(200).json(data);
  } catch (error) {
    res.status(500).json({ error: 'Failed to fetch data' });
  }
}
```
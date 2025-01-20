"use client";

import React, { useState } from "react";

export default function SearchPage() {
  const [query, setQuery] = useState<string>(""); // 검색어 상태
  const [results, setResults] = useState<string[]>([]); // 검색 결과 상태
  const [loading, setLoading] = useState<boolean>(false); // 로딩 상태
  const [error, setError] = useState<string | null>(null); // 에러 메시지 상태
  const [history, setHistory] = useState<string[]>([]); // 검색 기록 상태

  const handleSearch = async () => {
    if (!query.trim() || query.length < 1) {
      setError("검색어를 입력하세요.");
      return;
    }

    setLoading(true);
    setError(null);

    try {
      // PHP 서버에 POST 요청 보내기
      const response = await fetch("http://localhost:8445/APIs/page_APIs/searchLogs.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded", // PHP와 호환되는 방식
          "X-Auth-Key": "A?5Ql1qpU9MQA?r", 
        },
        body: `question=${encodeURIComponent(query)}`, // 검색어를 전달
      });

      // 서버 응답 상태 체크
      if (!response.ok) {
        throw new Error(`서버 응답 오류: ${response.status} ${response.statusText}`);
      }

      const data = await response.json(); // PHP에서 반환된 JSON 데이터 받기
      setResults(data.length > 0 ? data : ["검색 결과가 없습니다."]); // 결과가 없으면 메시지 표시

      // 검색 기록에 검색어 추가 (중복 방지, 최대 10개)
      setHistory((prevHistory) => {
        const newHistory = [query, ...prevHistory.filter((item) => item !== query)];
        return newHistory.slice(0, 10); // 최대 10개로 제한
      });
    } catch (err) {
      console.error("검색 중 오류 발생:", err); // 에러 로그 추가
      setError(`검색 중 문제가 발생했습니다: ${err instanceof Error ? err.message : '알 수 없는 오류'}`); // 구체적인 에러 메시지 표시
    } finally {
      setLoading(false);
    }
  };

  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter") {
      handleSearch();
    }
  };

  const handleDeleteHistory = (searchTerm: string) => {
    setHistory(history.filter((item) => item !== searchTerm)); // 선택한 검색어 삭제
  };

  return (
    <div className="flex flex-col h-screen p-4">
      <h1 className="text-3xl font-bold mb-4">Log Search</h1>

      <div className="flex mb-4">
        <input
          type="text"
          placeholder="검색어를 입력하세요"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          onKeyDown={handleKeyDown}
          className="flex-grow p-4 border-2 rounded-md mr-4 text-xl border-gray-300 dark:border-gray-700 text-black bg-white dark:bg-gray-800 dark:text-gray-200"
        />
        <button
          onClick={handleSearch}
          className="px-6 py-4 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-xl"
        >
          검색
        </button>
      </div>

      {loading && <p>로딩 중...</p>}
      {error && <p className="text-red-500">{error}</p>}

      <div>
        {results.length > 0 ? (
          <ul className="list-disc list-inside text-xl">
            {results.map((result, index) => (
              <li key={index}>{result}</li>
            ))}
          </ul>
        ) : (
          <p>검색 결과가 없습니다.</p>
        )}
      </div>

      <div className="mt-6">
        <h2 className="text-2xl font-bold mb-2">검색 기록</h2>
        {history.length > 0 ? (
          <ul className="list-disc list-inside text-lg">
            {history.map((item, index) => (
              <li key={index} className="flex items-center">
                <span>{item}</span>
                <button
                  onClick={() => handleDeleteHistory(item)}
                  className="ml-2 text-red-500 hover:text-red-700"
                >
                  X
                </button>
              </li>
            ))}
          </ul>
        ) : (
          <p>검색 기록이 없습니다.</p>
        )}
      </div>
    </div>
  );
}

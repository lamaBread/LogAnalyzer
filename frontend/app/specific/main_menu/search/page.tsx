"use client";

import React, { useState } from "react";

export default function AISearchPage() {
  const [query, setQuery] = useState<string>(""); // 검색어 상태
  const [results, setResults] = useState<string[]>([]); // AI 검색 결과 상태
  const [loading, setLoading] = useState<boolean>(false); // 로딩 상태
  const [error, setError] = useState<string | null>(null); // 에러 메시지 상태
  const [history, setHistory] = useState<string[]>([]); // 검색 기록 상태

  const handleSearch = async () => {
    if (!query.trim()) return; // 빈 검색어 무시

    setLoading(true); // 로딩 시작
    setError(null); // 에러 초기화

    try {
      // AI API 호출
      const response = await fetch("/api/search", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ query }),
      });

      if (response.ok) {
        const data = await response.json();
        setResults([data.result]); // AI의 결과를 받아와서 표시
        setHistory((prev) => [...prev, query]); // 검색 기록에 추가
      } else {
        setResults(["Error fetching AI results."]);
      }
    } catch (error) {
      setResults(["Error: Could not connect to the API."]);
    } finally {
      setLoading(false); // 로딩 완료
    }
  };

  // 엔터 키를 눌렀을 때 검색 함수 실행
  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter") {
      handleSearch();
    }
  };

  return (
    <div className="flex flex-col h-screen p-4">
      <h1 className="text-3xl font-bold mb-4">AI Log Search</h1>

      {/* 검색 입력 필드 */}
      <div className="flex mb-4">
        <input
          type="text"
          placeholder="검색어를 입력하세요"
          value={query} // 상태와 입력 값 연결
          onChange={(e) => setQuery(e.target.value)} // 입력 값 변경 시 상태 업데이트
          onKeyDown={handleKeyDown} // 엔터 키 눌렀을 때 검색
          className="flex-grow p-4 border-2 rounded-md mr-4 text-xl border-gray-300 dark:border-gray-700 text-black bg-white dark:bg-gray-800 dark:text-gray-200"
        />
        <button
          onClick={handleSearch}
          className="px-6 py-4 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-xl"
        >
          검색
        </button>
      </div>

      {/* 로딩 중 표시 */}
      {loading && <p>Loading...</p>}

      {/* 에러 메시지 */}
      {error && <p className="text-red-500">{error}</p>}

      {/* 검색 결과 */}
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

      {/* 검색 기록 */}
      <div className="mt-6">
        <h2 className="text-2xl font-bold mb-2">검색 기록</h2>
        {history.length > 0 ? (
          <ul className="list-disc list-inside text-lg">
            {history.map((item, index) => (
              <li key={index}>{item}</li>
            ))}
          </ul>
        ) : (
          <p>검색 기록이 없습니다.</p>
        )}
      </div>
    </div>
  );
}

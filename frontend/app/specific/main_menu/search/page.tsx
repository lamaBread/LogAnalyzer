"use client";

import React, { useState } from "react";

// 100번부터 500번까지의 로그와 설명 생성
const mockData = Array.from({ length: 401 }, (_, i) => {
  const logNumber = i + 100; // 100부터 시작
  return {
    log: `Log entry ${logNumber}: Example log message ${logNumber}.`,
    description: `로그 ${logNumber}에 대한 설명입니다.`,
  };
});

export default function SearchPage() {
  const [query, setQuery] = useState<string>(""); // 검색어 상태
  const [results, setResults] = useState<typeof mockData>([]); // 검색 결과 상태
  const [history, setHistory] = useState<string[]>([]); // 검색 기록 상태

  const handleSearch = () => {
    if (!query.trim()) return; // 빈 검색어 무시

    // 검색 결과 필터링
    const filteredResults = mockData.filter((item) =>
      item.log.toLowerCase().includes(query.toLowerCase())
    );

    setResults(filteredResults); // 검색 결과 업데이트
    setHistory((prev) => [...prev, query]); // 검색 기록에 추가
  };

  // 엔터 키를 눌렀을 때 검색 함수 실행
  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter") {
      handleSearch();
    }
  };

  return (
    <div className="flex flex-col h-screen p-4">
      <h1 className="text-3xl font-bold mb-4">Log Search</h1>

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

      {/* 검색 결과 */}
      <div>
        {results.length > 0 ? (
          <ul className="space-y-4">
            {results.map((result, index) => (
              <li key={index} className="p-4 border rounded-md bg-gray-100 dark:bg-gray-800">
                <p className="text-xl font-semibold">{result.log}</p>
                <p className="text-gray-600 dark:text-gray-400">{result.description}</p>
              </li>
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

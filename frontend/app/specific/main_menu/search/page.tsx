"use client";

import React, { useState, useEffect } from "react";

export default function SearchPage() {
  const [query, setQuery] = useState<string>(""); // 검색어 상태
  const [results, setResults] = useState<string[]>([]); // 로컬 검색 결과 상태
  const [loading, setLoading] = useState<boolean>(false); // 로딩 상태
  const [error, setError] = useState<string | null>(null); // 에러 메시지 상태
  const [history, setHistory] = useState<string[]>([]); // 검색 기록 상태
  const [localData, setLocalData] = useState<string[]>([]); // PHP 파일에서 불러온 데이터 상태

  useEffect(() => {
    // PHP 서버에서 로그 데이터를 가져오는 API 호출
    fetch("http://localhost:8445/APIs/page_APIs/searchLogs.php")  // 실제 PHP 파일 경로를 입력하세요
      .then((response) => response.json())
      .then((data) => setLocalData(data)) // PHP에서 받은 로그 데이터를 상태에 저장
      .catch((error) => {
        console.error("PHP 데이터를 읽는 중 오류 발생", error);
        setError("PHP 파일을 불러오는 중 오류가 발생했습니다.");
      });
  }, []);

  const handleSearch = () => {
    if (!query.trim()) return;

    setLoading(true);
    setError(null);

    try {
      // PHP에서 불러온 데이터에서 검색
      const filteredResults = localData.filter((entry) =>
        entry.toLowerCase().includes(query.toLowerCase()) // 검색어가 포함된 항목 필터링
      );

      if (filteredResults.length > 0) {
        setResults(filteredResults);
      } else {
        setResults(["검색 결과가 없습니다."]);
      }

      setHistory((prev) => [...prev, query]);
    } catch (error) {
      setError("검색 중 오류가 발생했습니다.");
    } finally {
      setLoading(false);
    }
  };

  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === "Enter") {
      handleSearch();
    }
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
          className="flex-grow p-4 border-2 rounded-md mr-4 text-xl"
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

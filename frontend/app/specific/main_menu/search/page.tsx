"use client";

import React, { useState } from "react";

export default function SearchPage() {
  const [query, setQuery] = useState<string>(""); // 검색어 상태
  const [results, setResults] = useState<string[]>([]); // 검색 결과 상태
  const [history, setHistory] = useState<string[]>([]); // 검색 기록 (최대 10개)
  const [loading, setLoading] = useState<boolean>(false); // 로딩 상태
  const [error, setError] = useState<string | null>(null); // 에러 메시지 상태

  const handleSearch = async () => {
    if (!query.trim() || query.length < 1) {
      setError("검색어를 입력하세요.");
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const response = await fetch("http://localhost:8445/APIs/page_APIs/searchLogs.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Auth-Key": "A?5Ql1qpU9MQA?r",
        },
        body: `question=${encodeURIComponent(query)}`,
      });

      if (!response.ok) {
        throw new Error(`서버 응답 오류: ${response.status} ${response.statusText}`);
      }

      const data = await response.json();
      setResults(data.length > 0 ? data : ["검색 결과가 없습니다."]);

      // 검색 기록 추가 (최대 10개 유지)
      setHistory((prev) => {
        const updatedHistory = [query, ...prev.filter((item) => item !== query)].slice(0, 10);
        return updatedHistory;
      });
    } catch (err) {
      console.error("검색 중 오류 발생:", err);
      setError(`검색 중 문제가 발생했습니다: ${err instanceof Error ? err.message : "알 수 없는 오류"}`);
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteHistory = (index: number) => {
    setHistory((prev) => prev.filter((_, i) => i !== index));
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

      {/* 검색 결과 */}
      <div className="mt-6">
        <h2 className="text-2xl font-bold mb-2">검색 결과</h2>
        <div className="overflow-y-auto border border-gray-400 rounded-md" style={{ maxHeight: "500px" }}>
          {results.length > 0 ? (
            <table className="table-auto w-full border-collapse text-xl">
              <tbody>
                {results.map((result, index) => (
                  <tr
                    key={index}
                    className={index % 2 === 0 ? "bg-white dark:bg-gray-700" : "bg-gray-200 dark:bg-gray-800"}
                  >
                    <td className="px-4 py-2 border-b border-gray-400">{result}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          ) : (
            <p className="p-4">검색 결과가 없습니다.</p>
          )}
        </div>
      </div>

      {/* 검색 기록 */}
      {history.length > 0 && (
        <div className="mt-6">
          <h2 className="text-2xl font-bold mb-2">검색 기록</h2>
          <ul className="text-lg">
            {history.map((item, index) => (
              <li key={index} className="flex justify-between items-center border-b py-2">
                <span>{item}</span>
                <button
                  onClick={() => handleDeleteHistory(index)}
                  className="text-red-500 hover:text-red-700 text-sm"
                >
                  삭제
                </button>
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}

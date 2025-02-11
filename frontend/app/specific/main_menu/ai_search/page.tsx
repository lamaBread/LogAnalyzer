"use client";

import React, { useState, useEffect } from "react";

interface HistoryItem {
  question: string;
  answer: string;
}

export default function AISearchPage() {
  const [results, setResults] = useState<HistoryItem[]>([]); // 검색 결과와 AI 답변을 저장
  const [query, setQuery] = useState<string>(""); // 검색어 상태 관리
  const [error, setError] = useState<string | null>(null); // 에러 상태 관리
  const [loading, setLoading] = useState<boolean>(false); // 로딩 상태 관리

  useEffect(() => {
    // 컴포넌트 로드 시 더미 데이터 사용
    const fetchConversations = async () => {
      try {
        // 원래 DB에서 가져오는 부분이었지만, 더미 데이터 사용
        const dummyConversations: HistoryItem[] = [
          { question: "테스트 질문 1", answer: "테스트 응답 1" },
          { question: "테스트 질문 2", answer: "테스트 응답 2" },
        ];
        setResults(dummyConversations);
      } catch (err) {
        setError("대화 기록을 불러오지 못했습니다.");
      }
    };

    fetchConversations();
  }, []);

  const handleSearch = async () => {
    if (!query.trim()) {
      setError("검색어를 입력하세요.");
      return;
    }

    setLoading(true);
    setError(null);

    try {
      // 📌 1️⃣ PHP 서버 요청 대신 더미 응답 사용
      const dummyResponse = { response: "PHP 서버 가짜 응답" };

      // 📌 2️⃣ AI 검색 요청도 제거하고 더미 응답 사용
      const dummyAIResponse = { answer: "AI 가짜 응답" };

      // 📌 3️⃣ 결과 저장 (더미 데이터 사용)
      const newHistory = [
        ...results,
        {
          question: query,
          answer: dummyAIResponse.answer || dummyResponse.response || "검색 결과가 없습니다.",
        },
      ];
      setResults(newHistory);

      setQuery(""); // 검색어 초기화
    } catch (err) {
      setError(err instanceof Error ? err.message : "알 수 없는 오류 발생");
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
    <div className="flex flex-col h-screen">
      <div className="flex flex-grow">
        {/* 검색 기록 */}
        <div className="w-1/4 p-4 border-r border-gray-300 dark:border-gray-700">
          <h2 className="text-2xl font-bold mb-4">검색 기록</h2>
          <div
            className="overflow-y-auto"
            style={{ maxHeight: "500px" }} // max-height 설정으로 스크롤 추가
          >
            <div className="p-4 min-h-[50px]">
              {results.length > 0 ? (
                <ul className="text-lg">
                  {results.map((item, index) => (
                    <li key={index} className="flex flex-col space-y-2 border-b py-2">
                      <div className="text-blue-500 font-semibold">Q: {item.question}</div>
                      <div className="text-gray-800 dark:text-gray-300">A: {item.answer}</div>
                    </li>
                  ))}
                </ul>
              ) : (
                <p>검색 기록이 없습니다.</p>
              )}
            </div>
          </div>
        </div>

        {/* AI 챗봇 */}
        <div className="w-3/4 p-2">
          <h2 className="text-2xl font-bold mb-2">AI 챗봇</h2>
          <div
            className="overflow-y-auto border border-gray-400 rounded-md"
            style={{ maxHeight: "500px" }} // max-height 설정으로 대화창 높이 제한
          >
            {results.length > 0 ? (
              <div className="flex flex-col space-y-4">
                {results.map((item, index) => (
                  <div key={index} className="flex flex-col space-y-2">
                    {/* 사용자 질문 */}
                    <div className="flex justify-end">
                      <div className="bg-blue-500 text-white p-3 rounded-lg max-w-xs mb-2">
                        <span className="font-semibold">Q: </span>{item.question}
                      </div>
                    </div>
                    {/* AI 답변 */}
                    <div className="flex justify-start">
                      <div className="bg-gray-200 text-black p-3 rounded-lg max-w-xs">
                        <span className="font-semibold">A: </span>{item.answer}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <p className="p-4">검색 결과가 없습니다.</p>
            )}
          </div>
        </div>
      </div>

      {/* 검색바 */}
      <div className="flex p-4 border-t border-gray-300 dark:border-gray-700">
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
    </div>
  );
}

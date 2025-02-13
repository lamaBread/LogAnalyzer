"use client";

import React, { useState, useEffect } from "react";
import { chatLLM } from "../../../lib/chatLLM";

interface HistoryItem {
  role: "user" | "assistant";  // 사용자 또는 AI 응답 구분
  content: string;            // 실제 메시지 내용
}

export default function AISearchPage() {
  const [history, setHistory] = useState<HistoryItem[]>([]); // 전체 대화 내역을 저장
  const [query, setQuery] = useState<string>(""); // 사용자가 입력하는 현재 질문을 저장
  const [error, setError] = useState<string | null>(null); // 에러 상태 관리
  const [loading, setLoading] = useState<boolean>(false); // (API 요청 중임을 나타내는) 로딩 상태 관리
  const [currentResponse, setCurrentResponse] = useState<string>(""); // 현재 스트리밍되고 있는 AI 응답을 저장

  const handleSearch = async () => {
    if (!query.trim()) {
      setError("검색어를 입력하세요.");
      return;
    }

    setLoading(true);
    setError(null);
    setCurrentResponse("");

    const newMessage: HistoryItem = { role: "user", content: query };
    const updatedHistory = [...history, newMessage];
    setHistory(updatedHistory);

    try {
      let fullResponse = "";  // AI 응답을 누적하여 저장할 변수
      for await (const chunk of chatLLM(updatedHistory)) {  // 스트리밍으로 들어오는 응답을 실시간으로 화면에 표시
        if (chunk.message?.content) {
          fullResponse += chunk.message.content;
          setCurrentResponse(fullResponse);
        }
        
        // 응답이 완료되면 전체 대화 기록에 추가
        if (chunk.done) {
          setHistory(prev => [...prev, { 
            role: "assistant", 
            content: fullResponse
          }]);
          setCurrentResponse("");
        }
      }

      setQuery("");
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
          <div className="overflow-y-auto" style={{ maxHeight: "500px" }}>
            <div className="p-4 min-h-[50px]">
              {history.length > 0 ? (
                <ul className="text-lg">
                  {history.map((item, index) => (
                    <li key={index} className="flex flex-col space-y-2 border-b py-2">
                      <div className={item.role === "user" ? "text-blue-500" : "text-green-500"}>
                        {item.role === "user" ? "Q: " : "A: "}{item.content}
                      </div>
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
          <div className="overflow-y-auto border border-gray-400 rounded-md" style={{ maxHeight: "500px" }}>
            <div className="flex flex-col space-y-4 p-4">
              {history.map((item, index) => (
                <div key={index} className="flex flex-col space-y-2">
                  {item.role === "user" ? (
                    <div className="flex justify-end">
                      <div className="bg-blue-500 text-white p-3 rounded-lg max-w-xs">
                        {item.content}
                      </div>
                    </div>
                  ) : (
                    <div className="flex justify-start">
                      <div className="bg-gray-200 text-black p-3 rounded-lg max-w-xs">
                        {item.content}
                      </div>
                    </div>
                  )}
                </div>
              ))}
              {currentResponse && (
                <div className="flex justify-start">
                  <div className="bg-gray-200 text-black p-3 rounded-lg max-w-xs">
                    {currentResponse}
                  </div>
                </div>
              )}
              {loading && !currentResponse && (
                <div className="flex justify-start">
                  <div className="bg-gray-200 text-black p-3 rounded-lg">
                    typing...
                  </div>
                </div>
              )}
            </div>
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
          disabled={loading}
          className="flex-grow p-4 border-2 rounded-md mr-4 text-xl border-gray-300 dark:border-gray-700 text-black bg-white dark:bg-gray-800 dark:text-gray-200"
        />
        <button
          onClick={handleSearch}
          disabled={loading}
          className="px-6 py-4 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-xl disabled:bg-gray-400"
        >
          검색
        </button>
      </div>
    </div>
  );
}

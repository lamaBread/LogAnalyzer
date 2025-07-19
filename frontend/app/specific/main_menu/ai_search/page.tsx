"use client";

import React, { useState, useEffect } from "react";
import { chatLLM } from "../../../lib/chatLLM";

interface HistoryItem {
  role: "user" | "assistant";
  content: string;
}

export default function AISearchPage() {
  const [history, setHistory] = useState<HistoryItem[]>([]);
  const [query, setQuery] = useState<string>("");
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState<boolean>(false);
  const [currentResponse, setCurrentResponse] = useState<string>("");

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
      let fullResponse = "";
      for await (const chunk of chatLLM(updatedHistory)) {
        if (chunk.message?.content) {
          fullResponse += chunk.message.content;
          setCurrentResponse(fullResponse);
        }
      
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

        <div className="w-3/4 p-2">
          <h2 className="text-2xl font-bold mb-2">AI 챗봇</h2>
          <div className="flex flex-col border border-gray-400 rounded-md" style={{ height: "800px" }}>
            <div className="overflow-y-auto flex flex-col h-full space-y-4 p-4">
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
            <div className="flex p-4 border-t border-gray-300 dark:border-gray-700 flex-shrink-0">
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
        </div>
      </div>
    </div>
  );
}

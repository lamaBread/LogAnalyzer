"use client";

import React, { useState } from "react";

interface Context {
  question: string;
  answer: string;
}

export default function AISearchPage() {
  const [query, setQuery] = useState<string>(""); // 검색어 상태
  const [results, setResults] = useState<string | null>(null); // AI 검색 결과 상태
  const [history, setHistory] = useState<Context[]>([]); // 검색 기록 상태
  const [loading, setLoading] = useState<boolean>(false); // 로딩 상태
  const [error, setError] = useState<string | null>(null); // 에러 메시지 상태

  const handleSearch = async () => {
    if (!query.trim()) {
      setError("검색어를 입력하세요.");
      return;
    }

    setLoading(true); // 로딩 시작
    setError(null); // 에러 초기화

    try {
      // 1️⃣ PHP 서버에 POST 요청 보내기 (기존 searchLogs.php와 연결)
      const response = await fetch("http://localhost:8445/APIs/page_APIs/searchLogs.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Auth-Key": "A?5Ql1qpU9MQA?r",
        },
        body: `question=${encodeURIComponent(query)}`, // 템플릿 리터럴 수정
      });

      const responseText = await response.text(); // 텍스트로 응답 받기
      try {
        const data = JSON.parse(responseText); // JSON 형식으로 변환
        setResults(data.length > 0 ? data : ["검색 결과가 없습니다."]);
      } catch (error) {
        console.error("응답을 JSON으로 파싱하는 중 오류 발생:", error);
        setError("서버 응답 오류");
      }

      // PHP 응답 데이터 처리 (JSON으로 변환)
      const phpData = await response.json();
      console.log("📌 PHP 응답 데이터:", phpData);

      // 2️⃣ AI 검색 요청 (AI API 호출)
      const aiResponse = await fetch("/api/aisearch", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          question: query,
          contexts: history.map((item) => ({
            question: item.question,
            answer: item.answer,
          })),
        }),
      });

      if (!aiResponse.ok) {
        const errorData = await aiResponse.json();
        throw new Error(errorData.error || "AI 검색 요청 실패");
      }

      const aiData = await aiResponse.json(); // AI 응답 데이터 처리
      console.log("📌 AI 응답 데이터:", aiData);

      // 3️⃣ 결과 저장 (PHP 응답 + AI 응답)
      const combinedResult = `🔹 PHP: ${phpData || "없음"} | 🤖 AI: ${aiData.answer}`;
      setResults(combinedResult); // 검색 결과 반영

      // 4️⃣ 새로운 질문과 답변을 history에 추가
      setHistory((prev) => {
        const newHistory = [...prev, { question: query, answer: combinedResult }];
        console.log("새로운 history:", newHistory);  // 새로운 history 배열 확인
        return newHistory;
      });

    } catch (err) {
      setError(err instanceof Error ? err.message : "알 수 없는 오류 발생");
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
    <div className="flex flex-col h-screen">
      <div className="flex flex-grow">
        {/* 검색 기록 */}
        <div className="w-1/4 p-4 border-r border-gray-300 dark:border-gray-700">
          <h2 className="text-2xl font-bold mb-4">검색 기록</h2>
          <div className="p-4 min-h-[50px]">
            {history.length > 0 ? (
              <ul className="text-lg">
                {history.map((item, index) => (
                  <li key={index} className="flex justify-between items-center border-b py-2">
                    <span>{item.question}</span>
                    <span>{item.answer}</span>
                  </li>
                ))}
              </ul>
            ) : (
              <p>검색 기록이 없습니다.</p>
            )}
          </div>
        </div>

        {/* AI 챗봇 */}
        <div className="w-3/4 p-2">
          <h2 className="text-2xl font-bold mb-2">AI 챗봇</h2>
          <div className="overflow-y-auto border border-gray-400 rounded-md" style={{ maxHeight: "500px" }}>
            {results ? (
              <table className="table-auto w-full border-collapse text-xl">
                <tbody>
                  <tr className="bg-white dark:bg-gray-700">
                    <td className="px-4 py-2 border-b border-gray-400">{results}</td>
                  </tr>
                </tbody>
              </table>
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

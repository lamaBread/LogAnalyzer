"use client";

import React, { useState, useRef, useEffect } from "react";
import { queryLLM } from './lib/queryLLM';
import { marked } from 'marked';
import './styles/markdown.css';

export default function Page() {
  const [statusWeatherCode, setstatusWeatherCode] = useState<string | null>(null);
  const [loading, setLoading] = useState<boolean>(false);
  const [loadingSymbol, setLoadingSymbol] = useState<string>('.');
  const outputRef = useRef<HTMLDivElement>(null);  // LLM 응답을 삽입할 위치.

  // statusWeatherCode에 따른 아이콘 결정
  const getIcon = (status: string | null) => {
    if (status === "0") {
      return "no result"; // 결과 없음
    } else if (status === "1") {
      return "☀️"; // 맑음
    } else if (status === "2") {
      return "🌥️"; // 흐림
    } else if (status === "3") {
      return "⛈️"; // 비
    }
    return null;
  };

  // 로딩 중 기호 순환
  useEffect(() => {
    if (loading) {
      const symbols = ['.', '..', '...', ''];
      let index = 0;
      const interval = setInterval(() => {
        setLoadingSymbol(symbols[index]);
        index = (index + 1) % symbols.length;
      }, 500);
      return () => clearInterval(interval);
    }
  }, [loading]);

  // 페이지 데이터 요청 함수
  const fetchPageData = async () => {
    setLoading(true); // 로딩 시작
    try {
      const logFilePath = './LOG/test_log_access'; // Log file path for testing
      const logArrayResponse = await fetch('http://localhost:8445/APIs/log_array.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({ filePath: logFilePath }),
      });

      if (!logArrayResponse.ok) {
          throw new Error('Failed to fetch log array');
      }

      const logArray = await logArrayResponse.json();
      const prompt = `get logs and please make a security report within 100 words. Logs:\n${logArray.join('\n')}`;

      const response = queryLLM(prompt); // LLM에 데이터 요청. 모든 로그를 함께 전송함.
      if (outputRef.current) {
        outputRef.current.innerHTML = ''; // Clear previous content
      }
      for await (const { response: chunk, done } of response) {
        if (done === 'true') { break; }  // done: true  <-- LLM의 마지막 응답 신호.
        if (outputRef.current) {  // done: true  <-- LLM의 마지막 응답 신호.
          outputRef.current.innerHTML += chunk;  // LLM 응답 삽입. 
        }
      }
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      if (outputRef.current) {
        outputRef.current.innerHTML = await marked(outputRef.current.innerHTML);  // 마크다운으로 변환하여 삽입.
      }
      setLoading(false); // 로딩 종료
    }
  };

  return (
    <div className="flex flex-grow gap-4 p-4">
      {/* 왼쪽: 시스템 정보 */}
      <div className="w-1/2 p-2 bg-white rounded-lg dark:bg-gray-800">
        <h1 className="text-3xl font-bold">오늘의 상태</h1>
        <div className="text-center mt-10">
          {statusWeatherCode && (
            <p className="text-7xl">{getIcon(statusWeatherCode)}</p> // 상태에 맞는 아이콘 표시
          )}
        </div>
      </div>

      {/* 오른쪽: 서버 정보 */}
      <div className="w-1/2 p-2 bg-white rounded-lg dark:bg-gray-800">
        <h1 className="text-3xl font-bold">서버 정보</h1>
        <div className="mt-4">
          <button onClick={fetchPageData} className="btn btn-primarye underline">
            보고서 생성 {loading && <span><small>최초 응답까지 약 10초 ~ 5분 정도 소요됩니다</small>&nbsp;&nbsp; Loading{loadingSymbol}</span>} {/* 로딩 중일 때 기호 표시 */}
          </button>
          <div className="mt-4 markdown-container">
            <div ref={outputRef}></div> {/* LLM 응답을 표시할 위치 */}
          </div>
        </div>
      </div>
    </div>
  );
}

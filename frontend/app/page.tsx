"use client";

import React, { useState, useRef, useEffect } from "react";
import { queryLLM } from "./lib/queryLLM";
import { marked } from "marked";
import "./styles/markdown.css";
import { LineChart, Line, XAxis, YAxis, Tooltip, ResponsiveContainer } from "recharts";

// 방문자 데이터 타입
type VisitorData = {
  time: string;
  count: number;
};

export default function Page() {
  const [loading, setLoading] = useState<boolean>(false);
  const [loadingSymbol, setLoadingSymbol] = useState<string>(".");
  const outputRef = useRef<HTMLDivElement>(null);
  const [visitorData, setVisitorData] = useState<VisitorData[]>([]);
  const [maxCount, setMaxCount] = useState<number>(10);

  // 로딩 중 기호 순환
  useEffect(() => {
    if (loading) {
      const symbols = [".", "..", "...", ""];
      let index = 0;
      const interval = setInterval(() => {
        setLoadingSymbol(symbols[index]);
        index = (index + 1) % symbols.length;
      }, 500);
      return () => clearInterval(interval);
    }
  }, [loading]);

  // 방문자 데이터 가져오기
  useEffect(() => {
    const fetchVisitorData = async () => {
      try {
        const response = await fetch("http://localhost:8445/APIs/log_graph.php");
        if (!response.ok) throw new Error("Failed to fetch visitor data");

        const data: VisitorData[] = await response.json();
        const counts = data.map((d) => d.count);
        setVisitorData(data);
        setMaxCount(Math.max(...counts, 10)); // 최대값 계산
      } catch (error) {
        console.error("Error fetching visitor data:", error);
      }
    };

    fetchVisitorData();
  }, []);

  // 페이지 데이터 요청 함수
  const fetchPageData = async () => {
    setLoading(true);
    try {
      const logFilePath = "./LOG/test_log_access";
      const logArrayResponse = await fetch("http://localhost:8445/APIs/log_array.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ filePath: logFilePath }),
      });

      if (!logArrayResponse.ok) throw new Error("Failed to fetch log array");

      const logArray = await logArrayResponse.json();
      const prompt = `get logs and please make a security report within 100 words. Logs:\n${logArray.join("\n")}`;

      const response = queryLLM(prompt);
      if (outputRef.current) outputRef.current.innerHTML = "";

      for await (const { response: chunk, done } of response) {
        if (done === "true") break;
        if (outputRef.current) outputRef.current.innerHTML += chunk;
      }
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      if (outputRef.current) {
        outputRef.current.innerHTML = await marked(outputRef.current.innerHTML);
      }
      setLoading(false);
    }
  };

  return (
    <div className="flex flex-grow gap-4 p-4">
      {/* 방문자 그래프 */}
      <div className="w-1/2 p-2 bg-white rounded-lg dark:bg-gray-800">
       <h1 className="text-3xl font-bold">오늘의 상태 (1분 단위 접속자 수)</h1>
        <ResponsiveContainer width="100%" height={300}>
          <LineChart data={visitorData.length > 0 ? visitorData : [{ time: "No Data", count: 0 }]}>
            <XAxis
              dataKey="time"
              tickFormatter={(tick: any) => String(tick)}
              interval={Math.max(1, Math.floor(visitorData.length / 10))}
            />
            <YAxis domain={[0, maxCount > 0 ? maxCount : 10]} />
            <Tooltip />
            <Line type="monotone" dataKey="count" stroke="#8884d8" dot={false} />
          </LineChart>
        </ResponsiveContainer>
      </div>

      {/* 서버 정보 */}
      <div className="w-1/2 p-2 bg-white rounded-lg dark:bg-gray-800">
        <div className="flex justify-between items-center mb-4">
          <h1 className="text-3xl font-bold">서버 정보</h1>
          <button onClick={fetchPageData} className="p-2 bg-blue-500 text-white rounded">
            {!loading ? <span>Analyze</span> : <span>Loading{loadingSymbol}</span>}
          </button>
          <small>최초 응답까지 약 10초 ~ 5분 정도 소요됩니다</small>
        </div>
        <div className="mt-4 markdown-container">
          <div ref={outputRef}></div>
        </div>
      </div>
    </div>
  );
}

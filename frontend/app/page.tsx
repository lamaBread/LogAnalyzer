"use client";

import React, { useState, useEffect } from "react";
import { MainPageCall } from './lib/mainpageCall';

export default function Page() {
  const [variable1, setVariable1] = useState<string | null>(null);
  const [variable2, setVariable2] = useState<string | null>(null);

  useEffect(() => {
    async function fetchData() {
      try {
        const data = await MainPageCall();
        
        
        setVariable1(data.mainText);
        setVariable2(data.statusValue);
      } catch (error) {
        console.error("Error fetching data:", error);
      }
    }

    fetchData();
  }, []);

   // variable2에 따른 아이콘 결정
   const getIcon = (status: string | null) => {
    if (status === "1") {
      return "☀️🌞"; // 맑음
    } else if (status === "2") {
      return "⛅️🌥️"; // 흐림
    } else if (status === "3") {
      return "🌧⛈️🌩️"; // 비
    }
    return null;
  };
  

  return (
    <div className="flex flex-grow gap-4 p-4">
      {/* 왼쪽: 시스템 정보 */}
      <div className="w-1/2 p-2 bg-white rounded-lg dark:bg-gray-800">
      <h1 className="text-3xl font-bold">오늘의 상태</h1>
        <div className="text-center mt-10">
          {variable2 && (
            <p className="text-7xl">{getIcon(variable2)}</p> // 상태에 맞는 아이콘 표시
          )}
        </div>
      </div>

      {/* 오른쪽: 서버 정보 */}
      <div className="w-1/2 p-2 bg-white rounded-lg dark:bg-gray-800">
        <h1 className="text-3xl font-bold">서버 정보</h1>
        <div>
          {variable1 === null || variable2 === null ? (
            <p>Loading...</p>
          ) : (
            <>
              {/* dangerouslySetInnerHTML 사용하여 HTML 렌더링 */}
              <div dangerouslySetInnerHTML={{ __html: variable1 }} />
              <p>Status Value: {variable2}</p>
            </>
          )}
        </div>
      </div>
    </div>
  );
}

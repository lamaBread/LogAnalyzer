"use client";

import React, { useState } from "react";
import { PageCall } from './lib/pageCall';

export default function Page() {
  const [variable1, setVariable1] = useState<string | null>(null);
  const [variable2, setVariable2] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState<boolean>(false);

  // variable2에 따른 아이콘 결정
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

  // 페이지 데이터 요청 함수
  const fetchPageData = async () => {
    setIsLoading(true); // 데이터 로딩 시작
    try {
      const data = await PageCall('mainPage');
      setVariable1(data.mainText);
      setVariable2(data.statusValue);
    } catch (error) {
      console.error("Error fetching data:", error);
    } finally {
      setIsLoading(false); // 데이터 로딩 종료
    }
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
        <div className="mt-4">
          {/* 데이터가 없을 때 버튼을 보여줌 */}
          {!variable1 && !variable2 && !isLoading ? (
            <button onClick={fetchPageData} className="btn btn-primarye underline">
              보고서 출력
            </button>
          ) : (
            <>
              {isLoading ? (
                <p>Loading...</p> // 데이터 로딩 중일 때 메시지
              ) : (
                <>
                  {/* variable1이 null이 아닌 경우에만 dangerouslySetInnerHTML 사용 */}
                  {variable1 && (
                    <div
                      dangerouslySetInnerHTML={{ __html: variable1 }} // HTML 렌더링
                    />
                  )}
                </>
              )}
            </>
          )}
        </div>
      </div>
    </div>
  );
}

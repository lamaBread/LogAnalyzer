"use client";

import React, { useState, useEffect, useRef } from "react";
import StatusLayout from "../../../components/StatusLayout";
import { fetchLogsAndGenerateReport } from "@/app/lib/getReportByStatusCode";
import { getLogs } from "@/app/lib/getLogs";
import { marked } from "marked";
import '../../../styles/markdown.css';

// chatLLM 함수 (스트리밍 API 호출용, ai_search 고유)
export async function* chatLLM(
  messages: Array<{ role: string; content: string }>,
  model: string = "llama3.2:1b"
) {
  try {
    const response = await fetch("http://127.0.0.1:11434/api/chat", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        model: model,
        messages: messages,
        stream: true,
      }),
    });

    if (!response.body) {
      throw new Error("No response body");
    }

    const reader = response.body.getReader();
    const decoder = new TextDecoder();
    let done = false;
    let buffer = "";

    while (!done) {
      const { value, done: readerDone } = await reader.read();
      done = readerDone;
      buffer += decoder.decode(value, { stream: true });

      let boundary = buffer.indexOf("\n");
      while (boundary !== -1) {
        const chunk = buffer.slice(0, boundary);
        buffer = buffer.slice(boundary + 1);
        if (chunk.trim()) {
          try {
            const json = JSON.parse(chunk);
            yield json;
          } catch (e) {
            console.error("Failed to parse JSON chunk:", e);
          }
        }
        boundary = buffer.indexOf("\n");
      }
    }
  } catch (error) {
    console.error("Failed to fetch data:", error);
    throw error;
  }
}

export default function AiSearchPage() {
  // 여기에서 ai_search 특화 로직을 구현하세요.
  // 예: 메시지 입력, chatLLM 호출, 스트리밍 결과 출력 등

  return (
    <div>
      <StatusLayout>
        <h1 className="text-2xl font-bold mb-4">AI Search Page</h1>
        {/* AI 검색 UI 및 결과 출력 영역 구현 */}
      </StatusLayout>
    </div>
  );
}

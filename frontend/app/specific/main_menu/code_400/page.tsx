"use client";

import React, { useState, useEffect, useRef } from "react";
import StatusLayout from "../../../components/StatusLayout";
import { fetchLogsAndGenerateReport } from "@/app/lib/getReportByStatusCode";
import { getLogs } from "@/app/lib/getLogs";
import { marked } from "marked";
import '../../../styles/markdown.css';


async function fetchFilteredLogs(prefix: string) {
  try {
    const logs = await getLogs("statusCode");
    return Object.keys(logs)
      .filter((key) => key.startsWith(prefix))
      .flatMap((key) => logs[key]);
  } catch (error) {
    console.error("Error fetching data:", error);
    return [];
  }
}

function useLoadingSymbol(
  loading: boolean,
  setLoadingSymbol: React.Dispatch<React.SetStateAction<string>>
) {
  React.useEffect(() => {
    if (!loading) return;

    const symbols = [".", "..", "...", ""];
    let index = 0;
    const interval = setInterval(() => {
      setLoadingSymbol(symbols[index]);
      index = (index + 1) % symbols.length;
    }, 500);

    return () => clearInterval(interval);
  }, [loading, setLoadingSymbol]);
}

async function analyzeLogs(
  logs: string[],
  prompt: string,
  reportRef: React.RefObject<HTMLDivElement>
) {
  await fetchLogsAndGenerateReport(logs, prompt, reportRef);
  if (reportRef.current) {
    reportRef.current.innerHTML = await marked(reportRef.current.innerHTML);
  }
}

export default function Code400Page() {
  const [loading, setLoading] = useState(false);
  const [loadingSymbol, setLoadingSymbol] = useState(".");
  const [logs, setLogs] = useState<string[]>([]);
  const reportRef = useRef<HTMLDivElement>(null);

  const leadingPrompt =
    "Analyze the logs with status codes ranging from 400 to 499 and generate a security report about the server status within 100 words.";

  useEffect(() => {
    async function fetchData() {
      const filteredLogs = await fetchFilteredLogs("4");
      setLogs(filteredLogs);
    }
    fetchData();
  }, []);

  useLoadingSymbol(loading, setLoadingSymbol);

  async function handleAnalyze() {
    setLoading(true);
    await analyzeLogs(logs, leadingPrompt, reportRef);
    setLoading(false);
  }

  return (
    <div>
      <StatusLayout>
        <div className="flex justify-between items-center mb-4">
          <h1 className="text-2xl font-bold">Status Code 400-499</h1>
          <button
            onClick={handleAnalyze}
            className="p-2 bg-gray-500 text-white rounded"
          >
            {!loading ? "Analyze" : `Loading${loadingSymbol}`}
          </button>
        </div>
        <div ref={reportRef} className="mb-4 markdown-container" />
      </StatusLayout>
      {logs.length > 0 ? (
        logs.map((log, idx) => <div key={idx}>{log}</div>)
      ) : (
        <p>No result...</p>
      )}
    </div>
  );
}

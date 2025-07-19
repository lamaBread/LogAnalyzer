"use client";

import React, { useState, useEffect, useRef } from "react";
import StatusLayout from "../../../components/StatusLayout";
import { fetchLogsAndGenerateReport } from "@/app/lib/getReportByStatusCode";
import { getLogs } from "@/app/lib/getLogs";
import { marked } from "marked";
import '../../../styles/markdown.css';

export default function Code300Page() {
  const [loading, setLoading] = useState<boolean>(false);
  const [logs, setLogs] = useState<string[]>([]);
  const [loadingSymbol, setLoadingSymbol] = useState<string>('.');
  const reportRef = useRef<HTMLDivElement>(null);
  const leadingPrompt = "Analyze the logs with status codes ranging from 300 to 399 and generate a security report about the server status within 100 words. ";

  useEffect(() => {
    async function fetchData() {
      try {
        const logs = await getLogs('statusCode');
        const filteredLogs = Object.keys(logs)
          .filter(key => key.startsWith('3'))
          .flatMap(key => logs[key]);
        setLogs(filteredLogs);
      } catch (error) {
        console.error("Error fetching data:", error);
      }
    }

    fetchData();
  }, []);

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

  async function handleAnalyze() {
    setLoading(true);
    await fetchLogsAndGenerateReport(logs, leadingPrompt, reportRef);
    setLoading(false);
    if (reportRef.current) {
      reportRef.current.innerHTML = await marked(reportRef.current.innerHTML);
    }
  }

  return (
    <div>
      <StatusLayout>
        <div className="flex justify-between items-center mb-4">
          <h1 className="text-2xl font-bold">Status Code 300-399</h1>
          <button onClick={handleAnalyze} className="p-2 bg-gray-500 text-white rounded">
            {!loading && <span>Analyze</span>}
            {loading && <span>Loading{loadingSymbol}</span>}
          </button>
        </div>
        <div ref={reportRef} className="mb-4 markdown-container"></div>
      </StatusLayout>
      {logs.length > 0 ? (
        <div>
          {logs.map((log, index) => (
            <div key={index}>{log}</div>
          ))}
        </div>
      ) : (
        <p>No result...</p>
      )}
    </div>
  );
}
"use client";

import React, { useState, useEffect } from "react";
import StatusLayout from "../../../components/StatusLayout";
import { getLogs } from "@/app/lib/getLogs";

export default function Code400Page() {
  const [logs, setLogs] = useState<string[]>([]);

  useEffect(() => {
    async function fetchData() {
      try {
        const logs = await getLogs('statusCode');  // 배열이 반환됨.
        const filteredLogs = Object.keys(logs)
          .filter(key => key.startsWith('4'))
          .flatMap(key => logs[key]);
        setLogs(filteredLogs);
      } catch (error) {
        console.error("Error fetching data:", error);
      }
    }

    fetchData();
  }, []);

  return (
    <div>
      <StatusLayout>
        <h1 className="text-2xl font-bold mb-4">Status Code 400-499</h1>
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
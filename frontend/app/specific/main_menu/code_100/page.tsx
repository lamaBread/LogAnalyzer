"use client";

import React, { useState, useEffect } from "react";
import StatusLayout from "../../../components/StatusLayout";
import { getLogs } from "@/app/lib/getLogs";

export default function Code100Page() {
  const [variable1, setVariable1] = useState<string | null>(null);

  useEffect(() => {
      async function fetchData() {
        try {
          const logs = getLogs('100');
          const parsedLogs = JSON.parse(await logs);
          
          setVariable1(await parsedLogs);
        } catch (error) {
          console.error("Error fetching data:", error);
        }
      }
  
      fetchData();
    }, []);
  
  return (
    <div>
      <StatusLayout>
        <h1 className="text-2xl font-bold mb-4">Status Code 100</h1>  
      </StatusLayout>
      {variable1 ? <div dangerouslySetInnerHTML={{ __html: variable1 }} /> : <p>Loading...</p>}
    </div>
    );
}
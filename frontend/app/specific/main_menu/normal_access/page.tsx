"use client";

import React, { useState, useEffect } from "react";
import { sortedLogs, sortLogsByRisk } from "../../../lib/queryLLM_sortLogsByRisk";

export default function normal_access() {
    const [variable1, setVariable1] = useState<string | null>(null);
    
    useEffect(() => {
        async function fetchData() {
            try {
                if (!sortedLogs) {
                    const logs = await sortLogsByRisk();
                    setVariable1(logs.normal_access);
                } else {
                    setVariable1(sortedLogs.normal_access);
                }
            } catch (error) {
                console.error("Error fetching data:", error);
            }
        }
      
        fetchData();
    }, []);  
  
    return (
      <>
        <h2 className="text-2xl font-semibold mb-4">Normal Access</h2>
        {variable1 ? <div dangerouslySetInnerHTML={{ __html: variable1.split('<br/>').join('<br/>') }} /> : <p>Loading...</p>}
      </>
    );
}
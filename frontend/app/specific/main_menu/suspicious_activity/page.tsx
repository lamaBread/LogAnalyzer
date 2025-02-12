"use client";

import React, { useState, useEffect } from "react";
import StatusLayout from "../../../components/StatusLayout";

export default function suspicious_activity() {
    const [variable1, setVariable1] = useState<string | null>(null);
        
          useEffect(() => {
              async function fetchData() {
                try {
                  // 의심되는 로그 출력.
                } catch (error) {
                  console.error("Error fetching data:", error);
                }
              }
          
              fetchData();
            }, []);

    return (
      <>
        <h2 className="text-2xl font-semibold mb-4">Suspicious Activity</h2>
        {variable1 ? <div dangerouslySetInnerHTML={{ __html: variable1 }} /> : <p>Loading...</p>}
      </>  
    );
}
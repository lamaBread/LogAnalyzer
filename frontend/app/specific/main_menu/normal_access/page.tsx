"use client";

import React, { useState, useEffect } from "react";
import StatusLayout from "../../../components/StatusLayout";

export default function normal_access() {
    const [variable1, setVariable1] = useState<string | null>(null);
    
      useEffect(() => {
          async function fetchData() {
            try {
              // 정상접속으로 분류된 로그를 출력할 것.
            } catch (error) {
              console.error("Error fetching data:", error);
            }
          }
      
          fetchData();
        }, []);  
  
    return (
      <>
        <h2 className="text-2xl font-semibold mb-4">Normal Access</h2>
        {variable1 ? <div dangerouslySetInnerHTML={{ __html: variable1 }} /> : <p>Loading...</p>}
      </>

    );
}
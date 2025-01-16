"use client";

import React, { useState, useEffect } from "react";
import StatusLayout from "../../../components/StatusLayout";
import { PageCall } from "../../../lib/pageCall";

export default function Code500Page() {
  const [variable1, setVariable1] = useState<string | null>(null);

  useEffect(() => {
      async function fetchData() {
        try {
          const data = await PageCall('500');
          
          
          setVariable1(data.mainText);
        } catch (error) {
          console.error("Error fetching data:", error);
        }
      }
  
      fetchData();
    }, []);
  
  return (
    <div>
      <StatusLayout>
        <h1 className="text-2xl font-bold mb-4">Status Code 500</h1>  
      </StatusLayout>
      {variable1 ? <div dangerouslySetInnerHTML={{ __html: variable1 }} /> : <p>Loading...</p>}
    </div>
    );
}
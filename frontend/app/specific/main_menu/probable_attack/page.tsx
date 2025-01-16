"use client";

import React, { useState, useEffect } from "react";
import StatusLayout from "../../../components/StatusLayout";
import { PageCall } from "../../../lib/pageCall";

export default function nomal_access() {
    const [variable1, setVariable1] = useState<string | null>(null);
        
          useEffect(() => {
              async function fetchData() {
                try {
                  const data = await PageCall('probPage');
                  
                  
                  setVariable1(data.mainText);
                } catch (error) {
                  console.error("Error fetching data:", error);
                }
              }
          
              fetchData();
            }, []);

    return (
      <>
        <h2 className="text-2xl font-semibold mb-4">Probable Attack</h2>
        {variable1 ? <div dangerouslySetInnerHTML={{ __html: variable1 }} /> : <p>Loading...</p>}
      </>
    );
}
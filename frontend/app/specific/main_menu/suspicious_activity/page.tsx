"use client";

import React, { useState } from "react";

export default function SuspiciousActivity() {
  const [filePath, setFilePath] = useState("");
  const [suspicionData, setSuspicionData] = useState<Record<string, any> | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [sortBy, setSortBy] = useState("score");
  const [sortDirection, setSortDirection] = useState("desc");

  const analyzeLogs = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setError(null);
    
    try {
      const formData = new FormData();
      formData.append("filePath", filePath);
      
      const response = await fetch('http://localhost:8445/APIs/evaluate_suspicion_for_each_IP.php', {
        method: "POST",
        body: formData,
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      
      const data = await response.json();
      setSuspicionData(data);
    } catch (err) {
      setError((err as Error).message || "An unknown error occurred");
      console.error("Error fetching data:", err);
    } finally {
      setIsLoading(false);
    }
  };

  const getSortedIPs = () => {
    if (!suspicionData) return [];
    
    return Object.entries(suspicionData)
      .sort(([ipA, dataA], [ipB, dataB]) => {
        const valueA = (dataA as any)[sortBy];
        const valueB = (dataB as any)[sortBy];
        
        if (sortDirection === "asc") {
          return valueA > valueB ? 1 : -1;
        } else {
          return valueA < valueB ? 1 : -1;
        }
      })
      .map(([ip, data]) => ({ ip, ...data as any }));
  };

  const handleSort = (field: string) => {
    if (sortBy === field) {
      setSortDirection(sortDirection === "asc" ? "desc" : "asc");
    } else {
      setSortBy(field);
      setSortDirection("desc");
    }
  };

  const getSeverityClass = (score: number) => {
    if (score >= 0.8) return "bg-red-100 border-red-500 text-red-700";
    if (score >= 0.4) return "bg-yellow-100 border-yellow-500 text-yellow-700";
    return "bg-green-100 border-green-500 text-green-700";
  };

  const getSeverityIcon = (score: number) => {
    if (score >= 0.8) return <span className="text-red-500 mr-2 font-bold">🔴</span>;
    if (score >= 0.4) return <span className="text-yellow-500 mr-2 font-bold">🟡</span>;
    return <span className="text-green-500 mr-2 font-bold">🟢</span>;
  };

  return (
    <div className="p-6 max-w-7xl mx-auto">
      <h1 className="text-3xl font-bold mb-6">Suspicious Activity Analysis</h1>
      
      <div className="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 className="text-xl font-semibold mb-4">Analyze Log File</h2>
        <form onSubmit={analyzeLogs} className="flex flex-col md:flex-row gap-4">
          <input
            type="text"
            value={filePath}
            onChange={(e) => setFilePath(e.target.value)}
            placeholder="Enter log file path (e.g., ./LOG/combine_access.log)"
            className="flex-grow p-2 border rounded-md"
            required
          />
          <button
            type="submit"
            disabled={isLoading}
            className="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-md transition duration-200"
          >
            {isLoading ? "Analyzing..." : "Analyze"}
          </button>
        </form>
      </div>

      {error && (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
          <strong className="font-bold">Error: </strong>
          <span>{error}</span>
        </div>
      )}

      {isLoading && (
        <div className="text-center p-8">
          <div className="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent"></div>
          <p className="mt-2 text-gray-600">Analyzing logs, please wait...</p>
        </div>
      )}

      {suspicionData && !isLoading && (
        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-xl font-semibold mb-4">Analysis Results</h2>
          
          <div className="mb-4">
            <p className="text-gray-600">
              Found {Object.keys(suspicionData).length} unique IP addresses in the logs.
            </p>
          </div>
          
          <div className="overflow-x-auto">
            <table className="min-w-full bg-white border border-gray-200">
              <thead>
                <tr className="bg-gray-100">
                  <th className="py-2 px-4 border-b cursor-pointer" onClick={() => handleSort("ip")}>
                    IP Address {sortBy === "ip" && (sortDirection === "asc" ? "↑" : "↓")}
                  </th>
                  <th className="py-2 px-4 border-b cursor-pointer" onClick={() => handleSort("score")}>
                    Suspicion Score {sortBy === "score" && (sortDirection === "asc" ? "↑" : "↓")}
                  </th>
                  <th className="py-2 px-4 border-b cursor-pointer" onClick={() => handleSort("totalLogs")}>
                    Total Logs {sortBy === "totalLogs" && (sortDirection === "asc" ? "↑" : "↓")}
                  </th>
                  <th className="py-2 px-4 border-b cursor-pointer" onClick={() => handleSort("suspiciousCount")}>
                    Suspicious Logs {sortBy === "suspiciousCount" && (sortDirection === "asc" ? "↑" : "↓")}
                  </th>
                  {/* <th className="py-2 px-4 border-b">Attack Types</th> */}
                  <th className="py-2 px-4 border-b">Details</th>
                </tr>
              </thead>
              <tbody>
                {getSortedIPs().map((item) => (
                  <tr key={item.ip} className="hover:bg-gray-50">
                    <td className="py-2 px-4 border-b">{item.ip}</td>
                    <td className="py-2 px-4 border-b">
                      <div className={`flex items-center px-3 py-1.5 rounded-full border ${getSeverityClass(item.score)}`}>
                        {getSeverityIcon(item.score)}
                        {item.score.toFixed(2)}
                      </div>
                    </td>
                    <td className="py-2 px-4 border-b">{item.totalLogs}</td>
                    <td className="py-2 px-4 border-b">{item.suspiciousCount}</td>
                    {/* <td className="py-2 px-4 border-b">
                      {item.detectedAttacks && item.detectedAttacks.length > 0 ? (
                        <details className="cursor-pointer">
                          <summary className="text-blue-600 hover:text-blue-800">
                            {item.detectedAttacks.length} type(s) detected
                          </summary>
                          <div className="mt-2 ml-4 text-sm">
                            <ul className="list-disc ml-4 mt-2 text-gray-700">
                              {item.detectedAttacks.map((attack: any, index: number) => (
                                <li key={index} className="mb-2">
                                  <div className="font-semibold">{attack.attackType}</div>
                                  <div className="text-gray-600 ml-2">{attack.attackDetails}</div>
                                  <div className="text-gray-500 ml-2 text-xs">Found in {attack.count} log(s)</div>
                                </li>
                              ))}
                            </ul>
                          </div>
                        </details>
                      ) : (
                        <span className="text-gray-500 italic">None</span>
                      )}
                    </td> */}
                    <td className="py-2 px-4 border-b">
                      <details className="cursor-pointer">
                        <summary className="text-blue-600 hover:text-blue-800">
                          Show suspicious logs ({item.suspiciousLogs?.length || 0})
                        </summary>
                        <div className="mt-2 ml-4 text-sm">
                          {item.suspiciousLogs && item.suspiciousLogs.length > 0 ? (
                            <ul className="list-disc ml-4 mt-2 text-gray-700">
                              {item.suspiciousLogs.map((logItem: any, index: number) => (
                                <li key={index} className="mb-3 break-all">
                                  <code className="bg-gray-100 px-1 block mb-2">{typeof logItem === 'object' ? logItem.log : logItem}</code>
                                  {typeof logItem === 'object' && logItem.detectedPatterns && (
                                    <div className="mt-1 ml-2">
                                      <p className="text-xs font-semibold text-gray-600">Detected patterns:</p>
                                      <ul className="list-circle ml-4">
                                        {logItem.detectedPatterns.map((pattern: any, patternIndex: number) => (
                                          <li key={patternIndex} className="text-xs mt-1">
                                            <span className="font-medium">{pattern.attackType}:</span> {pattern.attackDetails}
                                            <div className="text-gray-500">Pattern: <code>{pattern.pattern}</code></div>
                                          </li>
                                        ))}
                                      </ul>
                                    </div>
                                  )}
                                </li>
                              ))}
                            </ul>
                          ) : (
                            <p className="text-gray-500 italic">No suspicious logs detected</p>
                          )}
                        </div>
                      </details>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}
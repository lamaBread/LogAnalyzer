import { queryLLM } from "@/app/lib/queryLLM";

export async function fetchLogsAndGenerateReport(logs: string[], leadingPrompt: string, reportRef: React.RefObject<HTMLDivElement>) {
  try {
    const userPrompt = `${leadingPrompt}\n${logs.join('\n')}`;
    if (reportRef.current) {
      reportRef.current.innerHTML = ''; // Clear previous content
    }
    for await (const { response } of queryLLM(userPrompt)) {
      if (reportRef.current) {
        reportRef.current.innerHTML += response;
      }
    }
  } catch (error) {
    console.error("Error processing logs:", error);
  }
}

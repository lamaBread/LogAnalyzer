import { getLogs } from './getLogs';

// 중복 연산을 피하기 위한 전역변수.
export let sortedLogs: { normal_access: string, probable_attack: string, suspicious_activity: string } | null = null;

export async function sortLogsByRisk() {
  try {
    const logs = await getLogs('classifying'); // 가공되지 않은 모든 로그를 가져옴
    const userPrompt = `Classify the following logs into three categories: normal_access, probable_attack, and suspicious_activity. Ensure each log is assigned to only one category.

    - normal_access: Logs that are considered safe access.
    - suspicious_activity: Logs that are weakly suspected to be an attack.
    - probable_attack: Logs that are strongly suspected to be an attack.
    
    Logs:\n${JSON.stringify(logs)}`;
    
    const response = await fetch('http://127.0.0.1:11434/api/generate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        model: 'llama3.2:1b',
        prompt: userPrompt,
        stream: false,
        format: {
          type: 'object',
          properties: {
            normal_access: { type: 'string' },
            probable_attack: { type: 'string' },
            suspicious_activity: { type: 'string' },
          },
          required: ['normal_access', 'probable_attack', 'suspicious_activity'],
        },
      }),
    });

    if (!response.ok) {
      throw new Error('Failed to fetch structured response');
    }

    const structuredResponse = await response.json();
    const { normal_access, probable_attack, suspicious_activity } = structuredResponse;

    sortedLogs = { normal_access, probable_attack, suspicious_activity };

    return structuredResponse;
  } catch (error) {
    console.error('Failed to sort logs by risk:', error);
    throw error;
  }
}

export async function* chatLLM(messages: Array<{ role: string, content: string }>, model: string = 'llama3.2:1b') {
  try {
    const response = await fetch(`http://127.0.0.1:11434/api/chat`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        model: model,
        messages: messages,
        stream: true,
      }),
    });

    if (!response.body) {
      throw new Error('No response body');
    }

    const reader = response.body.getReader();
    const decoder = new TextDecoder();
    let done = false;
    let buffer = '';

    while (!done) {
      const { value, done: readerDone } = await reader.read();
      done = readerDone;
      buffer += decoder.decode(value, { stream: true });

      let boundary = buffer.indexOf('\n');
      while (boundary !== -1) {
        const chunk = buffer.slice(0, boundary);
        buffer = buffer.slice(boundary + 1);
        if (chunk.trim()) {
          try {
            const json = JSON.parse(chunk);
            yield json;
          } catch (e) {
            console.error('Failed to parse JSON chunk:', e);
          }
        }
        boundary = buffer.indexOf('\n');
      }
    }
  } catch (error) {
    console.error('Failed to fetch data:', error);
    throw error;
  }
}

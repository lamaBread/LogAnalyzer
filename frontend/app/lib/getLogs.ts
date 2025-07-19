export async function getLogs(type: string) {
  try {
    if (type === "statusCode") {
      return await callLogAPI("group_by_statusCode_array.php");
    } else if (type === "classifying") {
      return await callLogAPI("log_array.php", "./LOG/test_log");
    } else if (type === "IP") {
      return await callLogAPI("group_by_IP_array.php");
    }
  } catch (error) {
    console.error("Failed to fetch data:", error);
    throw error;
  }
}

async function callLogAPI(module = "log_array.php", filePath = "./LOG/test_log_access") {
  try {
    const logArrayResponse = await fetch("http://localhost:8445/APIs/" + module, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ filePath }),
    });

    if (!logArrayResponse.ok) throw new Error("Failed to fetch log array");

    const responseText = await logArrayResponse.text();
    return JSON.parse(responseText);
  } catch (error) {
    console.error("Failed to fetch data:", error);
    throw error;
  }
}

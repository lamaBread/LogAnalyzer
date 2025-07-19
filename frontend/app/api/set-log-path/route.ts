import { NextResponse } from "next/server";
import db from "@/app/lib/db";

export async function POST(req: Request) {
  try {
    const { logPath } = await req.json();
    await db.query("UPDATE settings SET log_path = ? WHERE id = ?", [logPath, 1]);

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error("Error updating log path:", error);
    return NextResponse.json({ success: false, message: "로그 파일 경로 저장 실패" });
  }
}

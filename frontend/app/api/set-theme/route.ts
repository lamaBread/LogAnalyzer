import { NextRequest, NextResponse } from "next/server";
import db from "@/app/lib/db";

export async function POST(req: NextRequest) {
  try {
    const { theme } = await req.json();

    await db.query("UPDATE settings SET theme = ? WHERE id = 1", [theme]);

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error("테마 저장 실패:", error);
    return NextResponse.json({ success: false, message: "테마 저장 실패" });
  }
}

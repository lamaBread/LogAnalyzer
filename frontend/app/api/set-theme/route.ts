import { NextRequest, NextResponse } from "next/server";
import db from "@/app/lib/db"; // DB 연결 모듈

export async function POST(req: NextRequest) {
  const { theme } = await req.json();  // 테마 값 받기

  // DB에 테마 업데이트 (사용자 설정에 맞게 업데이트)
  await db.query('UPDATE settings SET theme = ? WHERE id = 1', [theme]);  // id는 예시입니다.

  return NextResponse.json({ success: true });
}

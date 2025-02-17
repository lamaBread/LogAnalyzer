import { NextRequest, NextResponse } from "next/server";
import db from "@/app/lib/db"; // DB 연결 모듈

export async function POST(req: NextRequest) {
  const { font } = await req.json();  // 글꼴 값 받기

  // DB에 글꼴 설정 업데이트 (사용자 설정에 맞게 업데이트)
  await db.query('UPDATE settings SET font = ? WHERE id = 1', [font]);  // id는 예시입니다.

  return NextResponse.json({ success: true });
}

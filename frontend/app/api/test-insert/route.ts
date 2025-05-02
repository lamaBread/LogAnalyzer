import { NextResponse } from "next/server";
import pool from "../../lib/db"; // db.ts 경로 맞춰줘
import { ResultSetHeader } from "mysql2"; // 추가!

export async function GET() {
  try {
    // query()의 결과를 ResultSetHeader 타입으로 캐스팅
    const [result] = await pool.query<ResultSetHeader>(
      "INSERT INTO conversation (question, answer, created_at) VALUES (?, ?, NOW())",
      ["Next.js test question", "Next.js test answer"]
    );

    return NextResponse.json({ success: true, insertedId: result.insertId });
  } catch (err) {
    if (err instanceof Error) {
      return NextResponse.json({ success: false, error: err.message });
    }
    return NextResponse.json({ success: false, error: "Unknown error" });
  }
}

import { NextResponse } from "next/server";
import pool from "../../lib/db";
import { ResultSetHeader } from "mysql2";

export async function GET() {
  try {
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

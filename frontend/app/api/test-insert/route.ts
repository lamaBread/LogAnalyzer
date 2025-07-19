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
  } catch (error) {
    const message = error instanceof Error ? error.message : "Unknown error";
    return NextResponse.json({ success: false, error: message });
  }
}

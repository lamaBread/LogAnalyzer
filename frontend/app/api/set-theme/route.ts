import { NextRequest, NextResponse } from "next/server";
import db from "@/app/lib/db";

export async function POST(req: NextRequest) {
  const { theme } = await req.json();

  await db.query('UPDATE settings SET theme = ? WHERE id = 1', [theme]);

  return NextResponse.json({ success: true });
}

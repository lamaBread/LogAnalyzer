import { NextRequest, NextResponse } from "next/server";
import db from "@/app/lib/db";

export async function POST(req: NextRequest) {
  const { font } = await req.json();

  await db.query('UPDATE settings SET font = ? WHERE id = 1', [font]);

  return NextResponse.json({ success: true });
}

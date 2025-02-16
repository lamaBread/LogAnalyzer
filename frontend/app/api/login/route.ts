import { NextRequest, NextResponse } from "next/server";

export async function POST(req: NextRequest) {
  const { username, password } = await req.json();

  if (username === "admin" && password === "password123") {
    return NextResponse.json({ success: true });
  } else {
    return NextResponse.json({ success: false });
  }
}

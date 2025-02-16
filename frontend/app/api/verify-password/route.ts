import { NextRequest, NextResponse } from "next/server";
const bcrypt = require("bcryptjs");

const hashedPassword = "$2a$10$ABCDEFG1234567890abcdefgHIJKL1234567890"; // 실제 비밀번호 해시 (bcrypt로 생성)

export async function POST(req: NextRequest) {
  const { password } = await req.json();

  const isMatch = await bcrypt.compare(password, hashedPassword);

  if (isMatch) {
    return NextResponse.json({ success: true });
  } else {
    return NextResponse.json({ success: false });
  }
}

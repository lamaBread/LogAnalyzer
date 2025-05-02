import { NextRequest, NextResponse } from "next/server";
const bcrypt = require("bcryptjs");

// 생성된 해시값
const hashedPassword = "$2b$10$g/6Pz0jFKGR6rnQiuvU4culIgSlgKD4LxI5S5KCIyiHVoWx9XoBUW";

export async function POST(req: NextRequest) {
  const { password } = await req.json();

  // 비밀번호와 해시값 비교
  const isMatch = await bcrypt.compare(password, hashedPassword);

  if (isMatch) {
    return NextResponse.json({ success: true });
  } else {
    return NextResponse.json({ success: false });
  }
}

import { NextRequest, NextResponse } from "next/server";
import bcrypt from "bcryptjs";

const hashedPassword = "$2b$10$g/6Pz0jFKGR6rnQiuvU4culIgSlgKD4LxI5S5KCIyiHVoWx9XoBUW";

export async function POST(req: NextRequest) {
  try {
    const { password } = await req.json();

    const isMatch = await bcrypt.compare(password, hashedPassword);

    return NextResponse.json({ success: isMatch });
  } catch (error) {
    console.error("비밀번호 검증 실패:", error);
    return NextResponse.json({ success: false, message: "검증 중 오류가 발생했습니다." });
  }
}

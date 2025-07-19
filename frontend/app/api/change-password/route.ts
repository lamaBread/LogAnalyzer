import { NextRequest, NextResponse } from "next/server";
import bcrypt from "bcryptjs";
import db from "@/app/lib/db";

export async function POST(req: NextRequest) {
  try {
    const { currentPassword, newPassword } = await req.json();

    const userId = 1; // 예시로 고정된 사용자 아이디

    const [rows]: any = await db.query("SELECT * FROM users WHERE id = ?", [userId]);
    const user = rows[0];
    if (!user) {
      return NextResponse.json({ success: false, message: "사용자를 찾을 수 없습니다." });
    }

    const isMatch = await bcrypt.compare(currentPassword, user.password);
    if (!isMatch) {
      return NextResponse.json({ success: false, message: "현재 비밀번호가 일치하지 않습니다." });
    }

    const hashedPassword = await bcrypt.hash(newPassword, 10);
    await db.query("UPDATE users SET password = ? WHERE id = ?", [hashedPassword, userId]);

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error("비밀번호 변경 실패:", error);
    return NextResponse.json({ success: false, message: "비밀번호 변경 중 오류가 발생했습니다." });
  }
}

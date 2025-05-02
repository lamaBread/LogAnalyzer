import { NextRequest, NextResponse } from "next/server";
import bcrypt from "bcryptjs"; // bcrypt 라이브러리로 비밀번호 비교 및 해싱
import db from "@/app/lib/db";  // DB 연결 모듈 (여기서는 db가 연결된 상태라고 가정)

export async function POST(req: NextRequest) {
  const { currentPassword, newPassword } = await req.json();  // 클라이언트에서 보낸 현재 비밀번호와 새 비밀번호

  // 예시로 사용자 ID가 1인 사용자라고 가정합니다.
  const userId = 1;

  // 1. 현재 비밀번호가 올바른지 확인하기
  const [rows]: any = await db.query("SELECT * FROM users WHERE id = ?", [userId]);
  const user = rows[0];

  if (!user) {
    return NextResponse.json({ success: false, message: "사용자를 찾을 수 없습니다." });
  }

  // 현재 비밀번호와 DB에 저장된 비밀번호 비교
  const isMatch = await bcrypt.compare(currentPassword, user.password);

  if (!isMatch) {
    return NextResponse.json({ success: false, message: "현재 비밀번호가 일치하지 않습니다." });
  }

  // 2. 새 비밀번호 해싱 후 DB에 저장하기
  const hashedPassword = await bcrypt.hash(newPassword, 10);  // 새 비밀번호 해싱
  await db.query("UPDATE users SET password = ? WHERE id = ?", [hashedPassword, userId]);  // DB에 새 비밀번호 저장

  return NextResponse.json({ success: true });  // 성공 응답
}

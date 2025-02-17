import { NextRequest, NextResponse } from "next/server";
import bcrypt from 'bcryptjs';
import db from '@/app/lib/db';  // DB 연결 모듈

export async function POST(req: NextRequest) {
  const { currentPassword, newPassword } = await req.json();  // 현재 비밀번호, 새 비밀번호 받아오기

  // 1. 현재 비밀번호 확인
  const [rows]: any = await db.query('SELECT * FROM users WHERE id = 1');  // 예시: id 1인 사용자
  const user = rows[0];

  if (!user) {
    return NextResponse.json({ success: false, message: "사용자를 찾을 수 없습니다." });
  }

  const isMatch = await bcrypt.compare(currentPassword, user.password);  // 비밀번호 비교

  if (!isMatch) {
    return NextResponse.json({ success: false, message: "현재 비밀번호가 일치하지 않습니다." });
  }

  // 2. 새 비밀번호 해싱 후 DB 업데이트
  const hashedPassword = await bcrypt.hash(newPassword, 10);
  await db.query('UPDATE users SET password = ? WHERE id = 1', [hashedPassword]);

  return NextResponse.json({ success: true });
}

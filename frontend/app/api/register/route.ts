import { NextRequest, NextResponse } from 'next/server';
const bcrypt = require('bcryptjs');
import { query } from 'app/lib/db'; // DB 연결 라이브러리

// POST 방식으로 요청 시
export async function POST(req: NextRequest) {
  const { username, password } = await req.json(); // 사용자 이름과 비밀번호

  // 비밀번호 해싱
  const hashedPassword = bcrypt.hashSync(password, 10);

  // DB에 사용자 정보 저장 (예: user 테이블에 username과 hashedPassword 저장)
  try {
    const result = await query(`
      INSERT INTO users (username, password) 
      VALUES (?, ?)`, 
      [username, hashedPassword]
    );

    return NextResponse.json({ message: '회원가입 성공' });
  } catch (error) {
    console.error(error);
    return NextResponse.json({ message: '회원가입 실패' }, { status: 500 });
  }
}

import { NextResponse } from "next/server";
import pool from "@/app/lib/db";

export async function POST(req: Request) {
  try {
    const { question, contexts } = await req.json();

    if (!question || question.trim().length < 1) {
      return NextResponse.json({ error: "검색어를 입력하세요." }, { status: 400 });
    }

    // AI 응답 예제 (실제 AI 로직 적용 가능)
    const aiAnswer = `AI는 당신이 묻는 질문: ${question}에 대해 이렇게 답변합니다.`;

    // 1️⃣ DB에 대화 내용 저장
    const connection = await pool.getConnection();
    await connection.execute(
      "INSERT INTO conversation (question, answer) VALUES (?, ?)",
      [question, aiAnswer]
    );
    connection.release();

    // 2️⃣ 응답 반환
    return NextResponse.json({ question, answer: aiAnswer });

  } catch (error) {
    console.error("API 오류:", error);
    return NextResponse.json({ error: "서버 오류 발생" }, { status: 500 });
  }
}

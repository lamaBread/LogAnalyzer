import { NextResponse } from "next/server";
import pool from "@/app/lib/db";

export async function POST(req: Request) {
  try {
    const { question, contexts } = await req.json();

    if (!question || question.trim().length < 1) {
      return NextResponse.json({ error: "검색어를 입력하세요." }, { status: 400 });
    }

    // 1️⃣ AI 응답 예제 (실제 AI 로직 적용 가능)
    const aiAnswer = `AI는 당신이 묻는 질문: ${question}에 대해 이렇게 답변합니다.`;

    // 2️⃣ DB에 대화 저장 (pool.execute() 사용)
    try {
      await pool.execute(
        "INSERT INTO conversation (question, answer) VALUES (?, ?)",
        [question, aiAnswer]
      );
    } catch (dbError) {
      console.error("❌ DB 저장 오류:", dbError);
      return NextResponse.json({ error: "데이터베이스 저장 실패" }, { status: 500 });
    }

    // 3️⃣ 응답 반환
    return NextResponse.json({ question, answer: aiAnswer });

  } catch (error) {
    console.error("❌ API 오류:", error);
    return NextResponse.json({ error: "서버 오류 발생" }, { status: 500 });
  }
}

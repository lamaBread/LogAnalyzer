import { NextResponse } from "next/server";
import pool from "@/app/lib/db"; // db.ts에서 default export한 pool을 가져옴

export async function POST(request: Request) {
  try {
    const { question, answer, contexts }: { question: string; answer: string; contexts: any[] } = await request.json();

    // 1. 질문과 답변을 conversation 테이블에 저장
    console.log("🔍 DB에 질문과 답변 저장 중...");

    const [rows] = await pool.query(
      `INSERT INTO conversation (question, answer) VALUES (?, ?)`,
      [question, answer]
    );

    // insertId는 rows 배열의 첫 번째 객체에 포함되어 있음
    const conversationId = (rows as any).insertId;
    console.log("✅ 대화 기록 삽입 완료. insertId:", conversationId);

    // 2. contexts를 context 테이블에 저장
    console.log("🔍 contexts 저장 중...");
    for (const context of contexts) {
      await pool.query(
        `INSERT INTO context (conversation_id, context_question, context_answer) VALUES (?, ?, ?)`,
        [conversationId, context.question, context.answer]
      );
    }
    console.log("✅ contexts 저장 완료.");

    // 3. AI 응답을 반환 (예시로 처리한 부분)
    const aiResponse = await getAIResponse(question, contexts); // 실제 AI 응답 처리 함수
    console.log("📦 AI 응답 데이터:", aiResponse);

    return NextResponse.json({ answer: aiResponse });
  } catch (error) {
    console.error("🔥 오류 발생:", error); // 오류 로그 추가
    return NextResponse.json({ error: "서버 오류가 발생했습니다." }, { status: 500 });
  }
}

// 예시: AI 응답을 얻는 함수 (실제 로직에 맞게 작성)
async function getAIResponse(question: string, contexts: any[]): Promise<string> {
  // 여기에 AI 응답을 얻는 로직을 넣으세요
  return `AI가 생성한 답변: ${question}`; // 예시 응답
}

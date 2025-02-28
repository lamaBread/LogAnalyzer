import { NextApiRequest, NextApiResponse } from 'next';
import pool from '@/app/lib/db'; // pool을 임포트합니다.

// 대화 기록을 DB에 저장하는 함수
const saveConversationToDB = async (question: string, answer: string, contexts: { question: string; answer: string }[]) => {
  let connection;
  try {
    connection = await pool.getConnection(); // 커넥션 풀에서 커넥션을 가져옵니다.
    console.log("DB 연결 성공");

    await connection.beginTransaction(); // 트랜잭션 시작

    // 1️⃣ 대화 기록을 `conversation` 테이블에 저장
    const [conversationResult]: any = await connection.query(
      'INSERT INTO conversation (question, answer) VALUES (?, ?)',
      [question, answer]
    );

    const conversationId = conversationResult?.insertId;
    if (!conversationId) {
      throw new Error('대화 기록 저장 실패: insertId가 없습니다.');
    }

    // 2️⃣ `contexts`가 있다면 `context` 테이블에 저장
    if (contexts && contexts.length > 0) {
      for (const context of contexts) {
        await connection.query(
          'INSERT INTO context (conversation_id, context_question, context_answer) VALUES (?, ?, ?)',
          [conversationId, context.question, context.answer]
        );
      }
    }

    await connection.commit(); // 트랜잭션 커밋
    return conversationId;
  } catch (error) {
    if (connection) {
      await connection.rollback(); // 오류 발생 시 트랜잭션 롤백
    }
    console.error('DB 저장 실패:', error);
    throw new Error('DB 저장 실패');
  } finally {
    if (connection) {
      connection.release(); // 커넥션을 풀에 반환
    }
  }
};

const handler = async (req: NextApiRequest, res: NextApiResponse) => {
  if (req.method === 'POST') {
    const { question, contexts } = req.body;

    if (!question || !contexts) {
      return res.status(400).json({ error: '잘못된 입력입니다.' });
    }

    try {
      // AI 응답을 생성 (여기서는 임시로 "AI의 답변" 사용)
      const aiAnswer = `AI의 답변: ${question}`;

      // DB에 대화 기록 저장
      const conversationId = await saveConversationToDB(question, aiAnswer, contexts);

      // 저장된 데이터를 포함한 응답을 클라이언트로 반환
      res.status(200).json({ answer: aiAnswer, conversationId });
    } catch (error) {
      console.error('서버 오류:', error);
      res.status(500).json({ error: '서버 오류가 발생했습니다.' });
    }
  } else {
    res.status(405).json({ error: 'Method Not Allowed' });
  }
};

export default handler;

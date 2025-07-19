import { NextApiRequest, NextApiResponse } from 'next';
import pool from '@/app/lib/db';

const saveConversationToDB = async (question: string, answer: string, contexts: { question: string; answer: string }[]) => {
  let connection;
  try {
    connection = await pool.getConnection(); 
    console.log("DB 연결 성공");

    await connection.beginTransaction();

    const [conversationResult]: any = await connection.query(
      'INSERT INTO conversation (question, answer, created_at) VALUES (?, ?, NOW())',
      [question, answer]
    );

    const conversationId = conversationResult?.insertId;
    if (!conversationId) {
      throw new Error('대화 기록 저장 실패: insertId가 없습니다.');
    }

    if (contexts && contexts.length > 0) {
      console.log(`Contexts 저장 시작:`, contexts);
      for (const context of contexts) {
        await connection.query(
          'INSERT INTO context (conversation_id, context_question, context_answer) VALUES (?, ?, ?)',
          [conversationId, context.question, context.answer]
        );
      }
      console.log(`Contexts 저장 완료`);
    }

    await connection.commit();
    return conversationId;
  } catch (error) {
    if (connection) {
      await connection.rollback();
    }
    console.error('DB 저장 실패:', error);
    throw new Error('DB 저장 실패');
  } finally {
    if (connection) {
      connection.release();
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
      const aiAnswer = `AI의 답변: ${question}`;

      const conversationId = await saveConversationToDB(question, aiAnswer, contexts);

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

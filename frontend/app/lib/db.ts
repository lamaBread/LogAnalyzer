import mysql from "mysql2/promise";
import { RowDataPacket } from 'mysql2';

// MySQL 커넥션 풀 설정
const pool = mysql.createPool({
  host: 'mysql',
  user: 'root',
  password: 'rootpw',
  database: 'conversations',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
});

// 대화 기록 조회 함수 (conversation만 조회)
export const getConversations = async (): Promise<RowDataPacket[]> => {
  try {
    const [rows] = await pool.query('SELECT * FROM conversation ORDER BY created_at DESC');
    return rows as RowDataPacket[]; // 명시적으로 타입을 RowDataPacket[]로 지정
  } catch (err) {
    console.error("Error fetching conversations:", err);
    throw new Error('대화 기록을 가져오는 데 실패했습니다.');
  }
};

// 대화 기록과 관련된 context 조회 함수
export const getConversationsWithContext = async (): Promise<any[]> => {
  try {
    const [rows] = await pool.query(
      `SELECT c.id, c.question, c.answer, c.created_at, ctx.context_question, ctx.context_answer
       FROM conversation c
       LEFT JOIN context ctx ON c.id = ctx.conversation_id
       ORDER BY c.created_at DESC`
    );
    return rows as RowDataPacket[]; // 명시적으로 타입을 RowDataPacket[]로 지정
  } catch (err) {
    console.error("Error fetching conversations with context:", err);
    throw new Error('대화 기록 및 관련 컨텍스트를 가져오는 데 실패했습니다.');
  }
};

// 일반적인 쿼리 실행 함수
export const query = async (sql: string, values?: any) => {
  try {
    console.log("Executing query:", sql, "with values:", values);
    const [rows] = await pool.execute(sql, values);
    return rows;
  } catch (err) {
    console.error("Error executing query:", err);
    throw new Error('쿼리 실행 중 오류가 발생했습니다.');
  }
};

export default pool; // 다른 곳에서 pool을 사용할 수 있도록 내보냄

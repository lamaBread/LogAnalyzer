import mysql from "mysql2/promise";
import { RowDataPacket } from 'mysql2';

const pool = mysql.createPool({
  host: 'mysql',
  user: 'root',
  password: 'rootpw',
  database: 'conversations',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
});

export const getConversations = async (): Promise<RowDataPacket[]> => {
  try {
    const [rows] = await pool.query('SELECT * FROM conversation ORDER BY created_at DESC');
    return rows as RowDataPacket[];
  } catch (err) {
    console.error("Error fetching conversations:", err);
    throw new Error('대화 기록을 가져오는 데 실패했습니다.');
  }
};

export const getConversationsWithContext = async (): Promise<any[]> => {
  try {
    const [rows] = await pool.query(
      `SELECT c.id, c.question, c.answer, c.created_at, ctx.context_question, ctx.context_answer
       FROM conversation c
       LEFT JOIN context ctx ON c.id = ctx.conversation_id
       ORDER BY c.created_at DESC`
    );
    return rows as RowDataPacket[];
  } catch (err) {
    console.error("Error fetching conversations with context:", err);
    throw new Error('대화 기록 및 관련 컨텍스트를 가져오는 데 실패했습니다.');
  }
};

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

export default pool;

import mysql from "mysql2/promise";

// MySQL 커넥션 풀 설정
const pool = mysql.createPool({
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME,
  waitForConnections: true,
  connectionLimit: Number(process.env.DB_CONNECTION_LIMIT) || 10,
  queueLimit: 0,
});


// 대화 기록 조회 함수
export const getConversations = async () => {
  try {
    const [rows] = await pool.query('SELECT * FROM conversation ORDER BY created_at DESC');
    return rows;
  } catch (err) {
    console.error("Error fetching conversations:", err);
    throw new Error('대화 기록을 가져오는 데 실패했습니다.');
  }
};

export const query = async (sql: string, values?: any) => {
  try {
    const [rows] = await pool.execute(sql, values);
    return rows;
  } catch (err) {
    console.error("Error executing query:", err);
    throw new Error('쿼리 실행 중 오류가 발생했습니다.');
  }
};

export default pool; // 다른 곳에서 pool을 사용할 수 있도록 내보냄

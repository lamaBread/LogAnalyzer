import mysql from "mysql2/promise";

// MySQL 커넥션 풀 설정
const pool = mysql.createPool({
  host: 'localhost',
  user: 'root',
  password: 'rootpw',
  database: 'conversations',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
});

// 대화 기록 조회 함수
export const getConversations = async () => {
  try {
    const [rows] = await pool.query('SELECT * FROM conversation ORDER BY created_at DESC');
    return rows;
  } catch (err) {
    throw new Error('대화 기록을 가져오는 데 실패했습니다.');
  }
};

export default pool; // 다른 곳에서 pool을 사용할 수 있도록 내보냄

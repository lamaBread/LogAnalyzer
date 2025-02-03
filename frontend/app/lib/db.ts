// lib/db.ts
import mysql from "mysql2/promise";

const pool = mysql.createPool({
  host: "localhost",       // MySQL 서버 주소
  user: "root",            // MySQL 사용자명
  password: "rootpw",    // MySQL 비밀번호
  database: "conversations", // 사용할 데이터베이스 이름
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
});

export default pool;
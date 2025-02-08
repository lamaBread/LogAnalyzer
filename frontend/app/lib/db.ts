import mysql from "mysql2/promise";

const dbHost = process.env.DB_HOST || 'localhost:8445'; 
const [host, port] = dbHost.split(':'); 

const pool = mysql.createPool({
  host: host, 
  port: parseInt(port), 
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
});

export default pool;

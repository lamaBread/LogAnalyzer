const bcrypt = require('bcryptjs');

// 원본 비밀번호
const password = 'password123';

// 비밀번호를 해시화
const hashedPassword = bcrypt.hashSync(password, 10);

// 해시값 출력
console.log(hashedPassword);

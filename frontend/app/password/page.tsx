"use client";

import { useState } from "react";

export default function PasswordPage() {
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const res = await fetch("/api/verify-password", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ password }),
    });

    const data = await res.json();
    if (data.success) {
      document.cookie = "password_verified=true; path=/";
      window.location.href = "/login"; // 로그인 페이지로 이동
    } else {
      setError("비밀번호가 일치하지 않습니다.");
    }
  };

  return (
    <div style={{
      display: "flex",
      justifyContent: "center",
      alignItems: "center",
      height: "100vh", // 화면 전체를 차지하도록 설정
      width: "100vw",  // 화면 전체 너비 차지
      backgroundColor: "#f0f0f0", // 배경 색상 추가 (필요시)
      position: "absolute",  // 위치 고정
      top: 0,
      left: 0,
    }}>
      <form onSubmit={handleSubmit} style={{ textAlign: "center" }}>
        <h2>비밀번호 입력</h2>
        <input
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
          style={{
            padding: "10px",
            margin: "10px 0",
            fontSize: "16px",
            width: "100%",
            maxWidth: "300px",
          }}
        />
        <button type="submit" style={{
          padding: "10px 20px",
          fontSize: "16px",
          cursor: "pointer",
        }}>
          확인
        </button>
        {error && <p style={{ color: "red" }}>{error}</p>}
      </form>
    </div>
  );
}

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
      window.location.href = "/"; // 메인 페이지로 이동
    } else {
      setError("비밀번호가 일치하지 않습니다.");
    }
  };

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === "Enter") {
      handleSubmit(e);  // 엔터키 눌렀을 때 `handleSubmit` 호출
    }
  };

  return (
    <div style={{
      display: "flex",
      justifyContent: "center",
      alignItems: "center",
      height: "100vh",
      width: "100vw",
      backgroundColor: "#f0f0f0",
      position: "absolute",
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
          onKeyDown={handleKeyDown}  // 엔터키 이벤트 추가
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

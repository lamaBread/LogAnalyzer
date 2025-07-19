"use client";

import { useState, useEffect } from "react";

export default function PasswordPage() {
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [isDarkMode, setIsDarkMode] = useState(false);

  useEffect(() => {
    const darkModeMediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
    setIsDarkMode(darkModeMediaQuery.matches);

    const handleChange = (e: MediaQueryListEvent) => setIsDarkMode(e.matches);
    darkModeMediaQuery.addEventListener("change", handleChange);

    return () => darkModeMediaQuery.removeEventListener("change", handleChange);
  }, []);

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
      window.location.href = "/";
    } else {
      setError("비밀번호가 일치하지 않습니다...");
    }
  };

  return (
    <div className="fixed top-0 left-0 w-screen h-screen flex justify-center items-center bg-gray-200 dark:bg-gray-900 text-black dark:text-white">
      <form onSubmit={handleSubmit} className="text-center">
        <h2 className="text-lg font-semibold mb-4">비밀번호 입력</h2>
        <input
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
          className="p-2 mb-4 text-lg w-full max-w-xs rounded border bg-white dark:bg-gray-700 text-black dark:text-white border-gray-300 dark:border-gray-500"
        />
        <button
          type="submit"
          className="px-4 py-2 text-lg bg-gray-700 text-white rounded hover:bg-gray-600 transition"
        >
          확인
        </button>
        {error && <p className="text-red-500 mt-2">{error}</p>}
      </form>
    </div>
  );
}

"use client";

import { useState, useEffect } from "react";
import DarkModeToggle from "../../../components/darkmodetoggle"; // 경로를 실제 경로로 수정

export default function SettingsPage() {
  const [currentPassword, setCurrentPassword] = useState("");
  const [newPassword, setNewPassword] = useState("");
  const [theme, setTheme] = useState("light");
  const [font, setFont] = useState("sans-serif");
  const [logPath, setLogPath] = useState("");

  useEffect(() => {
    // localStorage에서 저장된 테마 & 폰트 불러오기
    const savedTheme = localStorage.getItem("theme");
    const savedFont = localStorage.getItem("font");
    if (savedTheme) setTheme(savedTheme);
    if (savedFont) setFont(savedFont);
  }, []);

  useEffect(() => {
    // 테마 변경 시 다크모드 또는 라이트모드 적용
    if (theme === "dark") {
      document.body.classList.add("dark");
    } else {
      document.body.classList.remove("dark");
    }
  }, [theme]);

  useEffect(() => {
    // 폰트 변경
    document.body.style.fontFamily = font;
  }, [font]);

  // 비밀번호 변경 요청
  const handleChangePassword = async () => {
    const res = await fetch("/api/change-password", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ currentPassword, newPassword }),
    });
    const data = await res.json();
    if (data.success) {
      alert("비밀번호가 변경되었습니다!");
      setCurrentPassword("");
      setNewPassword("");
    } else {
      alert("비밀번호 변경 실패: " + data.message);
    }
  };

  // 테마 변경 (localStorage 저장)
  const handleThemeChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const newTheme = e.target.value;
    setTheme(newTheme);
    localStorage.setItem("theme", newTheme);
  };

  // 폰트 변경 (localStorage 저장)
  const handleFontChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const newFont = e.target.value;
    setFont(newFont);
    localStorage.setItem("font", newFont);
  };

  // 로그 파일 경로 저장
  const handleLogPathSave = async () => {
    const res = await fetch("/api/set-log-path", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ logPath }),
    });
    const data = await res.json();
    if (data.success) {
      alert("로그 파일 경로가 저장되었습니다!");
    } else {
      alert("오류 발생: " + data.message);
    }
  };

  return (
    <div className="p-6 max-w-2xl mx-auto space-y-6">
      <h1 className="text-2xl font-bold">설정</h1>

      {/* 비밀번호 변경 */}
      <div className="space-y-2">
        <h2 className="text-lg font-semibold">비밀번호 변경</h2>
        <input
          type="password"
          placeholder="현재 비밀번호"
          value={currentPassword}
          onChange={(e) => setCurrentPassword(e.target.value)}
          className="w-full p-2 border rounded"
        />
        <input
          type="password"
          placeholder="새 비밀번호"
          value={newPassword}
          onChange={(e) => setNewPassword(e.target.value)}
          className="w-full p-2 border rounded"
        />
        <button onClick={handleChangePassword} className="px-4 py-2 bg-gray-600 text-white rounded">
          변경
        </button>
      </div>

      {/* 화면 테마 변경 */}
      <div className="space-y-2">
        <h2 className="text-lg font-semibold">화면 테마</h2>
        <select value={theme} onChange={handleThemeChange} className="p-2 border rounded">
          <option value="light">라이트 모드</option>
          <option value="dark">다크 모드</option>
        </select>
      </div>

      {/* 글꼴 변경 */}
      <div className="space-y-2">
        <h2 className="text-lg font-semibold">글꼴 설정</h2>
        <select value={font} onChange={handleFontChange} className="p-2 border rounded">
          <option value="sans-serif">기본</option>
          <option value="serif">세리프</option>
          <option value="monospace">모노스페이스</option>
        </select>
      </div>

      {/* 로그 파일 경로 설정 */}
      <div className="space-y-2">
        <h2 className="text-lg font-semibold">로그 파일 경로</h2>
        <input
          type="text"
          placeholder="예: /var/logs/app.log"
          value={logPath}
          onChange={(e) => setLogPath(e.target.value)}
          className="w-full p-2 border rounded"
        />
        <button onClick={handleLogPathSave} className="px-4 py-2 bg-gray-600 text-white rounded">
          저장
        </button>
      </div>
    </div>
  );
}

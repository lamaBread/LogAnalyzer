"use client";

import { useState } from "react";
import { useTheme } from "../../../context/ThemeContext"; // ThemeContext에서 useTheme 가져오기
import DarkModeToggle from "../../../components/darkmodetoggle"; // 경로를 실제 경로로 수정

export default function SettingsPage() {
  const { theme, setTheme } = useTheme();  // useTheme 훅을 사용하여 테마 상태 관리
  const [currentPassword, setCurrentPassword] = useState("");
  const [newPassword, setNewPassword] = useState("");
  const [font, setFont] = useState("sans-serif");
  const [fontSize, setFontSize] = useState("16px");
  const [logPath, setLogPath] = useState("");

  // 비밀번호 변경 처리 함수
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

  // 글꼴 변경 처리 함수
  const handleFontChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const newFont = e.target.value;
    setFont(newFont);
    localStorage.setItem("font", newFont);
  };

  // 글씨 크기 변경 처리 함수
  const handleFontSizeChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const newFontSize = e.target.value;
    setFontSize(newFontSize);
    localStorage.setItem("fontSize", newFontSize);
  };

  // 로그 파일 경로 저장 함수
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

      <div className="space-y-2">
        <h2 className="text-lg font-semibold">화면 테마</h2>
        <DarkModeToggle />
      </div>

      <div className="flex space-x-4"> {/* flex로 두 항목을 가로로 배치 */}
        <div className="space-y-2 w-1/2"> {/* 글꼴 설정 */}
          <h2 className="text-lg font-semibold">글꼴 설정</h2>
          <select
            value={font}
            onChange={handleFontChange}
            className="p-2 border rounded w-full dark:text-black"
          >
            <option value="sans-serif">기본</option>
            <option value="serif">세리프</option>
            <option value="monospace">모노스페이스</option>
          </select>
        </div>

        <div className="space-y-2 w-1/2"> {/* 글씨 크기 설정 */}
          <h2 className="text-lg font-semibold">글씨 크기 설정</h2>
          <select
            value={fontSize}
            onChange={handleFontSizeChange}
            className="p-2 border rounded w-full dark:text-black"
          >
            <option value="14px">작게</option>
            <option value="16px">보통</option>
            <option value="18px">크게</option>
            <option value="20px">매우 크게</option>
          </select>
        </div>
      </div>

      <div className="space-y-2">
        <h2 className="text-lg font-semibold ">로그 파일 경로</h2>
        <input
          type="text"
          placeholder="예: C:\ProgramData\YourAppName\logs"
          value={logPath}
          onChange={(e) => setLogPath(e.target.value)}
          className="w-full p-2 border rounded dark:text-black"
        />
        <button onClick={handleLogPathSave} className="px-4 py-2 bg-gray-600 text-white rounded">
          저장
        </button>
      </div>
    </div>
  );
}

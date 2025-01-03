"use client";

import { useState, useEffect } from "react";

export default function DarkModeToggle() {
  const [darkMode, setDarkMode] = useState<boolean>(false);

  useEffect(() => {
    const savedMode = localStorage.getItem("darkMode");
    if (savedMode === "true") {
      setDarkMode(true);
      document.documentElement.classList.add("dark");
    } else {
      setDarkMode(false);
      document.documentElement.classList.remove("dark");
    }
  }, []);

  const toggleDarkMode = () => {
    setDarkMode(!darkMode);
    if (!darkMode) {
      document.documentElement.classList.add("dark");
      localStorage.setItem("darkMode", "true");
    } else {
      document.documentElement.classList.remove("dark");
      localStorage.setItem("darkMode", "false");
    }
  };

  return (
    <button
      onClick={toggleDarkMode}
      className="px-2 py-2 bg-white-500 text-white rounded hover:bg-gray-600 transition-colors"
    >
      {darkMode ? "☀" : "🌙"}
    </button>
  );
}

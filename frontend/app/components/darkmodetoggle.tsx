"use client";

import { useEffect } from "react";
import { useTheme } from "../context/ThemeContext";

export default function DarkModeToggle() {
  const { theme, setTheme } = useTheme();

  useEffect(() => {
    const savedMode = localStorage.getItem("darkMode");
    if (savedMode === "true") {
      setTheme("dark");
      document.documentElement.classList.add("dark");
    } else {
      setTheme("light");
      document.documentElement.classList.remove("dark");
    }
  }, [setTheme]);

  const toggleDarkMode = () => {
    const newTheme = theme === "light" ? "dark" : "light";
    setTheme(newTheme);

    if (newTheme === "dark") {
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
      {theme === "light" ? "🌙" : "☀"}
    </button>
  );
}

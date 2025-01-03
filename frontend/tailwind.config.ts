import type { Config } from "tailwindcss";

export default {
  content: [
    "./pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./components/**/*.{js,ts,jsx,tsx,mdx}",
    "./app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  darkMode: 'class', // 다크 모드 토글을 위한 설정
  theme: {
    extend: {
      colors: {
        background: "var(--background)", // 사용자 정의 배경 색상
        foreground: "var(--foreground)", // 사용자 정의 전경 색상
      },
    },
  },
  plugins: [],
} satisfies Config;

"use client";  // "use client"를 추가하여 React 컴포넌트에서 상태를 사용할 수 있도록 함.

import React from "react";
import Link from "next/link";
import { usePathname } from "next/navigation"; // usePathname 훅 임포트

export default function StatusLayout({ children }: { children: React.ReactNode }) {
  const pathname = usePathname(); // 현재 경로를 가져옴

  // 링크 배열
  const links = [
    { href: "/specific/main_menu/code_100", label: "Code 100" },
    { href: "/specific/main_menu/code_200", label: "Code 200" },
    { href: "/specific/main_menu/code_300", label: "Code 300" },
    { href: "/specific/main_menu/code_400", label: "Code 400" },
    { href: "/specific/main_menu/code_500", label: "Code 500" },
  ];

  return (
    <div className="p-4">
      {/* 상단 링크 영역 */}
      <div className="flex space-x-10 mb-4 border-b pb-2">
        {links.map((link) => (
          <Link
            key={link.href}
            href={link.href}
            className={`text-black hover:underline dark:text-white ${
              pathname === link.href ? "font-bold text-xl" : ""
            }`} // 현재 경로와 일치하는 링크에 font-bold 추가
          >
            {link.label}
          </Link>
        ))}
      </div>
      {/* 페이지 컨텐츠 영역 */}
      <div>{children}</div>
    </div>
  );
}

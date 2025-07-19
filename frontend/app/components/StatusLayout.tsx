"use client";

import React from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";

export default function StatusLayout({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();

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
      <nav className="flex space-x-10 mb-4 border-b pb-2" aria-label="Status Codes Navigation">
        {links.map((link) => (
          <Link
            key={link.href}
            href={link.href}
            className={`text-black hover:underline dark:text-white ${
              pathname === link.href ? "font-bold text-xl" : ""
            }`}
            aria-current={pathname === link.href ? "page" : undefined}
          >
            {link.label}
          </Link>
        ))}
      </nav>
      {/* 페이지 컨텐츠 영역 */}
      <main>{children}</main>
    </div>
  );
}

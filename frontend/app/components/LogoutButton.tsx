// components/LogoutButton.tsx
'use client';

import { useRouter } from 'next/navigation';

export default function LogoutButton() {
  const router = useRouter();

  const handleLogout = async () => {
    await fetch('/api/logout'); // 로그아웃 API 호출
    router.push('/password'); // 로그아웃 후 비밀번호 페이지로 리디렉션
  };

  return <button onClick={handleLogout} className="px-3 py-1 text-black hover:border-b-2 hover:border-black dark:text-white dark:hover:border-white">Logout</button>;
}

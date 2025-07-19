'use client';

import { useRouter } from 'next/navigation';

export default function LogoutButton() {
  const router = useRouter();

  const handleLogout = async () => {
    await fetch('/api/logout');
    router.push('/password');
  };

  return <button onClick={handleLogout} className="px-3 py-1 text-black hover:border-b-2 hover:border-black dark:text-white dark:hover:border-white">Logout</button>;
}

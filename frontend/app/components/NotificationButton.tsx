// components/NotificationButton.tsx
"use client";

import React from "react";

const NotificationButton = () => {
  const handleNotificationClick = () => {
    alert("새로운 알림이 없습니다");
  };

  return (
    <button
      className="px-2 py-2 bg-white-500 text-white rounded hover:bg-yellow-600 transition-colors"
      onClick={handleNotificationClick}
    >
      🔔
    </button>
  );
};

export default NotificationButton;
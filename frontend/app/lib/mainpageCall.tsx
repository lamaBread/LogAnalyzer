"use client";

import { useState, useEffect } from "react";

export async function MainPageCall() {
  try {
    const response = await fetch('http://localhost:3000/APIs/page_APIs/mainPage.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Auth-Key': 'A?5Ql1qpU9MQA?r',
      },
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    return response.json(); 
  } catch (error) {
    console.error('Failed to fetch data:', error);
    throw error;
  }
}
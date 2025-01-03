"use client";

declare global {
  interface Navigator {
    deviceMemory?: number;
  }
}

import React, { useState, useEffect } from "react";

interface WeatherData {
  weather: { icon: string; description: string }[];
  main: {
    temp: number;
    temp_max: number;
    temp_min: number;
    humidity: number;
  };
  wind: {
    speed: number;
  };
  name: string;
}

export default function Page() {
  const [weather, setWeather] = useState<WeatherData | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  const [cpuInfo, setCpuInfo] = useState<string>("");
  const [memoryInfo, setMemoryInfo] = useState<string>("");

  const logs = [
    "Error: Something went wrong",
    "Info: System started",
    "Warning: Low disk space",
  ]; // 예시 로그 데이터

  const getTemperatureColor = (temp: number): string => {
    if (temp <= 0) return "text-blue-500";
    if (temp >= 30) return "text-red-500";
    return "text-yellow-500";
  };

  const getWeatherIconUrl = (icon: string): string => {
    return `https://openweathermap.org/img/wn/${icon}@2x.png`;
  };

  const fetchWeatherByLocation = async (
    latitude: number,
    longitude: number
  ): Promise<void> => {
    try {
      setLoading(true);
      setError(null);

      const apiKey = process.env.NEXT_PUBLIC_WEATHER_API_KEY || "2a53ab1dac031d29ecb640003d9bdefd";
      const response = await fetch(
        `https://api.openweathermap.org/data/2.5/weather?lat=${latitude}&lon=${longitude}&units=metric&appid=${apiKey}`
      );

      if (!response.ok) throw new Error("Failed to fetch weather data");

      const data: WeatherData = await response.json();
      setWeather(data);
    } catch (err) {
      console.error("Error fetching weather data:", err);
      setError("Failed to fetch weather data");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (navigator.hardwareConcurrency) setCpuInfo(`CPU Cores: ${navigator.hardwareConcurrency}`);
    if (navigator.deviceMemory) setMemoryInfo(`Memory: ${navigator.deviceMemory}GB`);

    if (!navigator.geolocation) {
      setError("Geolocation is not supported by your browser.");
      return;
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        const { latitude, longitude } = position.coords;
        fetchWeatherByLocation(latitude, longitude);
      },
      () => {
        setError("Failed to get your location. Please enable location access.");
      }
    );
  }, []);

  return (
    <div className="flex flex-col h-screen">
      <div className="flex flex-grow gap-4 p-4">
        {/* 왼쪽: 날씨 및 컴퓨터 시스템 정보 */}
        <div className="w-1/2 p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
          {loading ? (
            <div className="flex justify-center items-center">
              <div className="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
          ) : error ? (
            <p className="text-red-500 text-xl">{error}</p>
          ) : weather ? (
            <div className="space-y-4">
              <div className="flex items-center">
                <img
                  src={getWeatherIconUrl(weather.weather[0].icon)}
                  alt="Weather icon"
                  className="w-32 h-32 mr-6"
                />
                <div>
                  <h3 className="text-2xl font-semibold">{weather.name}</h3>
                  <p className="capitalize text-lg text-gray-600 dark:text-gray-400">
                    {weather.weather[0].description}
                  </p>
                  <p
                    className={`text-5xl font-bold ${getTemperatureColor(
                      weather.main.temp
                    )}`}
                  >
                    {weather.main.temp}°C
                  </p>
                </div>
              </div>
              <div>
                <h1 className="text-3xl font-bold mb-4 mt-6">시스템 정보</h1>
                <p>{cpuInfo}</p>
                <p>{memoryInfo}</p>
              </div>
            </div>
          ) : null}
        </div>

        {/* 오른쪽: 서버 정보 */}
        <div className="w-1/2 p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
          <h1 className="text-3xl font-bold mb-4">서버 정보</h1>
          <p>서버 상태 정보를 표시할 수 있는 공간입니다.</p>
        </div>
      </div>
    </div>
  );
}

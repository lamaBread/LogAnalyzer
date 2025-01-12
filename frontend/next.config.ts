import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  async rewrites() {
    return [
      {
        source: '/APIs/:path*',
        destination: 'http://php-apache:80/:path*' // 프록시 설정
      }
    ];
  }
  /* config options here */
};

export default nextConfig;

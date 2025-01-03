module.exports = {
  async rewrites() {
    return [
      {
        source: '/APIs/:path*',
        destination: 'http://nginx:80/:path*', // 프록시 설정 1
      }
    ];
  },
};
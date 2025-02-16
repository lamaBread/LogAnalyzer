import { NextRequest, NextResponse } from "next/server";

export function middleware(req: NextRequest) {
  const url = req.nextUrl;
  const passwordVerified = req.cookies.get("password_verified");
  const loggedIn = req.cookies.get("logged_in");

  // 1. 비밀번호 인증 안 된 경우 → `password` 페이지로 이동
  if (!passwordVerified && url.pathname !== "/password") {
    return NextResponse.redirect(new URL("/password", req.url));
  }

  // 2. 비밀번호 인증은 됐지만 로그인 안 된 경우 → `login` 페이지로 이동
  if (passwordVerified && !loggedIn && url.pathname !== "/login") {
    return NextResponse.redirect(new URL("/login", req.url));
  }

  // 3. 로그인 후에는 정상적으로 페이지 접근 가능
  return NextResponse.next();
}

// 미들웨어를 적용할 경로 설정
export const config = {
  matcher: ["/", "/main"], // 메인 페이지 보호 (추가적인 페이지 보호 가능)
};

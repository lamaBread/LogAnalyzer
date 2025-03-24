"use client";

export default function helpcenterPage() {
  return (
    <div className="p-6 max-w-3xl mx-auto space-y-8">
      <h1 className="text-3xl font-bold">사용 방법 안내</h1>

      {/* 섹션 1 */}
      <div className="space-y-4">
        <h2 className="text-2xl font-semibold">1. 로그인 및 회원가입</h2>
        <p className="text-gray-700 dark:text-gray-300">
          (여기에 로그인 및 회원가입 방법에 대한 설명을 작성하세요.)
        </p>
      </div>

      {/* 섹션 2 */}
      <div className="space-y-4">
        <h2 className="text-2xl font-semibold">2. 설정 변경</h2>
        <p className="text-gray-700 dark:text-gray-300">
          (여기에 설정 변경 방법에 대한 설명을 작성하세요.)
        </p>
      </div>

      {/* 섹션 3 */}
      <div className="space-y-4">
        <h2 className="text-2xl font-semibold">3. 보고서 받기</h2>
        <p className="text-gray-700 dark:text-gray-300">
          (여기에 보고서 받는 방법에 대한 설명을 작성하세요.)
        </p>
      </div>

      {/* 추가 섹션 필요 시 여기에 추가 */}
    </div>
  );
}

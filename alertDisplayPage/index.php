<?php
// 세션 시작 - 알림 상태 관리용
session_start();

// 테스트 로그 파일 경로 설정
$logFilePath = '../backend/LOG/test_log';

// 로그 데이터를 가져오기 위한 함수
function getLogData($logFilePath) {
    $logs = file($logFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return $logs ? $logs : [];
}

// evaluate_raw_logs.php에 로그 데이터 전송 함수
function evaluateLogs($logs) {
    $url = 'http://localhost:8445/evaluate_raw_logs.php';
    $data = ['logs' => $logs];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        return ['error' => 'Failed to connect to evaluation service'];
    }
    
    return json_decode($result, true);
}

// 현재 페이지가 POST 요청으로 호출된 경우 로그 평가 실행
$evaluation = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $logs = getLogData($logFilePath);
        if (!empty($logs)) {
            $evaluation = evaluateLogs($logs);
            
            // 평가 결과 세션에 저장 (새로고침 시 유지)
            $_SESSION['evaluation'] = $evaluation;
            $_SESSION['last_checked'] = date('Y-m-d H:i:s');
        } else {
            $error = "로그 파일을 읽을 수 없거나 로그가 없습니다.";
        }
    } catch (Exception $e) {
        $error = "오류 발생: " . $e->getMessage();
    }
} else {
    // 이전에 저장된 평가 결과가 있으면 불러오기
    if (isset($_SESSION['evaluation'])) {
        $evaluation = $_SESSION['evaluation'];
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그 분석 알림 대시보드</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            font-weight: bold;
        }
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border-color: #dc3545;
        }
        .alert-warning {
            background-color: rgba(255, 193, 7, 0.1);
            border-color: #ffc107;
        }
        .badge-attack {
            font-size: 85%;
            padding: 0.35em 0.65em;
        }
        .attack-sql {
            background-color: #dc3545;
        }
        .attack-xss {
            background-color: #fd7e14;
        }
        .attack-lfi {
            background-color: #6f42c1;
        }
        .attack-rce {
            background-color: #d63384;
        }
        .attack-other {
            background-color: #20c997;
        }
        .log-entry {
            font-family: monospace;
            word-break: break-all;
            padding: 10px;
            border-radius: 5px;
        }
        .status-count {
            font-size: 2rem;
            font-weight: bold;
        }
        .log-details {
            background-color: #f8f9fa;
            border-left: 4px solid #6c757d;
            padding: 10px;
            margin: 10px 0;
        }
        .highlight {
            background-color: #ffecb3;
            padding: 0 3px;
            border-radius: 3px;
        }
        .refresh-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        #countdownTimer {
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4 text-center">로그 분석 알림 대시보드</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- 정보 요약 섹션 -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-info-circle me-2"></i>분석 요약
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="mb-2">총 로그 수</div>
                                <div class="status-count text-primary">
                                    <?php echo $evaluation ? $evaluation['total_logs'] : '0'; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-2">의심스러운 로그 수</div>
                                <div class="status-count text-danger">
                                    <?php echo $evaluation && isset($evaluation['suspicious_count']) ? $evaluation['suspicious_count'] : '0'; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-2">마지막 검사 시간</div>
                                <div class="text-muted">
                                    <?php echo isset($_SESSION['last_checked']) ? $_SESSION['last_checked'] : '없음'; ?>
                                </div>
                                <div id="countdownTimer">다음 갱신까지: 60초</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($evaluation && isset($evaluation['suspicious_logs']) && !empty($evaluation['suspicious_logs'])): ?>
            <!-- 공격 유형 요약 -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <i class="fas fa-exclamation-triangle me-2"></i>탐지된 공격 유형
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php 
                                if (isset($evaluation['analysis_results']['detected_attacks'])):
                                    foreach ($evaluation['analysis_results']['detected_attacks'] as $attack): 
                                        // 공격 유형에 따른 색상 클래스 결정
                                        $attackClass = 'attack-other';
                                        if (stripos($attack['attackType'], 'sql') !== false) {
                                            $attackClass = 'attack-sql';
                                        } elseif (stripos($attack['attackType'], 'xss') !== false) {
                                            $attackClass = 'attack-xss';
                                        } elseif (stripos($attack['attackType'], 'lfi') !== false || stripos($attack['attackType'], 'file') !== false) {
                                            $attackClass = 'attack-lfi';
                                        } elseif (stripos($attack['attackType'], 'rce') !== false || stripos($attack['attackType'], 'command') !== false) {
                                            $attackClass = 'attack-rce';
                                        }
                                ?>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge <?php echo $attackClass; ?> badge-attack me-2">
                                                <?php echo htmlspecialchars($attack['attackType']); ?>
                                            </span>
                                            <small><?php echo htmlspecialchars($attack['attackDetails']); ?></small>
                                        </div>
                                        <span class="badge bg-secondary"><?php echo $attack['count']; ?>개</span>
                                    </div>
                                </div>
                                <?php 
                                    endforeach;
                                endif;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 의심스러운 로그 상세 내역 -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <i class="fas fa-search me-2"></i>의심스러운 로그 상세 내역
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="suspiciousLogsAccordion">
                                <?php foreach ($evaluation['suspicious_logs'] as $index => $log): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="false" 
                                                aria-controls="collapse<?php echo $index; ?>">
                                            로그 #<?php echo ($index + 1); ?> - 
                                            <?php 
                                            // IP 주소 추출 시도
                                            $ip = "알 수 없음";
                                            if (preg_match('/\[client ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $log['log'], $matches)) {
                                                $ip = $matches[1];
                                            }
                                            
                                            // 날짜 추출 시도
                                            $date = "알 수 없음";
                                            if (preg_match('/\[(.*?)\]/', $log['log'], $matches)) {
                                                $date = $matches[1];
                                            }
                                            
                                            echo "IP: $ip, 시간: $date";
                                            ?>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" 
                                         aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#suspiciousLogsAccordion">
                                        <div class="accordion-body">
                                            <div class="log-entry alert-danger mb-3">
                                                <?php 
                                                // 로그에서 탐지된 패턴을 하이라이트
                                                $logText = htmlspecialchars($log['log']);
                                                foreach ($log['detectedPatterns'] as $pattern) {
                                                    // 정규식 패턴을 일반 문자열로 변환해서 하이라이트
                                                    $searchPattern = htmlspecialchars($pattern['attackDetails']);
                                                    $logText = str_replace(
                                                        $searchPattern, 
                                                        '<span class="highlight">' . $searchPattern . '</span>', 
                                                        $logText
                                                    );
                                                }
                                                echo $logText;
                                                ?>
                                            </div>
                                            
                                            <h6 class="mt-3 mb-2">탐지된 패턴:</h6>
                                            <?php foreach ($log['detectedPatterns'] as $pattern): ?>
                                            <div class="log-details">
                                                <div><strong>공격 유형:</strong> <?php echo htmlspecialchars($pattern['attackType']); ?></div>
                                                <div><strong>상세 내용:</strong> <?php echo htmlspecialchars($pattern['attackDetails']); ?></div>
                                                <div><strong>패턴:</strong> <code><?php echo htmlspecialchars($pattern['pattern']); ?></code></div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
        <?php elseif ($evaluation): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>의심스러운 로그가 발견되지 않았습니다.
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>로그 분석을 시작하려면 '로그 분석 시작' 버튼을 클릭하세요.
            </div>
        <?php endif; ?>
        
        <!-- 수동 새로고침 버튼 -->
        <form method="post" action="" class="mb-5">
            <button type="submit" class="btn btn-primary refresh-btn">
                <i class="fas fa-sync-alt me-2"></i>로그 분석 시작
            </button>
        </form>
    </div>
    
    <!-- Bootstrap JS 및 Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- 자동 새로고침 스크립트 -->
    <script>
        // 60초마다 새로고침
        let secondsLeft = 60;
        
        function updateTimer() {
            document.getElementById('countdownTimer').textContent = `다음 갱신까지: ${secondsLeft}초`;
            secondsLeft--;
            
            if (secondsLeft < 0) {
                document.querySelector('form').submit();
                secondsLeft = 60;
            }
        }
        
        // 1초마다 타이머 업데이트
        setInterval(updateTimer, 1000);
        
        // 페이지 로드 시 타이머 시작
        updateTimer();
    </script>
</body>
</html>

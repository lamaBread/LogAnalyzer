<?php

// die();

// 모든 로그를 읽어들여서 배열로 반환하는 함수.
// 하나의 행을 하나의 원소로 삼아서 1차원 배열로 반환.
function readLogFileToArray($filePath) {  // Example usage    $logFilePath = '/path/to/your/logfile.log';   print_r($logArray);
    $lines = [];
    if (file_exists($filePath)) {
        $file = fopen($filePath, "r");
        if ($file) {
            while (($line = fgets($file)) !== false) {
                $trimmedLine = trim($line);
                if ($trimmedLine !== '' && strpos($trimmedLine, '---') !== 0) {  // Skip empty lines and lines that start with '---'
                    $lines[] = $trimmedLine;
                }
            }
            fclose($file);
        } else {
            echo "Error opening the file.";
        }
    } else {
        echo "File does not exist.";
    }
    return $lines;
}

// 로그 배열을 IP 주소별로 그룹화하는 함수.
// IP 주소를 추출하여 그룹화하고, IP 주소가 없는 경우 'unknown'으로 처리.
// Key: IP 주소 / value: 해당 IP 주소를 가지는 로그 배열.
function groupLogsByIP($logArray) {
    $groupedLogs = [];
    foreach ($logArray as $log) {
        // Extract the IP address from the log line
        if (preg_match('/\[client ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+):[0-9]+\]/', $log, $matches)) {
            $ip = $matches[1];
        } else {
            $ip = 'unknown';
        }
        if (!isset($groupedLogs[$ip])) {
            $groupedLogs[$ip] = [];
        }
        $groupedLogs[$ip][] = $log;
    }
    return $groupedLogs;
}

// 로그 배열을 상태코드별로 그룹화하는 함수.
// 상태코드를 추출하여 그룹화하고, 상태코드가 없는 경우 'unknown'으로 처리.
// Key: 상태코드 / value: 해당 상태코드를 가지는 로그 배열.
function groupLogsByStatusCode($logArray) {
    $groupedLogs = [];
    foreach ($logArray as $log) {
        // Extract the status code from the log line
        if (preg_match('/" [0-9]{3} /', $log, $matches)) {
            $statusCode = trim($matches[0], '" ');
        } else {
            $statusCode = 'unknown';
        }
        if (!isset($groupedLogs[$statusCode])) {
            $groupedLogs[$statusCode] = [];
        }
        $groupedLogs[$statusCode][] = $log;
    }
    return $groupedLogs;
}

// 로그 배열을 100 단위의 상태코드로 그룹화하는 함수.
// 상태코드를 추출하여 100 단위로 그룹화하고, 상태코드가 없는 경우 'unknown'으로 처리.
// Key: 100 단위 상태코드 / value: 해당 상태코드를 가지는 로그 배열.
function groupLogsByStatusCodeRange($logArray) {
    $groupedLogs = [];
    foreach ($logArray as $log) {
        // Extract the status code from the log line
        if (preg_match('/" ([0-9]{3}) /', $log, $matches)) {
            $statusCode = (int)$matches[1];
            $statusCodeRange = floor($statusCode / 100) * 100;
        } else {
            $statusCodeRange = 'unknown';
        }
        if (!isset($groupedLogs[$statusCodeRange])) {
            $groupedLogs[$statusCodeRange] = [];
        }
        $groupedLogs[$statusCodeRange][] = $log;
    }
    return $groupedLogs;
}

// 보고서를 평가하여 1~3의 위험도 숫자를 출력하는 함수. 문자열(1~3)을 반환한다.
// 미완성.
function queryLLM_evaluateReport($mainText) {
    $promptText = "Based on the following report, please evaluate the server status as an integer from 1 (safe) to 3 (dangerous). Respond using JSON. " . $mainText;
    $payload = [
        'prompt' => $promptText,
        'model' => 'llama3.2',
        'stream' => false,
        'format' => [ 
            'type' => 'object',
            'properties' => [
                'level' => ['type' => 'integer']
            ],
            'required' => ['level']
        ]
    ];

    $ch = curl_init("http://ollama:11434/api/generate");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $rawResponse = curl_exec($ch);
    curl_close($ch);

    echo "<br><br><br>";
    echo "Raw Response: ";
    echo '<pre>';
    print_r($rawResponse);
    echo '</pre>';
    echo "<br><br><br>";

    $decoded = json_decode($rawResponse, true);

    if (isset($decoded['response'])) {
        $jsonPart = json_decode($decoded['response'], true);
        if (isset($jsonPart['level'])) {
            return (int)$jsonPart['level'];
        }
    }
    return null;
}

// LLM에 쿼리를 보내는 함수. 응답 JSON을 decode하여 반환한다. (반환: 연관배열)
function queryLLM($prompt, $stream = false, $model = 'llama3.2') {
    $url = "http://ollama:11434/api/generate";
    $payload = [
        'model' => $model,
        'prompt' => $prompt,
        'stream' => $stream
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}


// markdown parser

/**
 * Slimdown - A simple regex-based Markdown parser in PHP. Supports the
 * following elements (and can be extended via `Slimdown::add_rule()`):
 *
 * - Headers
 * - Links
 * - Bold
 * - Emphasis
 * - Deletions
 * - Quotes
 * - Code blocks
 * - Inline code
 * - Blockquotes
 * - Ordered/unordered lists
 * - Horizontal rules
 * - Images
 *
 * Author: Johnny Broadway <johnny@johnnybroadway.com>
 * Website: https://github.com/jbroadway/slimdown
 * License: MIT
 */

 class Slimdown {
	public static $rules = array (
		'/```(.*?)```/s' => self::class .'::code_parse',                                                          // code blocks
		'/\n(#+)\s+(.*)/' => self::class .'::header',                                                             // headers
		'/\!\[([^\[]*?)\]\(([^\)]+)\)/' => self::class .'::img',                                                  // images
		'/\[([^\[]+)\]\(([^\)]+)\)/' => self::class .'::link',                                                    // links
		'/(\*\*|__)(?=(?:(?:[^`]*`[^`\r\n]*`)*[^`]*$))(?![^\/<]*>.*<\/.+>)(.*?)\1/' => '<strong>\2</strong>',     // bold
		'/(\*|_)(?=(?:(?:[^`]*`[^`\r\n]*`)*[^`]*$))(?![^\/<]*>.*<\/.+>)(.*?)\1/' => '<em>\2</em>',                // emphasis
		'/(\~\~)(?=(?:(?:[^`]*`[^`\r\n]*`)*[^`]*$))(?![^\/<]*>.*<\/.+>)(.*?)\1/' => '<del>\2</del>',              // del
		'/\:\"(.*?)\"\:/' => '<q>\1</q>',                                                                         // quote
		'/`(.*?)`/' => '<code>\1</code>',                                                                         // inline code
		'/(\n[\*|\-] )\[x\]/' => '\1<input type=\'checkbox\' disabled checked>',                                  // checkbox checked
		'/(\n[\*|\-] )\[\ \]/' => '\1<input type=\'checkbox\' disabled>',                                         // checkbox unchecked
		'/\n[\*|\-] (.*)/' => self::class .'::ul_list',                                                           // ul lists
		'/\n[0-9]+\.(.*)/' => self::class .'::ol_list',                                                           // ol lists
		'/\n(&gt;|\>)(.*)/' => self::class .'::blockquote',                                                       // blockquotes
		'/\n-{5,}/' => "\n<hr>",                                                                                  // horizontal rule
		'/\n([^\n]+)\n/' => self::class .'::para',                                                                // add paragraphs
		'/<\/ul>\s?<ul>/' => '',                                                                                  // fix extra ul
		'/<\/ol>\s?<ol>/' => '',                                                                                  // fix extra ol
		'/<\/blockquote><blockquote>/' => "\n",                                                                   // fix extra blockquote
		'/<a href=\'(.*?)\'>/' => self::class .'::fix_link',                                                      // fix links
		'/<img src=\'(.*?)\'/' => self::class .'::fix_img',                                                       // fix images
		'/<p>{{{([0-9]+)}}}<\/p>/s' => self::class .'::reinsert_code_blocks'                                      // re-insert code blocks
	);

	private static $code_blocks = [];
	
	private static function code_parse ($regs) {
		$item = $regs[1];
		$item = htmlentities ($item, ENT_COMPAT);
		$item = str_replace ("\n\n", '<br>', $item);
		$item = str_replace ("\n", '<br>', $item);
		while (mb_substr ($item, 0, 4) === '<br>') {
			$item = mb_substr ($item, 4);
		}
		while (mb_substr ($item, -4) === '<br>') {
			$item = mb_substr ($item, 0, -4);
		}
		// Store code blocks with placeholders to avoid other regexes affecting them
		self::$code_blocks[] = sprintf ("<pre><code>%s</code></pre>", trim ($item));
		return sprintf ("{{{%d}}}", count (self::$code_blocks) - 1);
	}

	private static function reinsert_code_blocks ($regs) {
		// Reinsert the stored code blocks at the end
		$index = $regs[1];
		return self::$code_blocks[$index];
	}

	private static function para ($regs) {
		$line = $regs[1];
		$trimmed = trim ($line);
		if (preg_match ('/^<\/?(ul|ol|li|h|p|bl|table|tr|th|td|code)/', $trimmed)) {
			return "\n" . $line . "\n";
		}
		if (! empty ($trimmed)) {
			return sprintf ("\n<p>%s</p>\n", $trimmed);
		}
		return $trimmed;
	}

	private static function ul_list ($regs) {
		$item = $regs[1];
		return sprintf ("\n<ul>\n\t<li>%s</li>\n</ul>", trim ($item));
	}

	private static function ol_list ($regs) {
		$item = $regs[1];
		return sprintf ("\n<ol>\n\t<li>%s</li>\n</ol>", trim ($item));
	}

	private static function blockquote ($regs) {
		$item = $regs[2];
		return sprintf ("\n<blockquote>%s</blockquote>", trim ($item));
	}

	private static function header ($regs) {
		list ($tmp, $chars, $header) = $regs;
		$level = strlen ($chars);
		return sprintf ('<h%d>%s</h%d>', $level, trim ($header), $level);
	}

	private static function link ($regs) {
		list ($tmp, $text, $link) = $regs;
		// Substitute _ and * in links so they don't break the URLs
		$link = str_replace (['_', '*'], ['{^^^}', '{~~~}'], $link);
		return sprintf ('<a href=\'%s\'>%s</a>', $link, $text);
	}

	private static function img ($regs) {
		list ($tmp, $text, $link) = $regs;
		// Substitute _ and * in links so they don't break the URLs
		$link = str_replace (['_', '*'], ['{^^^}', '{~~~}'], $link);
		return sprintf ('<img src=\'%s\' alt=\'%s\'>', $link, $text);
	}

	private static function fix_link ($regs) {
		// Replace substitutions so links are preserved
		$fixed_link = str_replace (['{^^^}', '{~~~}'], ['_', '*'], $regs[1]);
		return sprintf ('<a href=\'%s\'>', $fixed_link);
	}

	private static function fix_img ($regs) {
		// Replace substitutions so links are preserved
		$fixed_link = str_replace (['{^^^}', '{~~~}'], ['_', '*'], $regs[1]);
		return sprintf ('<img src=\'%s\'', $fixed_link);
	}

	/**
	 * Add a rule.
	 */
	public static function add_rule ($regex, $replacement) {
		self::$rules[$regex] = $replacement;
	}

	/**
	 * Render some Markdown into HTML.
	 */
	public static function render ($text) {
		self::$code_blocks = [];
		$text = "\n" . $text . "\n";
		foreach (self::$rules as $regex => $replacement) {
			if (is_callable ( $replacement)) {
				$text = preg_replace_callback ($regex, $replacement, $text);
			} else {
				$text = preg_replace ($regex, $replacement, $text);
			}
		}
		return trim ($text);
	}
}

// 쉽게 사용하기 위한 인터페이스이다.
function md($text) {
    return Slimdown::render($text);
}

/*
// test code
$logFilePath = '../LOG/combine_error.log';   
$logArray = readLogFileToArray($logFilePath);
echo '<pre>';
print_r($logArray);
echo '</pre>';
echo '<br><br><br>';
$groupedLogs = groupLogsByIP($logArray);
echo '<pre>';
print_r($groupedLogs);
echo '</pre>';
echo '<br><br><br>';
$securityReport = analyze_for_mainPage($logArray);
echo '<pre>';
echo $securityReport;
echo '</pre>';
*/

/*
// 상태코드별 로그 그룹화 테스트 코드
$logFilePath = '../LOG/test_log_access';
$logArray = readLogFileToArray($logFilePath);
$groupedLogsByStatus = groupLogsByStatusCode($logArray);
echo '<pre>';
print_r($groupedLogsByStatus);
echo '</pre>';

echo '<br><br><br>';
// 100 단위 상태코드별 로그 그룹화 테스트 코드
$groupedLogsByStatusRange = groupLogsByStatusCodeRange($logArray);
echo '<pre>';
print_r($groupedLogsByStatusRange);
echo '</pre>';

*/
<?php
session_start();
require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Section;

// 初始化 PhpWord 並設定全局預設字型與大小
$phpWord = new PhpWord();
$phpWord->setDefaultFontName('標楷體'); // 全文件預設使用標楷體
$phpWord->setDefaultFontSize(12);

// --------------------
// 1. 建立封面頁
// --------------------
$coverSection = $phpWord->addSection([
    'marginTop'    => 1000,
    'marginBottom' => 1000,
    'marginLeft'   => 1200,
    'marginRight'  => 1200,
    'borderBottomSize' => 12,
    'borderLeftSize'   => 12,
    'borderRightSize'  => 12,
    'borderTopSize'    => 12,
    'borderColor'      => '000000',
]);

// 封面標題
$coverSection->addText(
    '備審資料',
    ['bold' => true, 'size' => 36, 'color' => '333399'],
    ['alignment' => Jc::CENTER, 'spaceBefore' => 300, 'spaceAfter' => 300]
);
$coverSection->addTextBreak(5);

// 顯示使用者姓名（假設 Session 中有 user['name']）
$coverSection->addText(
    '姓名：' . htmlspecialchars($_SESSION['user']['name']),
    ['size' => 18],
    ['alignment' => Jc::CENTER, 'spaceBefore' => 200, 'spaceAfter' => 200]
);

// 生成日期並置於右下角
$date = date('Y-m-d');
$coverSection->addText(
    "生成日期：$date",
    ['size' => 12, 'italic' => true],
    ['alignment' => Jc::RIGHT, 'spaceBefore' => 5000]
);

$section = $phpWord->addSection();
$footer = $section->addFooter();

// 插入自動更新的頁碼
$footer->addPreserveText(
    '{PAGE}',
    'Times New Roman', // 字體樣式（這裡設為 null）
    array('alignment' => 'center') // 這裡控制對齊方式
);

// --------------------
// 2. 資料庫連線及相關設定
// --------------------
$servername = "127.0.0.1";
$username = "HCHJ";
$password = "xx435kKHq";
$dbname = "HCHJ";

$conn = new mysqli($servername, $username, $password, $dbname);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

$userData = $_SESSION['user'];
$userId = $userData['user'];
$username = $userData['name'];

$options = isset($_POST['options']) ? $_POST['options'] : [];
if (empty($options)) {
    die("未選擇任何匯出選項！");
}

// --------------------
// 若有選取自傳檔案，作為後續處理參考
// --------------------
if (isset($_POST['autobiography_file'])) {
    $autobiographyFile = $_POST['autobiography_file'];
} else {
    $autobiographyFile = '';
}

// --------------------
// 定義查詢選項對應
// --------------------
$queryMap = [
    'competition'    => "SELECT file_name, file_content FROM portfolio WHERE student_id = '$userId' AND category = '競賽證明'",
    'transcript'     => "SELECT file_name, file_content FROM portfolio WHERE student_id = '$userId' AND category = '成績單'",
    'autobiography'  => "", // 自傳依據使用者選擇的檔案決定
    'diploma'        => "SELECT file_name, file_content FROM portfolio WHERE student_id = '$userId' AND category = '學歷證明'",
    'internship'     => "SELECT file_name, file_content FROM portfolio WHERE student_id = '$userId' AND category = '實習證明'",
    'certifications' => "", // 相關證照之後再處理
    'language'       => "SELECT file_name, file_content FROM portfolio WHERE student_id = '$userId' AND category = '語言能力證明'",
    'other'          => "SELECT file_name, file_content FROM portfolio WHERE student_id = '$userId' AND category = '其他資料'",
    'Proof-of-service'=> "SELECT file_name, file_content FROM portfolio WHERE student_id = '$userId' AND category = '服務證明'",
    'read'           => "SELECT file_name, file_content FROM portfolio WHERE student_id = '$userId' AND category = '讀書計畫'"
];

// --------------------
// 處理專業證照選項
// --------------------
if (!empty($_POST['certifications_files'])) {
    $selectedCertifications = $_POST['certifications_files'];
    // 避免 SQL Injection，將字串轉為安全格式
    $escapedCerts = array_map(function($cert) use ($conn) {
        return "'" . $conn->real_escape_string($cert) . "'";
    }, $selectedCertifications);
    $certInCondition = implode(',', $escapedCerts);
    
    if (!empty($certInCondition)) {
        $queryMap['certifications'] = "
            SELECT file_name, file_content, organization, certificate_name
            FROM portfolio
            WHERE student_id = '$userId'
              AND category = '專業證照'
              AND organization IN ($certInCondition)
        ";
    }
}

// --------------------
// 處理自傳檔案選項
// --------------------
if (!empty($_POST['autobiography_files'])) {
    $selectedAutobiographies = $_POST['autobiography_files'];
    $escapedAutobiographies = array_map(function($file) use ($conn) {
        return "'" . $conn->real_escape_string($file) . "'";
    }, $selectedAutobiographies);
    $autoInCondition = implode(',', $escapedAutobiographies);
    if (!empty($autoInCondition)) {
        $queryMap['autobiography'] = "
            SELECT file_name, file_content
            FROM portfolio
            WHERE student_id = '$userId'
              AND category = '自傳'
              AND file_name IN ($autoInCondition)
        ";
    }
}

// --------------------
// 定義中文選項標題
// --------------------
$optionNames = [
    'competition'    => '競賽證明',
    'transcript'     => '成績單',
    'autobiography'  => '自傳',
    'diploma'        => '學歷證明',
    'internship'     => '實習證明',
    'certifications' => '專業證照',
    'language'       => '語言能力證明',
    'other'          => '其他資料',
    'Proof-of-service'=> '服務證明',
    'read'           => '讀書計畫'
];

// --------------------
// 自傳專用的文字與段落樣式
// --------------------
$autobiographyTextStyle = ['name' => '標楷體', 'size' => 14, 'color' => '000000'];
$autobiographyParagraphStyle = ['alignment' => Jc::BOTH, 'spaceAfter' => 100];

// --------------------
// 4. 依使用者選取的順序處理各個匯出選項
// --------------------
foreach ($options as $option) {

    // 依照不同選項建立新頁與設定頁面標題
    if ($option === 'autobiography') {
        // 自傳：獨立新頁，標題樣式保留原本設定
        $section = $phpWord->addSection();
        $section->addText("自傳", ['bold' => true, 'size' => 25, 'color' => '333399'], ['alignment' => Jc::CENTER]);
    } elseif ($option === 'topics') {
        // 專題資料：只需輸出一頁，加入標題與頁尾
        $section = $phpWord->addSection();
        $section->addText("專題資料", ['bold' => true, 'size' => 25, 'color' => '333399'], ['alignment' => Jc::CENTER]);
        $footer = $section->addFooter();
        $footer->addText("此頁面僅供展示專題資料之用途。", ['size' => 14], ['alignment' => Jc::CENTER]);
        continue;
    } elseif ($option === 'certifications') {
        // 專業證照：依分頁邏輯處理
        $resultCert = $conn->query($queryMap['certifications']);
        $certifications = [];
        if ($resultCert && $resultCert->num_rows > 0) {
            while ($row = $resultCert->fetch_assoc()) {
                $certifications[] = $row;
            }
        }
        $maxPerPage = 6;  // 每頁最多6筆資料（3行x2列）
        $totalCerts = count($certifications);
        $pageIndex = 0;
        while ($pageIndex * $maxPerPage < $totalCerts) {
            $certSection = $phpWord->addSection();
            $certSection->addText("專業證照", ['bold' => true, 'size' => 25, 'color' => '333399'], ['alignment' => Jc::CENTER]);
            // 建立表格樣式
            $tableStyle = [
                'borderSize'   => 12,
                'borderColor'  => '000000',
                'cellMargin'   => 50,
            ];
            $phpWord->addTableStyle('CertTable' . $pageIndex, $tableStyle);
            $table = $certSection->addTable('CertTable' . $pageIndex);
            $certsInPage = array_slice($certifications, $pageIndex * $maxPerPage, $maxPerPage);
            $cellCount = 0;
            for ($row = 0; $row < 3; $row++) {
                $table->addRow();
                for ($col = 0; $col < 2; $col++) {
                    $cell = $table->addCell(4500);
                    if (isset($certsInPage[$cellCount])) {
                        $cert = $certsInPage[$cellCount];
                        try {
                            $cell->addImage($cert['file_content'], [
                                'width' => 198,  // 約7cm
                                'height' => 142, // 約5cm
                                'scaling' => 100,
                                'alignment' => Jc::CENTER,
                            ]);
                        } catch (Exception $e) {
                            $cell->addText("圖片無法載入", ['color' => 'FF0000'], ['alignment' => Jc::CENTER]);
                        }
                        $cell->addText($cert['certificate_name'], ['size' => 12], ['alignment' => Jc::CENTER]);
                    }
                    $cellCount++;
                }
            }
            $pageIndex++;
        }
        continue;
    } 
    // 其他選項：如果是「競賽證明」、「成績單」、「學歷證明」、「實習證明」或「語言能力證明」，採用競賽證明版面樣式
    elseif (in_array($option, ['competition', 'transcript', 'diploma', 'internship', 'language'])) {
        $section = $phpWord->addSection();
        $section->addText($optionNames[$option], ['bold' => true, 'size' => 25, 'color' => '333399'], ['alignment' => Jc::CENTER]);
    } 
    // 其他選項則維持原有設定（例如其他資料、服務證明、讀書計畫）
    else {
        $section = $phpWord->addSection();
        if (isset($optionNames[$option])) {
            $section->addText($optionNames[$option], ['bold' => true, 'size' => 25, 'color' => '333399'], ['alignment' => Jc::CENTER]);
        }
    }
    $section->addTextBreak(1);

    // 執行該選項對應的查詢（若有設定查詢）
    $sql = $queryMap[$option];
    if ($sql == "") {
        continue;
    }
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $description = $row['file_name'];
            $fileContent = $row['file_content'];
            $ext = strtolower(pathinfo($description, PATHINFO_EXTENSION));

            // 處理圖片檔案
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
                // 若屬於競賽證明、成績單、學歷證明、實習證明、語言能力證明，套用競賽版面樣式
                if (in_array($option, ['competition', 'transcript', 'diploma', 'internship', 'language'])) {
                    $textStyle = ['size' => 12];
                    $paragraphStyle = ['alignment' => Jc::CENTER];
                } elseif ($option === 'autobiography') {
                    $textStyle = $autobiographyTextStyle;
                    $paragraphStyle = $autobiographyParagraphStyle;
                } else {
                    $textStyle = ['size' => 12];
                    $paragraphStyle = ['alignment' => Jc::BOTH];
                }
                
                // 在圖片上方顯示檔案名稱
                $section->addText("檔案名稱：$description", $textStyle, $paragraphStyle);
            
                if (!empty($fileContent) && strlen($fileContent) > 100) {
                    try {
                        $section->addImage($fileContent, [
                            'width'     => 300,
                            'height'    => 200,
                            'alignment' => Jc::CENTER,
                        ]);
                    } catch (Exception $e) {
                        $section->addText("插入圖片失敗：$description", ['italic' => true, 'color' => 'FF0000'], $paragraphStyle);
                    }
                } else {
                    $section->addText("無效的影像資料：$description", $textStyle, $paragraphStyle);
                }
                $section->addTextBreak(1);
            }
            // 處理 DOCX 檔案
            else if ($ext === 'docx') {
                if (!empty($fileContent)) {
                    try {
                        $tempFilePath = tempnam(sys_get_temp_dir(), 'docx_');
                        if ($tempFilePath === false) {
                            throw new Exception("無法建立臨時檔案");
                        }
                        file_put_contents($tempFilePath, $fileContent);

                        $zip = new ZipArchive();
                        if ($zip->open($tempFilePath) === true) {
                            $xmlContent = $zip->getFromName('word/document.xml');
                            $zip->close();
                            unlink($tempFilePath);

                            if ($xmlContent === false) {
                                throw new Exception('未能讀取 document.xml 檔案');
                            }

                            $xml = simplexml_load_string($xmlContent);
                            $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                            $textNodes = $xml->xpath('//w:t');

                            $content = '';
                            foreach ($textNodes as $node) {
                                $content .= (string)$node . "\n";
                            }

                            if ($option === 'autobiography') {
                                $section->addText($content, $autobiographyTextStyle, $autobiographyParagraphStyle);
                            } elseif (in_array($option, ['competition', 'transcript', 'diploma', 'internship', 'language'])) {
                                // 競賽版面樣式：置中輸出 DOCX 內容
                                $section->addText(
                                    $content,
                                    ['name' => 'Times New Roman', 'size' => 12],
                                    ['alignment' => Jc::CENTER]
                                );
                            } else {
                                $section->addText(
                                    $content,
                                    ['name' => 'Times New Roman', 'size' => 12],
                                    ['alignment' => Jc::BOTH]
                                );
                            }
                        } else {
                            unlink($tempFilePath);
                            throw new Exception('無法開啟 .docx 文件');
                        }
                    } catch (Exception $e) {
                        $section->addText("讀取 DOCX 檔案失敗：$description - " . $e->getMessage(), ['italic' => true, 'color' => 'FF0000']);
                    }
                } else {
                    $section->addText("無效的 DOCX 檔案資料：$description");
                }
            }
            // 其他不支援的檔案類型
            else {
                $section->addText("不支援的檔案類型：$description", ['italic' => true, 'color' => 'FF0000'], ['alignment' => Jc::CENTER]);
            }
            $section->addTextBreak(1);
        }
    } else {
        $section->addText("查無資料：{$optionNames[$option]}", ['size' => 12], ['alignment' => Jc::CENTER]);
    }
}

$conn->close();

// --------------------
// 7. 輸出 Word 文件
// --------------------
header("Content-Description: File Transfer");
header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header("Content-Disposition: attachment; filename=\"$userId.docx\"");
header("Expires: 0");
header("Cache-Control: must-revalidate");
header("Pragma: public");

try {
    $phpWord->save("php://output", 'Word2007');
} catch (Exception $e) {
    die("生成 Word 文件失敗：" . $e->getMessage());
}
?>

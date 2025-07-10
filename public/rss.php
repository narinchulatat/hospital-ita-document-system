<?php
/**
 * RSS Feed for Hospital ITA Document System
 * Provides RSS feed for latest approved public documents
 */

header('Content-Type: application/rss+xml; charset=UTF-8');

require_once '../config/config.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';

// Auto-load classes
spl_autoload_register(function ($className) {
    $classFile = __DIR__ . '/../classes/' . $className . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

try {
    $document = new Document();
    $category = new Category();
    
    // Get latest 20 approved public documents
    $documents = $document->getAll([
        'is_public' => 1, 
        'status' => 'approved'
    ], 1, 20, 'created_at', 'desc');
    
    // Get site settings
    $siteName = SITE_NAME;
    $siteDescription = SITE_DESCRIPTION;
    $siteUrl = BASE_URL;
    
} catch (Exception $e) {
    error_log("RSS Feed error: " . $e->getMessage());
    $documents = [];
    $siteName = 'ระบบจัดเก็บเอกสาร ITA โรงพยาบาล';
    $siteDescription = 'ระบบจัดการเอกสารสำหรับโรงพยาบาล';
    $siteUrl = 'http://localhost/hospital-ita-document-system';
}

// Generate RSS XML
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title><?= htmlspecialchars($siteName) ?></title>
    <link><?= htmlspecialchars($siteUrl) ?>/public/</link>
    <description><?= htmlspecialchars($siteDescription) ?></description>
    <language>th</language>
    <lastBuildDate><?= date('r') ?></lastBuildDate>
    <generator>Hospital ITA Document System RSS Generator</generator>
    <managingEditor>info@hospital.go.th (ระบบจัดเก็บเอกสาร ITA)</managingEditor>
    <webMaster>webmaster@hospital.go.th (เว็บมาสเตอร์)</webMaster>
    <atom:link href="<?= htmlspecialchars($siteUrl) ?>/public/rss.php" rel="self" type="application/rss+xml" />
    
    <image>
        <url><?= htmlspecialchars($siteUrl) ?>/assets/images/logo.png</url>
        <title><?= htmlspecialchars($siteName) ?></title>
        <link><?= htmlspecialchars($siteUrl) ?>/public/</link>
        <width>144</width>
        <height>144</height>
        <description>โลโก้ระบบจัดเก็บเอกสาร ITA โรงพยาบาล</description>
    </image>
    
    <?php if (!empty($documents)): ?>
        <?php foreach ($documents as $doc): ?>
            <item>
                <title><?= htmlspecialchars($doc['title']) ?></title>
                <link><?= htmlspecialchars($siteUrl) ?>/public/documents/view.php?id=<?= $doc['id'] ?></link>
                <description><![CDATA[
                    <?php if (!empty($doc['description'])): ?>
                        <p><?= htmlspecialchars($doc['description']) ?></p>
                    <?php endif; ?>
                    
                    <p><strong>รายละเอียด:</strong></p>
                    <ul>
                        <li><strong>หมวดหมู่:</strong> <?= htmlspecialchars($doc['category_name'] ?? 'ไม่ระบุ') ?></li>
                        <li><strong>ประเภทไฟล์:</strong> <?= strtoupper(htmlspecialchars($doc['file_type'])) ?></li>
                        <li><strong>ขนาดไฟล์:</strong> <?= formatFileSize($doc['file_size']) ?></li>
                        <li><strong>ดาวน์โหลด:</strong> <?= number_format($doc['download_count']) ?> ครั้ง</li>
                        <li><strong>เข้าชม:</strong> <?= number_format($doc['view_count']) ?> ครั้ง</li>
                    </ul>
                    
                    <p><a href="<?= htmlspecialchars($siteUrl) ?>/public/documents/view.php?id=<?= $doc['id'] ?>">ดูรายละเอียดเอกสาร</a></p>
                    <p><a href="<?= htmlspecialchars($siteUrl) ?>/public/documents/download.php?id=<?= $doc['id'] ?>">ดาวน์โหลดเอกสาร</a></p>
                ]]></description>
                <guid isPermaLink="true"><?= htmlspecialchars($siteUrl) ?>/public/documents/view.php?id=<?= $doc['id'] ?></guid>
                <pubDate><?= date('r', strtotime($doc['created_at'])) ?></pubDate>
                <category><?= htmlspecialchars($doc['category_name'] ?? 'ทั่วไป') ?></category>
                
                <?php if (!empty($doc['keywords'])): ?>
                    <?php 
                    $keywords = explode(',', $doc['keywords']);
                    foreach ($keywords as $keyword): 
                        $keyword = trim($keyword);
                        if (!empty($keyword)):
                    ?>
                        <category><?= htmlspecialchars($keyword) ?></category>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                <?php endif; ?>
                
                <author>webmaster@hospital.go.th (<?= htmlspecialchars($doc['created_by_name'] ?? 'ระบบ') ?>)</author>
                
                <enclosure url="<?= htmlspecialchars($siteUrl) ?>/public/documents/download.php?id=<?= $doc['id'] ?>" 
                          length="<?= $doc['file_size'] ?>" 
                          type="<?= htmlspecialchars(getMimeType($doc['file_type'])) ?>" />
            </item>
        <?php endforeach; ?>
    <?php else: ?>
        <item>
            <title>ยังไม่มีเอกสารในระบบ</title>
            <link><?= htmlspecialchars($siteUrl) ?>/public/</link>
            <description>ขณะนี้ยังไม่มีเอกสารสาธารณะในระบบ กรุณาตรวจสอบอีกครั้งในภายหลัง</description>
            <guid isPermaLink="true"><?= htmlspecialchars($siteUrl) ?>/public/#no-documents</guid>
            <pubDate><?= date('r') ?></pubDate>
        </item>
    <?php endif; ?>
    
</channel>
</rss>

<?php
/**
 * Get MIME type for file extension
 */
function getMimeType($fileExtension) {
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'txt' => 'text/plain',
        'csv' => 'text/csv',
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed'
    ];
    
    return $mimeTypes[strtolower($fileExtension)] ?? 'application/octet-stream';
}
?>
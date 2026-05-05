<?php
require_once __DIR__ . '/storage.php';

function handle_file(&$result, $source, $query, $cfg, $key) {
    switch ($source) {
        case 'file_config':
            $safe = $cfg ?: [];
            unset($safe['password_hash']);
            if (empty($safe)) {
                $safe = ['amap_key'=>'','icp_api'=>'','max_file_size_mb'=>50,
                    'ip_sources'=>['ip-api'=>['enabled'=>true,'name'=>'ip-api.com','note'=>'功能完整，推荐'],'ip-sb'=>['enabled'=>true,'name'=>'ip.sb','note'=>'功能完整'],'ipwhois'=>['enabled'=>true,'name'=>'ipwho.is','note'=>'功能完整'],'ipip'=>['enabled'=>true,'name'=>'IPIP.net','note'=>'无经纬度'],'ip-baidu'=>['enabled'=>true,'name'=>'百度','note'=>'国内定位'],'ip-baota'=>['enabled'=>true,'name'=>'宝塔','note'=>'含经纬度'],'ip9'=>['enabled'=>true,'name'=>'ip9.com.cn','note'=>'国内，含经纬度']]];
            }
            $result['success'] = true; $result['data'] = $safe; return;

        case 'file_list':
            $files = []; $pdo = dbConnect(); $dbFiles = [];
            if ($pdo) { try { $stmt = $pdo->query("SELECT name, user_id FROM files"); while ($r = $stmt->fetch()) $dbFiles[$r['name']] = $r['user_id']; } catch (Exception $e) {} }
            foreach (storageList() as $f) { $f['user_id'] = intval($dbFiles[$f['name']] ?? 0); $f['type'] = 'application/octet-stream'; $files[] = $f; }
            $result['success'] = true; $result['data'] = $files; return;

        case 'file_upload':
            $u = authUser(); if (!$u) { $result['error'] = '请先登录'; return; }
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $result['error'] = '请使用 POST 请求'; return; }
            if (empty($_FILES['file'])) { $result['error'] = '未选择文件'; return; }
            $f = $_FILES['file'];
            if ($f['error'] !== UPLOAD_ERR_OK) { $result['error'] = '上传失败: ' . $f['error']; return; }
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp','svg','bmp','ico','mp4','webm','avi','mov','mkv','mp3','wav','ogg','flac','aac','pdf','doc','docx','xls','xlsx','ppt','pptx','txt','zip','rar','7z','gz','tar','json','xml','csv','md','php','html','css','js','ts','py','java','c','cpp','h','sql','yaml','yml'];
            if ($ext && !in_array($ext, $allowed)) { $result['error'] = '不支持的文件类型 .' . $ext; return; }
            $maxSize = 50 * 1024 * 1024;
            if ($f['size'] > $maxSize) { $result['error'] = '文件超过 50MB 限制'; return; }
            $name = $f['name']; $i = 1;
            $existing = storageList();
            $names = array_column($existing, 'name');
            while (in_array($name, $names)) { $p = pathinfo($name); $name = $p['filename'] . "_({$i})." . ($p['extension'] ?? ''); $i++; }
            if (storagePut($name, $f['tmp_name'])) {
                try { $pdo = dbConnect(); if ($pdo) { try { $pdo->exec("ALTER TABLE files ADD COLUMN user_id INT DEFAULT 0"); } catch (Exception $e) {} $stmt = $pdo->prepare("INSERT INTO files (name, size, type, user_id) VALUES (?, ?, ?, ?)"); $stmt->execute([$name, $f['size'], $f['type'], $u['user_id']]); } } catch (Exception $e) {}
                $result['success'] = true; $result['data'] = ['name' => $name, 'size' => $f['size'], 'type' => $f['type']];
            } else { $result['error'] = '保存文件失败'; }
            return;

        case 'file_upload_array':
            $u = authUser(); if (!$u) { $result['error'] = '请先登录'; return; }
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $result['error'] = '请使用 POST 请求'; return; }
            if (empty($_FILES['file'])) { $result['error'] = '未选择文件'; return; }
            $allowed = ['jpg','jpeg','png','gif','webp','svg','bmp','ico','mp4','webm','avi','mov','mkv','mp3','wav','ogg','flac','aac','pdf','doc','docx','xls','xlsx','ppt','pptx','txt','zip','rar','7z','gz','tar','json','xml','csv','md','php','html','css','js','ts','py','java','c','cpp','h','sql','yaml','yml'];
            $maxSize = 50 * 1024 * 1024; $ufiles = $_FILES['file'];
            if (!is_array($ufiles['name'])) $ufiles = ['name'=>[$ufiles['name']],'tmp_name'=>[$ufiles['tmp_name']],'size'=>[$ufiles['size']],'error'=>[$ufiles['error']],'type'=>[$ufiles['type']]];
            $uploaded = []; try { $pdo = dbConnect(); if ($pdo) { try { $pdo->exec("ALTER TABLE files ADD COLUMN user_id INT DEFAULT 0"); } catch (Exception $e) {} } } catch (Exception $e) { $pdo = null; }
            $existing = storageList();
            $names = array_column($existing, 'name');
            for ($i = 0; $i < count($ufiles['name']); $i++) {
                if ($ufiles['error'][$i] !== UPLOAD_ERR_OK) continue;
                $ext = strtolower(pathinfo($ufiles['name'][$i], PATHINFO_EXTENSION));
                if ($ext && !in_array($ext, $allowed)) continue; if ($ufiles['size'][$i] > $maxSize) continue;
                $name = $ufiles['name'][$i];
                $j = 1;
                while (in_array($name, $names)) { $p = pathinfo($name); $name = $p['filename'] . "_({$j})." . ($p['extension'] ?? ''); $j++; }
                if (storagePut($name, $ufiles['tmp_name'][$i])) {
                    $names[] = $name;
                    $uploaded[] = ['name' => $name, 'size' => $ufiles['size'][$i]];
                    if ($pdo) { try { $stmt = $pdo->prepare("INSERT INTO files (name, size, type, user_id) VALUES (?, ?, ?, ?)"); $stmt->execute([$name, $ufiles['size'][$i], $ufiles['type'][$i] ?? '', $u['user_id']]); } catch (Exception $e) {} }
                }
            }
            $result['success'] = !empty($uploaded); $result['data'] = $uploaded; $result['error'] = empty($uploaded) ? '没有文件被上传' : null;
            return;

        case 'file_delete':
            $u = authUser(); if (!$u) { $result['error'] = '请先登录'; return; }
            $name = basename($_GET['file'] ?? ''); if (!$name) { $result['error'] = '缺少文件名'; return; }
            $pdo = dbConnect();
            if ($u['role'] !== 'admin') {
                $owner = 0;
                if ($pdo) { try { $stmt = $pdo->prepare("SELECT user_id FROM files WHERE name = ?"); $stmt->execute([$name]); $r = $stmt->fetch(); if ($r) $owner = intval($r['user_id']); } catch (Exception $e) {} }
                if ($owner !== intval($u['user_id'])) { $result['error'] = '无权删除该文件'; return; }
            }
            if (storageDelete($name)) {
                if ($pdo) { try { $stmt = $pdo->prepare("DELETE FROM files WHERE name = ?"); $stmt->execute([$name]); } catch (Exception $e) {} }
                $result['success'] = true; $result['data'] = ['deleted' => $name];
            } else { $result['error'] = '删除失败'; }
            return;

        case 'file_download':
            $fn = basename($_GET['file'] ?? '');
            if (!$fn) { $result['error'] = '缺少文件名'; return; }
            $fp = __DIR__ . '/../uploads/' . $fn;
            if (!file_exists($fp)) { http_response_code(404); exit; }
            header('Content-Type: ' . (function_exists('mime_content_type') ? (mime_content_type($fp) ?: 'application/octet-stream') : 'application/octet-stream'));
            header('Content-Length: ' . filesize($fp));
            header("Content-Disposition: inline; filename=\"" . str_replace(['"', "\r", "\n"], '', $fn) . "\"; filename*=UTF-8''" . rawurlencode($fn));
            readfile($fp); exit;
    }
}

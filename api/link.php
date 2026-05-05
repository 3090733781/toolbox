<?php
function link_storage() {
    $fp = __DIR__ . '/../links.json';
    if (!file_exists($fp)) return [];
    return json_decode(file_get_contents($fp), true) ?: [];
}

function link_save($data) {
    file_put_contents(__DIR__ . '/../links.json', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function handle_link(&$result, $source, $query, $cfg, $key) {
    switch ($source) {
        case 'link_list':
            $links = link_storage();
            $changed = false;
            $maxId = 0;
            foreach ($links as $link) {
                if (isset($link['id']) && $link['id'] > $maxId) $maxId = $link['id'];
            }
            foreach ($links as $i => $link) {
                if (!isset($link['id'])) {
                    $links[$i]['id'] = ++$maxId;
                    $changed = true;
                }
            }
            if ($changed) link_save($links);
            $result['success'] = true;
            $result['data'] = $links;
            return;

        case 'link_add':
            @session_start();
            if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { $result['error'] = '无权限'; return; }
            $input = json_decode(file_get_contents('php://input'), true);
            $name = trim($input['name'] ?? '');
            $url = trim($input['url'] ?? '');
            if (!$name || !$url) { $result['error'] = '名称和网址不能为空'; return; }
            $links = link_storage();
            $maxId = 0;
            foreach ($links as $link) {
                if (isset($link['id']) && $link['id'] > $maxId) $maxId = $link['id'];
            }
            $links[] = ['id' => $maxId + 1, 'name' => $name, 'url' => $url, 'time' => date('Y-m-d H:i:s')];
            link_save($links);
            $result['success'] = true;
            $result['data'] = $links;
            return;

        case 'link_delete':
            @session_start();
            if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { $result['error'] = '无权限'; return; }
            $id = intval($_GET['id'] ?? -1);
            if ($id <= 0) { $result['error'] = '无效ID'; return; }
            $links = link_storage();
            $newLinks = [];
            $found = false;
            foreach ($links as $link) {
                if (isset($link['id']) && $link['id'] === $id) {
                    $found = true;
                    continue;
                }
                $newLinks[] = $link;
            }
            if (!$found) { $result['error'] = '友链不存在'; return; }
            link_save($newLinks);
            $result['success'] = true;
            $result['data'] = $newLinks;
            return;
    }
}

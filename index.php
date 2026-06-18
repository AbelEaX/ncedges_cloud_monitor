<?php
session_start();
require __DIR__ . '/helpers.php';
$config = require __DIR__ . '/config.php';
$statusFile = __DIR__ . '/status.json';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

date_default_timezone_set($config['timezone']);
$currentStatus = file_exists($statusFile) ? json_decode(file_get_contents($statusFile), true) : [];

// Calculate quick stats for initial load
$totalServers = count($currentStatus);
$upCount = 0;
foreach ($currentStatus as $s) {
    if (($s['status'] ?? '') === 'up') $upCount++;
}
$downCount = $totalServers - $upCount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $config['company_name']; ?> – Service Health</title>
<style>
:root {
    --bg:#1a1a1a; --sidebar:#1a1a1a; --accent:#ffc107;
    --card:#282828; --border:#444444; --text:#e0e0e0;
    --muted:#a0a0a0; --green:#66bb6a; --red:#ef5350;
}
body{margin:0;font:12px Inter,sans-serif;background:var(--bg);color:var(--text);display:flex;height:100vh;overflow:hidden;}

/* Sidebar Styling */
.sidebar{width:180px;background:var(--sidebar);color:var(--text);display:flex;flex-direction:column;padding:15px;flex-shrink:0;border-right:1px solid var(--border);}
.sidebar h2{font-size:13px;color:var(--text);margin:0 0 15px 0;text-transform:uppercase;letter-spacing:1px;border-bottom:2px solid var(--accent);padding-bottom:5px;}
.nav-item{padding:8px 10px;border-radius:4px;color:var(--muted);text-decoration:none;font-size:11px;margin-bottom:4px;display:block;}
.nav-item.active{background:rgba(255,255,255,0.1);color:var(--text);font-weight:600;}
.sidebar-stats{margin-top:auto;border-top:1px solid var(--border);padding-top:15px;}
.s-stat{display:flex;justify-content:space-between;font-size:11px;margin-bottom:8px;}
.s-stat .val{font-weight:700;color:var(--accent);}

/* Main Content */
.main{flex:1;overflow-y:auto;display:flex;flex-direction:column;}
.header{background:var(--card);padding:10px 15px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;}
.header h1{font-size:14px;margin:0;}
.logout-btn{font-size:11px;color:var(--red);text-decoration:none;font-weight:600;}

.container{padding:15px;}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;}
.card{background:var(--card);border:1px solid var(--border);border-radius:4px;padding:12px;border-left:4px solid var(--accent);}
.card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;}
.card-header h2{font-size:13px;margin:0;color:var(--text);}
.badge{font-size:10px;padding:2px 6px;border-radius:4px;font-weight:700;text-transform:uppercase;}
.ok{background:rgba(102,187,106,0.2);color:var(--green);}
.bad{background:rgba(239,83,80,0.2);color:var(--red);}

/* Meta Data (Reduced Size) */
.meta-row{display:flex;gap:12px;font-size:11px;color:var(--muted);border-top:1px solid #f1f5f9;padding-top:8px;}
.meta-item span{display:block;font-size:9px;text-transform:uppercase;color:var(--muted);}
.meta-item strong{color:var(--text);font-weight:600;}

.history{font-size:10px;color:var(--muted);margin-top:8px;background:var(--bg);padding:5px;border-radius:4px;}
</style>
</head>
<body>
<div class="sidebar">
    <h2>Monitor Portal</h2>
    <a href="index.php" class="nav-item active">Dashboard</a>
    <a href="manage.php" class="nav-item">Manage Assets</a>
    <a href="reports.php" class="nav-item">Reports</a>
    <a href="settings.php" class="nav-item">Settings</a>

    <div class="sidebar-stats">
        <div class="s-stat"><span>Assets</span><span class="val" id="stat-total"><?= $totalServers; ?></span></div>
        <div class="s-stat"><span>Online</span><span class="val" style="color:#22c55e" id="stat-up"><?= $upCount; ?></span></div>
        <div class="s-stat"><span>Offline</span><span class="val" style="color:#ef4444" id="stat-down"><?= $downCount; ?></span></div>
    </div>
</div>

<div class="main">
    <div class="header">
        <h1>Infrastructure Overview</h1>
        <div>
            <span id="last-refreshed" style="font-size:12px; color:var(--muted); margin-right:15px;">Updated: <?= date('H:i:s'); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
<div class="grid" id="server-grid">
<?php foreach ($currentStatus as $srv): ?>
<div class="card">
    <div class="card-header">
        <h2><?= htmlspecialchars($srv['name']); ?></h2>
        <span class="badge <?= $srv['status']==='up'?'ok':'bad' ?>">
            <?= strtoupper($srv['status']); ?>
        </span>
    </div>
    <div class="meta-row">
        <div class="meta-item"><span>Duration</span><strong><?= formatDuration(time()-$srv['since']); ?></strong></div>
        <div class="meta-item"><span>Checked</span><strong><?= isset($srv['last_check']) ? date('H:i', $srv['last_check']) : 'N/A'; ?></strong></div>
        <div class="meta-item"><span>Latency</span><strong><?= $srv['latency'] ?? 0; ?>ms</strong></div>
    </div>
    <?php if(!empty($srv['history'])): ?>
    <div class="history">
        <strong>Incident History:</strong><br>
        <?php foreach(array_reverse($srv['history']) as $h): ?>
            <?= ucfirst($h['status']); ?> at <?= date('Y-m-d H:i:s',$h['timestamp']); ?><br>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</div>
    </div>
</div>

<script>
function duration(seconds){
    let h=Math.floor(seconds/3600), m=Math.floor((seconds%3600)/60);
    return `${h.toString().padStart(2,'0')}h ${m.toString().padStart(2,'0')}m`;
}

function fetchStatus(){
    fetch('status_api.php',{cache:"no-store"})
    .then(res=>res.json())
    .then(data=>{
        // Update Summary Stats
        const servers = Object.values(data.servers);
        const total = servers.length;
        const up = servers.filter(s => s.status === 'up').length;
        const down = total - up;

        document.getElementById("stat-total").textContent = total;
        document.getElementById("stat-up").textContent = up;
        document.getElementById("stat-down").textContent = down;

        // Update Grid
        document.getElementById("last-refreshed").textContent = "Updated: "+data.timestamp.split(' ')[1];
        const grid=document.getElementById("server-grid");
        grid.innerHTML="";
        Object.values(data.servers).forEach(srv=>{
            const card=document.createElement("div"); card.className="card";
            let badgeClass=srv.status==="up"?"ok":"bad";
            let uptime=duration(Math.floor(Date.now()/1000-srv.since));
            let historyHtml="";
            if(srv.history&&srv.history.length>0){
                historyHtml=`<div class="history"><strong>Incident History:</strong><br>`;
                srv.history.slice().reverse().forEach(h=>{
                    let ts=new Date(h.timestamp*1000).toLocaleString();
                    historyHtml+=`${h.status.charAt(0).toUpperCase()+h.status.slice(1)} at ${ts}<br>`;
                });
                historyHtml+="</div>";
            }
            card.innerHTML=`
                <div class="card-header">
                    <h2>${srv.name}</h2>
                    <span class="badge ${badgeClass}">${srv.status.toUpperCase()}</span>
                </div>
                <div class="meta-row">
                    <div class="meta-item"><span>Duration</span><strong>${uptime}</strong></div>
                    <div class="meta-item"><span>Checked</span><strong>${srv.last_check ? new Date(srv.last_check * 1000).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'N/A'}</strong></div>
                    <div class="meta-item"><span>Latency</span><strong>${srv.latency || 0}ms</strong></div>
                </div>
                ${historyHtml}
            `;
            grid.appendChild(card);
        });
    })
    .catch(err=>console.error("Failed to fetch status:",err));
}

// Initial fetch
fetchStatus();
// Auto-refresh every 60s
setInterval(fetchStatus,60000);
</script>
</body>
</html>

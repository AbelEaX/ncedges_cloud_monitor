$files = @("metrics.php", "uptime.php", "alerts.php", "activity.php", "export.php")
foreach ($file in $files) {
    $path = "public/api/reports/$file"
    $content = Get-Content $path -Raw
    
    # Check if export.php needs permission fix
    if ($file -eq "export.php") {
        $content = $content -replace "\!\$auth->hasPermission\('reports\.view'\)", "!`$auth->hasPermission('reports.export')"
    }
    
    # Replace range parsing
    $rangeLogic = "    `$startDate = `$_GET['startDate'] ?? null;
    `$endDate = `$_GET['endDate'] ?? null;
    
    if (`$startDate -and `$endDate) {
        `$whereClause = `"created_at BETWEEN :start AND :end`";
        `$checkedAtWhereClause = `"checked_at BETWEEN :start AND :end`";
        `$params = ['start' => `$startDate . ' 00:00:00', 'end' => `$endDate . ' 23:59:59'];
    } else {
        `$range = `$_GET['range'] ?? '7d';
        `$rangeFilter = match(`$range) {
            '24h' => '-1 day',
            '30d' => '-30 days',
            '90d' => '-90 days',
            default => '-7 days',
        };
        `$whereClause = `"created_at >= datetime('now', :range)`";
        `$checkedAtWhereClause = `"checked_at >= datetime('now', :range)`";
        `$params = ['range' => `$rangeFilter];
    }"
    
    # Update metrics.php
    if ($file -eq "metrics.php") {
        $content = $content -replace "`$range = `$_GET\['range'\] \?\? '7d';.*`$rangeFilter = match\(`$range\) \{.*default => '-7 days',.*    \};(?s)", $rangeLogic
        $content = $content -replace "checked_at >= datetime\('now', :range\)", "`$checkedAtWhereClause"
    }
    
    # Update alerts.php
    if ($file -eq "alerts.php") {
        $content = $content -replace "`$range = `$_GET\['range'\] \?\? '7d';.*`$rangeFilter = match\(`$range\) \{.*default => '-7 days',.*    \};(?s)", $rangeLogic
        $content = $content -replace "created_at >= datetime\('now', :range\)", "`$whereClause"
    }
    
    # Update activity.php
    if ($file -eq "activity.php") {
        $content = $content -replace "`$range = `$_GET\['range'\] \?\? '7d';.*`$rangeFilter = match\(`$range\) \{.*default => '-7 days',.*    \};(?s)", $rangeLogic
        $content = $content -replace "created_at >= datetime\('now', :range\)", "`$whereClause"
    }
    
    Set-Content $path $content
}

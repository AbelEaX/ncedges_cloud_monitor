<?php
/**
 * Activity Timeline Component
 * 
 * Displays recent activities in a timeline format
 * Usage: <?= component('timeline.activity', ['activities' => $activities]) ?>
 */
$activities = $activities ?? [];
$maxItems = $maxItems ?? 10;
?>

<div class="activity-timeline" style="
    border-left: 2px solid var(--border);
    padding-left: 20px;
    position: relative;
">
    <?php foreach (array_slice($activities, 0, $maxItems) as $activity): ?>
    <div class="activity-item" style="
        margin-bottom: 20px;
        position: relative;
    ">
        <!-- Timeline dot -->
        <div style="
            position: absolute;
            left: -27px;
            top: 4px;
            width: 12px;
            height: 12px;
            background: var(--primary);
            border: 2px solid var(--card);
            border-radius: 50%;
        "></div>
        
        <!-- Activity content -->
        <div style="
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 12px;
        ">
            <div style="
                display: flex;
                justify-content: space-between;
                align-items: start;
                margin-bottom: 4px;
            ">
                <strong style="
                    font-size: 12px;
                    color: var(--text);
                    text-transform: capitalize;
                ">
                    <?= htmlspecialchars($activity['action'] ?? 'activity'); ?>
                </strong>
                <span style="
                    font-size: 11px;
                    color: var(--muted);
                ">
                    <?php 
                    $createdAt = $activity['created_at'] ?? '';
                    if ($createdAt) {
                        $time = strtotime($createdAt);
                        echo date('M d, H:i', $time);
                    }
                    ?>
                </span>
            </div>
            
            <p style="
                margin: 0;
                font-size: 11px;
                color: var(--muted);
                line-height: 1.4;
            ">
                <?php 
                $description = $activity['description'] ?? 'User activity';
                if (isset($activity['entity_type'])) {
                    $description = ucfirst($activity['action']) . ' ' . $activity['entity_type'];
                    if (isset($activity['details'])) {
                        $details = is_string($activity['details']) ? json_decode($activity['details'], true) : $activity['details'];
                        if (!empty($details)) {
                            $description .= ' (' . implode(', ', array_slice($details, 0, 2)) . ')';
                        }
                    }
                }
                echo htmlspecialchars($description);
                ?>
            </p>
            
            <?php if (isset($activity['user_id']) && $activity['user_id']): ?>
            <div style="
                margin-top: 8px;
                padding-top: 8px;
                border-top: 1px solid var(--border);
                font-size: 10px;
                color: var(--muted);
            ">
                by User #<?= $activity['user_id']; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php if (empty($activities)): ?>
    <div style="
        text-align: center;
        padding: 20px;
        color: var(--muted);
        font-size: 12px;
    ">
        No activities yet
    </div>
    <?php endif; ?>
</div>

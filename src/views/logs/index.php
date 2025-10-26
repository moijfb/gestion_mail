<div class="logs-page">
    <h2>Journaux d'activité</h2>

    <div class="log-controls">
        <form method="GET" action="<?= $base_url ?>/logs" class="filter-form">
            <select name="type" onchange="this.form.submit()">
                <?php foreach ($available_logs as $log): ?>
                    <option value="<?= htmlspecialchars($log) ?>" <?= $log_type === $log ? 'selected' : '' ?>>
                        <?= ucfirst($log) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="lines" onchange="this.form.submit()">
                <option value="50" <?= $lines == 50 ? 'selected' : '' ?>>50 lignes</option>
                <option value="100" <?= $lines == 100 ? 'selected' : '' ?>>100 lignes</option>
                <option value="200" <?= $lines == 200 ? 'selected' : '' ?>>200 lignes</option>
                <option value="500" <?= $lines == 500 ? 'selected' : '' ?>>500 lignes</option>
            </select>
        </form>
    </div>

    <div class="log-viewer">
        <pre><?php
            if (empty($logs)) {
                echo "Aucune entrée de log";
            } else {
                foreach ($logs as $line) {
                    echo htmlspecialchars($line);
                }
            }
        ?></pre>
    </div>
</div>

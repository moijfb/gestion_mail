<div class="unsubscriptions-page">
    <div class="page-header">
        <h2>Désinscriptions (<?= count($unsubscribed) ?>)</h2>
        <a href="<?= $base_url ?>/unsubscriptions/export" class="btn">Exporter CSV</a>
    </div>

    <div class="stats-row">
        <div class="stat-item">
            <strong>Total:</strong> <?= $stats['total'] ?>
        </div>
        <?php foreach ($stats['by_source'] as $source => $count): ?>
            <div class="stat-item">
                <strong><?= ucfirst($source) ?>:</strong> <?= $count ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="filters">
        <form method="GET" action="<?= $base_url ?>/unsubscriptions" class="filter-form">
            <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">

            <select name="source">
                <option value="">Toutes les sources</option>
                <option value="public" <?= ($filters['source'] ?? '') === 'public' ? 'selected' : '' ?>>Public</option>
                <option value="manual" <?= ($filters['source'] ?? '') === 'manual' ? 'selected' : '' ?>>Manuel</option>
                <option value="import" <?= ($filters['source'] ?? '') === 'import' ? 'selected' : '' ?>>Import</option>
            </select>

            <button type="submit" class="btn">Filtrer</button>
            <a href="<?= $base_url ?>/unsubscriptions" class="btn btn-secondary">Réinitialiser</a>
        </form>
    </div>

    <?php if (empty($unsubscribed)): ?>
        <p class="no-data">Aucune désinscription</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Date</th>
                    <th>Raison</th>
                    <th>Source</th>
                    <th>IP</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unsubscribed as $entry): ?>
                    <tr>
                        <td><?= htmlspecialchars($entry['email']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($entry['date'])) ?></td>
                        <td><?= htmlspecialchars($entry['reason']) ?></td>
                        <td><span class="badge"><?= htmlspecialchars($entry['source']) ?></span></td>
                        <td><?= htmlspecialchars($entry['ip']) ?></td>
                        <td class="actions">
                            <a href="<?= $base_url ?>/unsubscriptions/reactivate?email=<?= urlencode($entry['email']) ?>&csrf_token=<?= $csrf_token ?>"
                               class="btn btn-sm"
                               onclick="return confirm('Confirmer la réactivation ? Ne réactivez que si vous avez le consentement explicite.')">
                                Réactiver
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

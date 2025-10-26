<div class="dashboard">
    <h2>Tableau de bord</h2>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Contacts</h3>
            <div class="stat-number"><?= $contact_stats['total'] ?></div>
            <div class="stat-details">
                <span class="badge badge-success"><?= $contact_stats['active'] ?> actifs</span>
                <span class="badge badge-danger"><?= $contact_stats['unsubscribed'] ?> désinscrits</span>
                <?php if ($contact_stats['pending'] > 0): ?>
                    <span class="badge badge-warning"><?= $contact_stats['pending'] ?> en attente</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="stat-card">
            <h3>Groupes</h3>
            <div class="stat-number"><?= count($group_stats) ?></div>
            <div class="stat-details">
                <a href="<?= $base_url ?>/groups" class="btn btn-sm">Gérer les groupes</a>
            </div>
        </div>

        <div class="stat-card">
            <h3>Désinscriptions</h3>
            <div class="stat-number"><?= $unsubscribed_stats['total'] ?></div>
            <div class="stat-details">
                <?php if (!empty($unsubscribed_stats['recent'])): ?>
                    Dernière: <?= date('d/m/Y', strtotime($unsubscribed_stats['recent'][0]['date'])) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-section">
            <h3>Top 10 Groupes</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Contacts</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($top_groups)): ?>
                        <tr>
                            <td colspan="2">Aucun groupe</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($top_groups as $groupId => $group): ?>
                            <tr>
                                <td><?= htmlspecialchars($group['name']) ?></td>
                                <td><?= $group['count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="dashboard-section">
            <h3>Top 10 Domaines</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Domaine</th>
                        <th>Contacts</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contact_stats['by_domain'])): ?>
                        <tr>
                            <td colspan="2">Aucune donnée</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach (array_slice($contact_stats['by_domain'], 0, 10, true) as $domain => $count): ?>
                            <tr>
                                <td><?= htmlspecialchars($domain) ?></td>
                                <td><?= $count ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="actions-section">
        <h3>Actions rapides</h3>
        <div class="action-buttons">
            <a href="<?= $base_url ?>/contacts/create" class="btn btn-primary">Nouveau contact</a>
            <a href="<?= $base_url ?>/groups/create" class="btn btn-primary">Nouveau groupe</a>
            <a href="<?= $base_url ?>/import" class="btn btn-secondary">Importer des contacts</a>
            <a href="<?= $base_url ?>/composer" class="btn btn-success">Préparer un envoi</a>
        </div>
    </div>
</div>

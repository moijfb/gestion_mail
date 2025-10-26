<div class="contacts-page">
    <div class="page-header">
        <h2>Contacts (<?= count($contacts) ?>)</h2>
        <a href="<?= $base_url ?>/contacts/create" class="btn btn-primary">Nouveau contact</a>
    </div>

    <div class="filters">
        <form method="GET" action="<?= $base_url ?>/contacts" class="filter-form">
            <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">

            <select name="groupe">
                <option value="">Tous les groupes</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= $group['id'] ?>" <?= ($filters['groupe'] ?? '') === $group['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($group['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="tag">
                <option value="">Tous les tags</option>
                <?php foreach ($all_tags as $tag): ?>
                    <option value="<?= htmlspecialchars($tag) ?>" <?= ($filters['tag'] ?? '') === $tag ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tag) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="statut">
                <option value="">Tous les statuts</option>
                <option value="active" <?= ($filters['statut'] ?? '') === 'active' ? 'selected' : '' ?>>Actifs</option>
                <option value="unsubscribed" <?= ($filters['statut'] ?? '') === 'unsubscribed' ? 'selected' : '' ?>>Désinscrits</option>
                <option value="pending" <?= ($filters['statut'] ?? '') === 'pending' ? 'selected' : '' ?>>En attente</option>
            </select>

            <button type="submit" class="btn">Filtrer</button>
            <a href="<?= $base_url ?>/contacts" class="btn btn-secondary">Réinitialiser</a>
        </form>
    </div>

    <?php if (empty($contacts)): ?>
        <p class="no-data">Aucun contact trouvé</p>
    <?php else: ?>
        <div class="table-actions">
            <a href="<?= $base_url ?>/export?<?= http_build_query($filters) ?>" class="btn btn-sm">Exporter la sélection</a>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Société</th>
                    <th>Groupes</th>
                    <th>Tags</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $contact): ?>
                    <tr class="status-<?= $contact['statut'] ?>">
                        <td><?= htmlspecialchars($contact['email']) ?></td>
                        <td><?= htmlspecialchars($contact['nom']) ?></td>
                        <td><?= htmlspecialchars($contact['prenom']) ?></td>
                        <td><?= htmlspecialchars($contact['societe']) ?></td>
                        <td>
                            <?php if (!empty($contact['groupes'])): ?>
                                <?php foreach ($contact['groupes'] as $groupeId): ?>
                                    <?php
                                    $groupe = array_filter($groups, fn($g) => $g['id'] === $groupeId);
                                    $groupe = !empty($groupe) ? reset($groupe) : null;
                                    ?>
                                    <?php if ($groupe): ?>
                                        <span class="badge"><?= htmlspecialchars($groupe['name']) ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($contact['tags'])): ?>
                                <?php foreach ($contact['tags'] as $tag): ?>
                                    <span class="badge badge-info"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($contact['statut'] === 'active'): ?>
                                <span class="badge badge-success">Actif</span>
                            <?php elseif ($contact['statut'] === 'unsubscribed'): ?>
                                <span class="badge badge-danger">Désinscrit</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><?= htmlspecialchars($contact['statut']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <a href="<?= $base_url ?>/contacts/edit/<?= $contact['id'] ?>" class="btn btn-sm">Modifier</a>
                            <a href="<?= $base_url ?>/contacts/delete/<?= $contact['id'] ?>?csrf_token=<?= $csrf_token ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

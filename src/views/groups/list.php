<div class="groups-page">
    <div class="page-header">
        <h2>Groupes (<?= count($groups) ?>)</h2>
        <a href="<?= $base_url ?>/groups/create" class="btn btn-primary">Nouveau groupe</a>
    </div>

    <?php if (empty($groups)): ?>
        <p class="no-data">Aucun groupe créé</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Nombre de contacts</th>
                    <th>Date de création</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $group): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($group['name']) ?></strong></td>
                        <td><?= htmlspecialchars($group['description']) ?></td>
                        <td><?= $stats[$group['id']]['count'] ?? 0 ?></td>
                        <td><?= date('d/m/Y', strtotime($group['date_created'])) ?></td>
                        <td class="actions">
                            <a href="<?= $base_url ?>/groups/view/<?= $group['id'] ?>" class="btn btn-sm">Voir</a>
                            <a href="<?= $base_url ?>/groups/edit/<?= $group['id'] ?>" class="btn btn-sm">Modifier</a>
                            <a href="<?= $base_url ?>/groups/delete/<?= $group['id'] ?>?csrf_token=<?= $csrf_token ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

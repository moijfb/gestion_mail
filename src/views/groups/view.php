<div class="group-view-page">
    <div class="page-header">
        <h2><?= htmlspecialchars($group['name']) ?></h2>
        <a href="<?= $base_url ?>/groups/edit/<?= $group['id'] ?>" class="btn">Modifier</a>
    </div>

    <div class="group-info">
        <p><strong>Description:</strong> <?= htmlspecialchars($group['description']) ?></p>
        <p><strong>Date de création:</strong> <?= date('d/m/Y', strtotime($group['date_created'])) ?></p>
        <p><strong>Nombre de contacts:</strong> <?= count($contacts) ?></p>
    </div>

    <h3>Contacts du groupe</h3>

    <?php if (empty($contacts)): ?>
        <p class="no-data">Aucun contact dans ce groupe</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Société</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td><?= htmlspecialchars($contact['email']) ?></td>
                        <td><?= htmlspecialchars($contact['nom']) ?></td>
                        <td><?= htmlspecialchars($contact['prenom']) ?></td>
                        <td><?= htmlspecialchars($contact['societe']) ?></td>
                        <td>
                            <?php if ($contact['statut'] === 'active'): ?>
                                <span class="badge badge-success">Actif</span>
                            <?php else: ?>
                                <span class="badge badge-danger"><?= htmlspecialchars($contact['statut']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

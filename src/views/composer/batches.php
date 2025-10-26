<div class="batches-page">
    <h2>Lots d'envoi préparés</h2>

    <div class="summary">
        <p><strong>Total de contacts:</strong> <?= $total_contacts ?></p>
        <p><strong>Nombre de lots:</strong> <?= $total_batches ?></p>
        <p><strong>Maximum par lot:</strong> <?= $max_per_send ?></p>
        <?php if ($pause_between_batches > 0): ?>
            <p><strong>Pause entre lots:</strong> <?= $pause_between_batches ?> secondes</p>
        <?php endif; ?>
    </div>

    <?php if (empty($batches)): ?>
        <p class="no-data">Aucun contact correspondant aux filtres</p>
    <?php else: ?>
        <?php foreach ($batches as $batch): ?>
            <div class="batch-card">
                <h3>Lot <?= $batch['number'] ?> (<?= $batch['count'] ?> contacts)</h3>

                <div class="batch-actions">
                    <button class="btn btn-primary copy-btn" data-content="<?= htmlspecialchars($batch['emails_string']) ?>">
                        Copier les adresses (CCI)
                    </button>
                    <button class="btn btn-secondary copy-btn" data-content="<?= htmlspecialchars(implode("\n", $batch['emails'])) ?>">
                        Copier la liste (une par ligne)
                    </button>
                </div>

                <div class="batch-preview">
                    <strong>Aperçu:</strong>
                    <textarea readonly rows="3"><?= htmlspecialchars($batch['emails_string']) ?></textarea>
                </div>

                <details>
                    <summary>Voir les contacts (<?= $batch['count'] ?>)</summary>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($batch['contacts'] as $contact): ?>
                                <tr>
                                    <td><?= htmlspecialchars($contact['email']) ?></td>
                                    <td><?= htmlspecialchars($contact['nom']) ?></td>
                                    <td><?= htmlspecialchars($contact['prenom']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </details>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="form-actions">
        <a href="<?= $base_url ?>/composer" class="btn btn-secondary">Nouvelle préparation</a>
    </div>
</div>

<script>
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const content = this.dataset.content;
        navigator.clipboard.writeText(content).then(() => {
            const original = this.textContent;
            this.textContent = 'Copié !';
            setTimeout(() => {
                this.textContent = original;
            }, 2000);
        });
    });
});
</script>

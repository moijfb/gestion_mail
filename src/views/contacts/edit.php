<div class="contact-form-page">
    <h2>Modifier contact</h2>

    <form method="POST" action="<?= $base_url ?>/contacts/edit/<?= $contact['id'] ?>" class="form">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($contact['email']) ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($contact['prenom']) ?>">
            </div>

            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($contact['nom']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="societe">Société</label>
            <input type="text" id="societe" name="societe" value="<?= htmlspecialchars($contact['societe']) ?>">
        </div>

        <div class="form-group">
            <label for="telephone">Téléphone</label>
            <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($contact['telephone']) ?>">
        </div>

        <div class="form-group">
            <label for="groupes">Groupes</label>
            <select id="groupes" name="groupes[]" multiple size="5">
                <?php foreach ($groups as $group): ?>
                    <option value="<?= $group['id'] ?>" <?= in_array($group['id'], $contact['groupes']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($group['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="tags">Tags</label>
            <input type="text" id="tags" name="tags" value="<?= htmlspecialchars(implode(', ', $contact['tags'])) ?>">
            <small>Séparés par des virgules</small>
        </div>

        <div class="form-group">
            <label for="statut">Statut</label>
            <select id="statut" name="statut">
                <option value="active" <?= $contact['statut'] === 'active' ? 'selected' : '' ?>>Actif</option>
                <option value="unsubscribed" <?= $contact['statut'] === 'unsubscribed' ? 'selected' : '' ?>>Désinscrit</option>
                <option value="pending" <?= $contact['statut'] === 'pending' ? 'selected' : '' ?>>En attente</option>
            </select>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="4"><?= htmlspecialchars($contact['notes']) ?></textarea>
        </div>

        <div class="form-info">
            <p><strong>Date d'ajout:</strong> <?= date('d/m/Y H:i', strtotime($contact['date_added'])) ?></p>
            <p><strong>Dernière modification:</strong> <?= date('d/m/Y H:i', strtotime($contact['date_modified'])) ?></p>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="<?= $base_url ?>/contacts" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

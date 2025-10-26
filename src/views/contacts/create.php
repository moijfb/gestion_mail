<div class="contact-form-page">
    <h2>Nouveau contact</h2>

    <form method="POST" action="<?= $base_url ?>/contacts/create" class="form">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom">
            </div>

            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom">
            </div>
        </div>

        <div class="form-group">
            <label for="societe">Société</label>
            <input type="text" id="societe" name="societe">
        </div>

        <div class="form-group">
            <label for="telephone">Téléphone</label>
            <input type="tel" id="telephone" name="telephone">
        </div>

        <div class="form-group">
            <label for="groupes">Groupes</label>
            <select id="groupes" name="groupes[]" multiple size="5">
                <?php foreach ($groups as $group): ?>
                    <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <small>Maintenez Ctrl (Cmd sur Mac) pour sélectionner plusieurs groupes</small>
        </div>

        <div class="form-group">
            <label for="tags">Tags</label>
            <input type="text" id="tags" name="tags" placeholder="Séparés par des virgules">
            <small>Exemple: lead, premium, 2025</small>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="4"></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Créer le contact</button>
            <a href="<?= $base_url ?>/contacts" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<div class="composer-page">
    <h2>Préparer un envoi</h2>

    <div class="info-box">
        <p><strong>Nombre maximum par envoi:</strong> <?= $max_per_send ?> contacts</p>
        <p><strong>Séparateur:</strong> <?= htmlspecialchars($separator) ?></p>
    </div>

    <form method="POST" action="<?= $base_url ?>/composer/prepare" class="form">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

        <h3>Filtres de sélection</h3>

        <div class="form-group">
            <label for="search">Recherche texte libre</label>
            <input type="text" id="search" name="search" placeholder="Rechercher...">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="groupe">Groupe</label>
                <select id="groupe" name="groupe">
                    <option value="">Tous les groupes</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="tag">Tag</label>
                <select id="tag" name="tag">
                    <option value="">Tous les tags</option>
                    <?php foreach ($all_tags as $tag): ?>
                        <option value="<?= htmlspecialchars($tag) ?>"><?= htmlspecialchars($tag) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="domaine">Domaine</label>
            <input type="text" id="domaine" name="domaine" placeholder="exemple.com">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Préparer les lots</button>
        </div>
    </form>
</div>

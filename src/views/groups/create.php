<div class="group-form-page">
    <h2>Nouveau groupe</h2>

    <form method="POST" action="<?= $base_url ?>/groups/create" class="form">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

        <div class="form-group">
            <label for="name">Nom *</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Créer le groupe</button>
            <a href="<?= $base_url ?>/groups" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

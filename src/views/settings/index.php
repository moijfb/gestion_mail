<div class="settings-page">
    <h2>Paramètres</h2>

    <div class="settings-sections">
        <section class="settings-section">
            <h3>Paramètres d'envoi</h3>
            <form method="POST" action="<?= $base_url ?>/settings" class="form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <div class="form-group">
                    <label for="max_per_send">Nombre maximum par envoi</label>
                    <input type="number" id="max_per_send" name="max_per_send" value="<?= $settings['max_per_send'] ?>" min="1">
                </div>

                <div class="form-group">
                    <label for="separator">Séparateur d'emails</label>
                    <select id="separator" name="separator">
                        <option value=";" <?= $settings['separator'] === ';' ? 'selected' : '' ?>>Point-virgule (;)</option>
                        <option value="," <?= $settings['separator'] === ',' ? 'selected' : '' ?>>Virgule (,)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pause_between_batches">Pause entre lots (secondes)</label>
                    <input type="number" id="pause_between_batches" name="pause_between_batches" value="<?= $settings['pause_between_batches'] ?>" min="0">
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </section>

        <section class="settings-section">
            <h3>Paramètres d'import</h3>
            <form method="POST" action="<?= $base_url ?>/settings" class="form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <div class="form-group">
                    <label for="import_encoding">Encodage par défaut</label>
                    <select id="import_encoding" name="import_encoding">
                        <option value="UTF-8" <?= $settings['import_encoding'] === 'UTF-8' ? 'selected' : '' ?>>UTF-8</option>
                        <option value="ISO-8859-1" <?= $settings['import_encoding'] === 'ISO-8859-1' ? 'selected' : '' ?>>ISO-8859-1</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="import_separator">Séparateur CSV par défaut</label>
                    <select id="import_separator" name="import_separator">
                        <option value="," <?= $settings['import_separator'] === ',' ? 'selected' : '' ?>>Virgule (,)</option>
                        <option value=";" <?= $settings['import_separator'] === ';' ? 'selected' : '' ?>>Point-virgule (;)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </section>

        <section class="settings-section">
            <h3>Sauvegardes</h3>
            <form method="POST" action="<?= $base_url ?>/settings" class="form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="auto_backup" value="1" <?= $settings['auto_backup'] ? 'checked' : '' ?>>
                        Activer les sauvegardes automatiques
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>

            <div class="backup-actions">
                <a href="<?= $base_url ?>/settings/backup?csrf_token=<?= $csrf_token ?>" class="btn btn-secondary">Créer une sauvegarde maintenant</a>
            </div>
        </section>

        <section class="settings-section">
            <h3>Changer le mot de passe</h3>
            <form method="POST" action="<?= $base_url ?>/settings/password" class="form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <div class="form-group">
                    <label for="old_password">Ancien mot de passe</label>
                    <input type="password" id="old_password" name="old_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>

                <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
            </form>
        </section>
    </div>
</div>

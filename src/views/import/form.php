<div class="import-page">
    <h2>Importer des contacts</h2>

    <div class="info-box">
        <h3>Instructions</h3>
        <ul>
            <li>Le fichier doit être au format CSV</li>
            <li>L'email est obligatoire pour chaque contact</li>
            <li>Vous pouvez mapper les colonnes de votre fichier aux champs des contacts</li>
            <li>Les doublons peuvent être ignorés, mis à jour ou créés</li>
        </ul>
    </div>

    <form method="POST" action="<?= $base_url ?>/import" enctype="multipart/form-data" class="form">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

        <div class="form-group">
            <label for="csv_file">Fichier CSV *</label>
            <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="separator">Séparateur</label>
                <select id="separator" name="separator">
                    <option value=",">Virgule (,)</option>
                    <option value=";">Point-virgule (;)</option>
                    <option value="\t">Tabulation</option>
                </select>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="has_header" value="1" checked>
                    Le fichier contient une ligne d'en-tête
                </label>
            </div>
        </div>

        <div class="form-group">
            <label for="duplicate_action">Action sur les doublons</label>
            <select id="duplicate_action" name="duplicate_action">
                <option value="ignore">Ignorer</option>
                <option value="update">Mettre à jour</option>
                <option value="create">Créer doublon</option>
            </select>
        </div>

        <div class="form-group">
            <label for="default_group">Groupe par défaut (optionnel)</label>
            <select id="default_group" name="default_group">
                <option value="">Aucun</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Mappage des colonnes</label>
            <div class="mapping-info">
                <p>Après avoir sélectionné votre fichier, vous pourrez mapper les colonnes</p>
            </div>
            <input type="hidden" name="mapping[email]" value="email">
            <input type="hidden" name="mapping[nom]" value="nom">
            <input type="hidden" name="mapping[prenom]" value="prenom">
            <input type="hidden" name="mapping[societe]" value="societe">
            <input type="hidden" name="mapping[telephone]" value="telephone">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Importer</button>
            <a href="<?= $base_url ?>/contacts" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

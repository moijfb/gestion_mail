<?php

class Settings
{
    private $fileManager;
    private $settingsFile = 'settings.json';
    private $settings;

    public function __construct()
    {
        $this->fileManager = new FileManager();
        $this->loadSettings();
    }

    /**
     * Charge les paramètres depuis le fichier
     */
    private function loadSettings()
    {
        $defaultSettings = [
            'max_per_send' => 50,
            'separator' => ';',
            'language' => 'fr',
            'timezone' => 'Europe/Paris',
            'import_encoding' => 'UTF-8',
            'import_separator' => ',',
            'auto_backup' => true,
            'backup_frequency' => 'daily',
            'auto_delete_days' => 0,
            'date_format' => 'd/m/Y H:i',
            'pause_between_batches' => 0
        ];

        $this->settings = $this->fileManager->readJson($this->settingsFile, $defaultSettings);

        // Fusionner avec les valeurs par défaut pour les nouvelles clés
        $this->settings = array_merge($defaultSettings, $this->settings);
    }

    /**
     * Obtient un paramètre
     */
    public function get($key, $default = null)
    {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }

    /**
     * Définit un paramètre
     */
    public function set($key, $value)
    {
        $this->settings[$key] = $value;
    }

    /**
     * Définit plusieurs paramètres
     */
    public function setMultiple($settings)
    {
        foreach ($settings as $key => $value) {
            $this->settings[$key] = $value;
        }
    }

    /**
     * Sauvegarde les paramètres
     */
    public function save()
    {
        $this->fileManager->writeJson($this->settingsFile, $this->settings);
    }

    /**
     * Obtient tous les paramètres
     */
    public function getAll()
    {
        return $this->settings;
    }

    /**
     * Réinitialise les paramètres par défaut
     */
    public function reset()
    {
        $this->loadSettings();
        $this->save();
    }
}

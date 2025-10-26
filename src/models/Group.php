<?php

class Group
{
    private $fileManager;
    private $logger;
    private $groupsFile = 'groups.json';

    public function __construct()
    {
        $this->fileManager = new FileManager();
        $this->logger = new Logger();
    }

    /**
     * Génère un ID unique
     */
    private function generateId()
    {
        return uniqid('group_', true);
    }

    /**
     * Récupère tous les groupes
     */
    public function getAll()
    {
        return $this->fileManager->readJson($this->groupsFile, []);
    }

    /**
     * Récupère un groupe par ID
     */
    public function getById($id)
    {
        $groups = $this->getAll();
        foreach ($groups as $group) {
            if ($group['id'] === $id) {
                return $group;
            }
        }
        return null;
    }

    /**
     * Crée un nouveau groupe
     */
    public function create($name, $description = '')
    {
        if (empty($name)) {
            throw new Exception("Le nom du groupe est obligatoire");
        }

        $groups = $this->getAll();

        $group = [
            'id' => $this->generateId(),
            'name' => trim($name),
            'description' => trim($description),
            'date_created' => date('Y-m-d H:i:s'),
            'contact_ids' => []
        ];

        $groups[] = $group;
        $this->fileManager->writeJson($this->groupsFile, $groups);

        $this->logger->audit('CREATE', 'group', $group['id'], ['name' => $name]);

        return $group;
    }

    /**
     * Met à jour un groupe
     */
    public function update($id, $data)
    {
        $groups = $this->getAll();
        $found = false;

        foreach ($groups as $index => $group) {
            if ($group['id'] === $id) {
                $groups[$index] = array_merge($group, $data);
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Exception("Groupe non trouvé");
        }

        $this->fileManager->writeJson($this->groupsFile, $groups);
        $this->logger->audit('UPDATE', 'group', $id, $data);

        return $groups[$index];
    }

    /**
     * Supprime un groupe
     */
    public function delete($id)
    {
        $groups = $this->getAll();
        $filtered = array_filter($groups, function($group) use ($id) {
            return $group['id'] !== $id;
        });

        if (count($filtered) === count($groups)) {
            throw new Exception("Groupe non trouvé");
        }

        $this->fileManager->writeJson($this->groupsFile, array_values($filtered));
        $this->logger->audit('DELETE', 'group', $id);

        // Retirer ce groupe de tous les contacts
        $contactModel = new Contact();
        $contacts = $contactModel->getAll();
        foreach ($contacts as $contact) {
            if (in_array($id, $contact['groupes'])) {
                $contactModel->removeFromGroup($contact['id'], $id);
            }
        }

        return true;
    }

    /**
     * Ajoute un contact à un groupe
     */
    public function addContact($groupId, $contactId)
    {
        $group = $this->getById($groupId);
        if (!$group) {
            throw new Exception("Groupe non trouvé");
        }

        if (!in_array($contactId, $group['contact_ids'])) {
            $group['contact_ids'][] = $contactId;
            $this->update($groupId, ['contact_ids' => $group['contact_ids']]);
        }

        return true;
    }

    /**
     * Retire un contact d'un groupe
     */
    public function removeContact($groupId, $contactId)
    {
        $group = $this->getById($groupId);
        if (!$group) {
            throw new Exception("Groupe non trouvé");
        }

        $group['contact_ids'] = array_values(array_filter($group['contact_ids'], function($id) use ($contactId) {
            return $id !== $contactId;
        }));

        $this->update($groupId, ['contact_ids' => $group['contact_ids']]);

        return true;
    }

    /**
     * Récupère les contacts d'un groupe
     */
    public function getContacts($groupId)
    {
        $group = $this->getById($groupId);
        if (!$group) {
            return [];
        }

        $contactModel = new Contact();
        $allContacts = $contactModel->getAll();

        return array_filter($allContacts, function($contact) use ($group) {
            return in_array($group['id'], $contact['groupes']);
        });
    }

    /**
     * Obtient le nombre de contacts dans chaque groupe
     */
    public function getStats()
    {
        $groups = $this->getAll();
        $contactModel = new Contact();
        $contacts = $contactModel->getAll();

        $stats = [];
        foreach ($groups as $group) {
            $count = 0;
            foreach ($contacts as $contact) {
                if (in_array($group['id'], $contact['groupes'])) {
                    $count++;
                }
            }
            $stats[$group['id']] = [
                'name' => $group['name'],
                'count' => $count
            ];
        }

        return $stats;
    }
}

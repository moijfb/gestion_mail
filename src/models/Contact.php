<?php

class Contact
{
    private $fileManager;
    private $logger;
    private $contactsFile = 'contacts.json';

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
        return uniqid('contact_', true);
    }

    /**
     * Valide une adresse email
     */
    public function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Récupère tous les contacts
     */
    public function getAll()
    {
        return $this->fileManager->readJson($this->contactsFile, []);
    }

    /**
     * Récupère un contact par ID
     */
    public function getById($id)
    {
        $contacts = $this->getAll();
        foreach ($contacts as $contact) {
            if ($contact['id'] === $id) {
                return $contact;
            }
        }
        return null;
    }

    /**
     * Récupère un contact par email
     */
    public function getByEmail($email)
    {
        $contacts = $this->getAll();
        foreach ($contacts as $contact) {
            if (strtolower($contact['email']) === strtolower($email)) {
                return $contact;
            }
        }
        return null;
    }

    /**
     * Crée un nouveau contact
     */
    public function create($data)
    {
        // Validation
        if (empty($data['email']) || !$this->validateEmail($data['email'])) {
            throw new Exception("Adresse email invalide");
        }

        // Vérifier si l'email existe déjà
        if ($this->getByEmail($data['email'])) {
            throw new Exception("Un contact avec cet email existe déjà");
        }

        $contacts = $this->getAll();

        $contact = [
            'id' => $this->generateId(),
            'email' => strtolower(trim($data['email'])),
            'nom' => $data['nom'] ?? '',
            'prenom' => $data['prenom'] ?? '',
            'societe' => $data['societe'] ?? '',
            'telephone' => $data['telephone'] ?? '',
            'tags' => $data['tags'] ?? [],
            'groupes' => $data['groupes'] ?? [],
            'statut' => $data['statut'] ?? 'active',
            'date_added' => date('Y-m-d H:i:s'),
            'date_modified' => date('Y-m-d H:i:s'),
            'notes' => $data['notes'] ?? ''
        ];

        $contacts[] = $contact;
        $this->fileManager->writeJson($this->contactsFile, $contacts);

        $this->logger->audit('CREATE', 'contact', $contact['id'], ['email' => $contact['email']]);

        return $contact;
    }

    /**
     * Met à jour un contact
     */
    public function update($id, $data)
    {
        $contacts = $this->getAll();
        $found = false;

        foreach ($contacts as $index => $contact) {
            if ($contact['id'] === $id) {
                // Validation de l'email si fourni
                if (isset($data['email'])) {
                    if (!$this->validateEmail($data['email'])) {
                        throw new Exception("Adresse email invalide");
                    }

                    // Vérifier les doublons
                    $existing = $this->getByEmail($data['email']);
                    if ($existing && $existing['id'] !== $id) {
                        throw new Exception("Un contact avec cet email existe déjà");
                    }
                }

                // Mettre à jour les champs
                $contacts[$index] = array_merge($contact, $data);
                $contacts[$index]['date_modified'] = date('Y-m-d H:i:s');

                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Exception("Contact non trouvé");
        }

        $this->fileManager->writeJson($this->contactsFile, $contacts);
        $this->logger->audit('UPDATE', 'contact', $id, $data);

        return $contacts[$index];
    }

    /**
     * Supprime un contact
     */
    public function delete($id)
    {
        $contacts = $this->getAll();
        $filtered = array_filter($contacts, function($contact) use ($id) {
            return $contact['id'] !== $id;
        });

        if (count($filtered) === count($contacts)) {
            throw new Exception("Contact non trouvé");
        }

        $this->fileManager->writeJson($this->contactsFile, array_values($filtered));
        $this->logger->audit('DELETE', 'contact', $id);

        return true;
    }

    /**
     * Supprime plusieurs contacts
     */
    public function deleteMultiple($ids)
    {
        $contacts = $this->getAll();
        $filtered = array_filter($contacts, function($contact) use ($ids) {
            return !in_array($contact['id'], $ids);
        });

        $deletedCount = count($contacts) - count($filtered);

        $this->fileManager->writeJson($this->contactsFile, array_values($filtered));
        $this->logger->audit('DELETE_MULTIPLE', 'contact', 'multiple', ['count' => $deletedCount]);

        return $deletedCount;
    }

    /**
     * Recherche des contacts
     */
    public function search($filters = [])
    {
        $contacts = $this->getAll();

        // Filtrer par texte libre
        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $contacts = array_filter($contacts, function($contact) use ($search) {
                return stripos($contact['email'], $search) !== false ||
                       stripos($contact['nom'], $search) !== false ||
                       stripos($contact['prenom'], $search) !== false ||
                       stripos($contact['societe'], $search) !== false ||
                       stripos($contact['notes'], $search) !== false;
            });
        }

        // Filtrer par groupe
        if (!empty($filters['groupe'])) {
            $contacts = array_filter($contacts, function($contact) use ($filters) {
                return in_array($filters['groupe'], $contact['groupes']);
            });
        }

        // Filtrer par tag
        if (!empty($filters['tag'])) {
            $contacts = array_filter($contacts, function($contact) use ($filters) {
                return in_array($filters['tag'], $contact['tags']);
            });
        }

        // Filtrer par statut
        if (!empty($filters['statut'])) {
            $contacts = array_filter($contacts, function($contact) use ($filters) {
                return $contact['statut'] === $filters['statut'];
            });
        }

        // Filtrer par domaine
        if (!empty($filters['domaine'])) {
            $contacts = array_filter($contacts, function($contact) use ($filters) {
                $domain = substr(strrchr($contact['email'], "@"), 1);
                return stripos($domain, $filters['domaine']) !== false;
            });
        }

        // Filtrer par date
        if (!empty($filters['date_from'])) {
            $contacts = array_filter($contacts, function($contact) use ($filters) {
                return $contact['date_added'] >= $filters['date_from'];
            });
        }

        if (!empty($filters['date_to'])) {
            $contacts = array_filter($contacts, function($contact) use ($filters) {
                return $contact['date_added'] <= $filters['date_to'];
            });
        }

        return array_values($contacts);
    }

    /**
     * Ajoute un contact à un groupe
     */
    public function addToGroup($contactId, $groupId)
    {
        $contact = $this->getById($contactId);
        if (!$contact) {
            throw new Exception("Contact non trouvé");
        }

        if (!in_array($groupId, $contact['groupes'])) {
            $contact['groupes'][] = $groupId;
            $this->update($contactId, ['groupes' => $contact['groupes']]);
        }

        return true;
    }

    /**
     * Retire un contact d'un groupe
     */
    public function removeFromGroup($contactId, $groupId)
    {
        $contact = $this->getById($contactId);
        if (!$contact) {
            throw new Exception("Contact non trouvé");
        }

        $contact['groupes'] = array_values(array_filter($contact['groupes'], function($g) use ($groupId) {
            return $g !== $groupId;
        }));

        $this->update($contactId, ['groupes' => $contact['groupes']]);

        return true;
    }

    /**
     * Ajoute un tag à un contact
     */
    public function addTag($contactId, $tag)
    {
        $contact = $this->getById($contactId);
        if (!$contact) {
            throw new Exception("Contact non trouvé");
        }

        if (!in_array($tag, $contact['tags'])) {
            $contact['tags'][] = $tag;
            $this->update($contactId, ['tags' => $contact['tags']]);
        }

        return true;
    }

    /**
     * Retire un tag d'un contact
     */
    public function removeTag($contactId, $tag)
    {
        $contact = $this->getById($contactId);
        if (!$contact) {
            throw new Exception("Contact non trouvé");
        }

        $contact['tags'] = array_values(array_filter($contact['tags'], function($t) use ($tag) {
            return $t !== $tag;
        }));

        $this->update($contactId, ['tags' => $contact['tags']]);

        return true;
    }

    /**
     * Obtient les statistiques sur les contacts
     */
    public function getStats()
    {
        $contacts = $this->getAll();

        $stats = [
            'total' => count($contacts),
            'active' => 0,
            'unsubscribed' => 0,
            'pending' => 0,
            'by_domain' => [],
            'by_date' => []
        ];

        foreach ($contacts as $contact) {
            // Par statut
            if ($contact['statut'] === 'active') {
                $stats['active']++;
            } elseif ($contact['statut'] === 'unsubscribed') {
                $stats['unsubscribed']++;
            } elseif ($contact['statut'] === 'pending') {
                $stats['pending']++;
            }

            // Par domaine
            $domain = substr(strrchr($contact['email'], "@"), 1);
            if (!isset($stats['by_domain'][$domain])) {
                $stats['by_domain'][$domain] = 0;
            }
            $stats['by_domain'][$domain]++;

            // Par date
            $date = substr($contact['date_added'], 0, 10);
            if (!isset($stats['by_date'][$date])) {
                $stats['by_date'][$date] = 0;
            }
            $stats['by_date'][$date]++;
        }

        // Trier les domaines par nombre de contacts
        arsort($stats['by_domain']);
        $stats['by_domain'] = array_slice($stats['by_domain'], 0, 20, true);

        return $stats;
    }

    /**
     * Détecte les doublons
     */
    public function findDuplicates()
    {
        $contacts = $this->getAll();
        $emails = [];
        $duplicates = [];

        foreach ($contacts as $contact) {
            $email = strtolower($contact['email']);
            if (isset($emails[$email])) {
                $duplicates[$email][] = $contact;
            } else {
                $emails[$email] = [$contact];
            }
        }

        return array_filter($emails, function($group) {
            return count($group) > 1;
        });
    }
}

<?php

require_once BASE_PATH . '/src/controllers/BaseController.php';

class ContactController extends BaseController
{
    private $contactModel;
    private $unsubscribedModel;

    public function __construct()
    {
        parent::__construct();
        $this->contactModel = new Contact();
        $this->unsubscribedModel = new Unsubscribed();
    }

    public function handle($uri, $method)
    {
        // API endpoints
        if (strpos($uri, '/contacts/api/') === 0) {
            $this->handleApi($uri, $method);
            return;
        }

        // Actions
        if (strpos($uri, '/contacts/create') === 0) {
            if ($method === 'POST') {
                $this->createContact();
            } else {
                $this->showCreateForm();
            }
        } elseif (preg_match('#^/contacts/edit/(.+)$#', $uri, $matches)) {
            if ($method === 'POST') {
                $this->updateContact($matches[1]);
            } else {
                $this->showEditForm($matches[1]);
            }
        } elseif (preg_match('#^/contacts/delete/(.+)$#', $uri, $matches)) {
            $this->deleteContact($matches[1]);
        } else {
            $this->listContacts();
        }
    }

    /**
     * Gère les requêtes API
     */
    private function handleApi($uri, $method)
    {
        if ($method !== 'POST') {
            $this->json(['error' => 'Méthode non autorisée'], 405);
        }

        $this->verifyCsrf();
        $data = $this->getPostData();

        if (strpos($uri, '/contacts/api/search') === 0) {
            $contacts = $this->contactModel->search($data);
            $this->json(['contacts' => $contacts]);
        } elseif (strpos($uri, '/contacts/api/delete-multiple') === 0) {
            $ids = $data['ids'] ?? [];
            $count = $this->contactModel->deleteMultiple($ids);
            $this->json(['success' => true, 'count' => $count]);
        } elseif (strpos($uri, '/contacts/api/add-tag') === 0) {
            $this->contactModel->addTag($data['contact_id'], $data['tag']);
            $this->json(['success' => true]);
        } elseif (strpos($uri, '/contacts/api/remove-tag') === 0) {
            $this->contactModel->removeTag($data['contact_id'], $data['tag']);
            $this->json(['success' => true]);
        }
    }

    /**
     * Liste tous les contacts
     */
    private function listContacts()
    {
        $filters = $_GET;
        $contacts = $this->contactModel->search($filters);

        $groupModel = new Group();
        $groups = $groupModel->getAll();

        // Récupérer tous les tags uniques
        $allTags = [];
        foreach ($this->contactModel->getAll() as $contact) {
            $allTags = array_merge($allTags, $contact['tags']);
        }
        $allTags = array_unique($allTags);
        sort($allTags);

        $data = [
            'contacts' => $contacts,
            'groups' => $groups,
            'all_tags' => $allTags,
            'filters' => $filters,
            'page_title' => 'Contacts'
        ];

        $this->renderWithLayout('contacts/list', $data);
    }

    /**
     * Affiche le formulaire de création
     */
    private function showCreateForm()
    {
        $groupModel = new Group();
        $groups = $groupModel->getAll();

        $data = [
            'groups' => $groups,
            'page_title' => 'Nouveau contact'
        ];

        $this->renderWithLayout('contacts/create', $data);
    }

    /**
     * Crée un nouveau contact
     */
    private function createContact()
    {
        $this->verifyCsrf();
        $postData = $this->getPostData();

        try {
            // Vérifier si l'email est dans la liste des désinscrits
            $isUnsubscribed = $this->unsubscribedModel->isUnsubscribed($postData['email']);

            if ($isUnsubscribed) {
                $unsubInfo = $this->unsubscribedModel->getByEmail($postData['email']);
                $this->setFlash('warning',
                    'Attention: Cette adresse email est dans la liste des désinscrits depuis le ' .
                    date('d/m/Y', strtotime($unsubInfo['date'])) .
                    '. Ne pas réactiver sans consentement explicite.');
            }

            // Traiter les tags
            if (isset($postData['tags']) && is_string($postData['tags'])) {
                $postData['tags'] = array_filter(array_map('trim', explode(',', $postData['tags'])));
            }

            // Traiter les groupes
            if (isset($postData['groupes']) && !is_array($postData['groupes'])) {
                $postData['groupes'] = [$postData['groupes']];
            }

            $contact = $this->contactModel->create($postData);

            $this->setFlash('success', 'Contact créé avec succès');
            $this->redirect('/contacts');

        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/contacts/create');
        }
    }

    /**
     * Affiche le formulaire d'édition
     */
    private function showEditForm($id)
    {
        $contact = $this->contactModel->getById($id);

        if (!$contact) {
            $this->setFlash('error', 'Contact non trouvé');
            $this->redirect('/contacts');
        }

        $groupModel = new Group();
        $groups = $groupModel->getAll();

        $data = [
            'contact' => $contact,
            'groups' => $groups,
            'page_title' => 'Modifier contact'
        ];

        $this->renderWithLayout('contacts/edit', $data);
    }

    /**
     * Met à jour un contact
     */
    private function updateContact($id)
    {
        $this->verifyCsrf();
        $postData = $this->getPostData();

        try {
            // Traiter les tags
            if (isset($postData['tags']) && is_string($postData['tags'])) {
                $postData['tags'] = array_filter(array_map('trim', explode(',', $postData['tags'])));
            }

            // Traiter les groupes
            if (isset($postData['groupes']) && !is_array($postData['groupes'])) {
                $postData['groupes'] = [$postData['groupes']];
            }

            $this->contactModel->update($id, $postData);

            $this->setFlash('success', 'Contact mis à jour avec succès');
            $this->redirect('/contacts');

        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/contacts/edit/' . $id);
        }
    }

    /**
     * Supprime un contact
     */
    private function deleteContact($id)
    {
        $this->verifyCsrf();

        try {
            $this->contactModel->delete($id);
            $this->setFlash('success', 'Contact supprimé avec succès');
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('/contacts');
    }
}

<?php

require_once BASE_PATH . '/src/controllers/BaseController.php';

class GroupController extends BaseController
{
    private $groupModel;

    public function __construct()
    {
        parent::__construct();
        $this->groupModel = new Group();
    }

    public function handle($uri, $method)
    {
        if (strpos($uri, '/groups/create') === 0) {
            if ($method === 'POST') {
                $this->createGroup();
            } else {
                $this->showCreateForm();
            }
        } elseif (preg_match('#^/groups/edit/(.+)$#', $uri, $matches)) {
            if ($method === 'POST') {
                $this->updateGroup($matches[1]);
            } else {
                $this->showEditForm($matches[1]);
            }
        } elseif (preg_match('#^/groups/delete/(.+)$#', $uri, $matches)) {
            $this->deleteGroup($matches[1]);
        } elseif (preg_match('#^/groups/view/(.+)$#', $uri, $matches)) {
            $this->viewGroup($matches[1]);
        } else {
            $this->listGroups();
        }
    }

    /**
     * Liste tous les groupes
     */
    private function listGroups()
    {
        $groups = $this->groupModel->getAll();
        $stats = $this->groupModel->getStats();

        $data = [
            'groups' => $groups,
            'stats' => $stats,
            'page_title' => 'Groupes'
        ];

        $this->renderWithLayout('groups/list', $data);
    }

    /**
     * Affiche le formulaire de création
     */
    private function showCreateForm()
    {
        $data = [
            'page_title' => 'Nouveau groupe'
        ];

        $this->renderWithLayout('groups/create', $data);
    }

    /**
     * Crée un nouveau groupe
     */
    private function createGroup()
    {
        $this->verifyCsrf();
        $postData = $this->getPostData();

        try {
            $this->groupModel->create($postData['name'], $postData['description'] ?? '');
            $this->setFlash('success', 'Groupe créé avec succès');
            $this->redirect('/groups');
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/groups/create');
        }
    }

    /**
     * Affiche le formulaire d'édition
     */
    private function showEditForm($id)
    {
        $group = $this->groupModel->getById($id);

        if (!$group) {
            $this->setFlash('error', 'Groupe non trouvé');
            $this->redirect('/groups');
        }

        $data = [
            'group' => $group,
            'page_title' => 'Modifier groupe'
        ];

        $this->renderWithLayout('groups/edit', $data);
    }

    /**
     * Met à jour un groupe
     */
    private function updateGroup($id)
    {
        $this->verifyCsrf();
        $postData = $this->getPostData();

        try {
            $this->groupModel->update($id, [
                'name' => $postData['name'],
                'description' => $postData['description'] ?? ''
            ]);

            $this->setFlash('success', 'Groupe mis à jour avec succès');
            $this->redirect('/groups');
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/groups/edit/' . $id);
        }
    }

    /**
     * Supprime un groupe
     */
    private function deleteGroup($id)
    {
        $this->verifyCsrf();

        try {
            $this->groupModel->delete($id);
            $this->setFlash('success', 'Groupe supprimé avec succès');
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('/groups');
    }

    /**
     * Affiche les détails d'un groupe
     */
    private function viewGroup($id)
    {
        $group = $this->groupModel->getById($id);

        if (!$group) {
            $this->setFlash('error', 'Groupe non trouvé');
            $this->redirect('/groups');
        }

        $contacts = $this->groupModel->getContacts($id);

        $data = [
            'group' => $group,
            'contacts' => $contacts,
            'page_title' => 'Groupe: ' . $group['name']
        ];

        $this->renderWithLayout('groups/view', $data);
    }
}

// Application JavaScript pour la gestion d'emails

document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('.flash');
    flashMessages.forEach(function(flash) {
        setTimeout(function() {
            flash.style.transition = 'opacity 0.5s';
            flash.style.opacity = '0';
            setTimeout(function() {
                flash.remove();
            }, 500);
        }, 5000);
    });

    // Confirmation pour les suppressions
    const deleteLinks = document.querySelectorAll('a[href*="delete"]');
    deleteLinks.forEach(function(link) {
        if (!link.hasAttribute('onclick')) {
            link.addEventListener('click', function(e) {
                if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                    e.preventDefault();
                }
            });
        }
    });

    // Copier dans le presse-papiers
    const copyButtons = document.querySelectorAll('[data-copy]');
    copyButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const text = this.getAttribute('data-copy');
            copyToClipboard(text);

            const originalText = this.textContent;
            this.textContent = 'Copié !';
            setTimeout(() => {
                this.textContent = originalText;
            }, 2000);
        });
    });

    // Fonction de copie dans le presse-papiers
    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text);
        } else {
            // Fallback pour les navigateurs plus anciens
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }
    }

    // Validation de formulaire pour le changement de mot de passe
    const passwordForm = document.querySelector('form[action*="password"]');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');

            if (newPassword && confirmPassword) {
                if (newPassword.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Les mots de passe ne correspondent pas');
                    return false;
                }

                if (newPassword.value.length < 6) {
                    e.preventDefault();
                    alert('Le mot de passe doit contenir au moins 6 caractères');
                    return false;
                }
            }
        });
    }

    // Sélection multiple pour les contacts
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.contact-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Actions groupées sur les contacts
    const bulkActionButton = document.getElementById('bulk-action');
    if (bulkActionButton) {
        bulkActionButton.addEventListener('click', function() {
            const selectedContacts = document.querySelectorAll('.contact-checkbox:checked');
            const contactIds = Array.from(selectedContacts).map(cb => cb.value);

            if (contactIds.length === 0) {
                alert('Veuillez sélectionner au moins un contact');
                return;
            }

            const action = document.getElementById('bulk-action-select').value;

            if (action === 'delete') {
                if (confirm(`Supprimer ${contactIds.length} contact(s) ?`)) {
                    performBulkAction('delete', contactIds);
                }
            } else if (action === 'export') {
                performBulkAction('export', contactIds);
            }
        });
    }

    function performBulkAction(action, contactIds) {
        // Cette fonction peut être étendue pour gérer les actions groupées
        console.log('Bulk action:', action, contactIds);
    }

    // Prévisualisation du fichier CSV avant import
    const csvFileInput = document.getElementById('csv_file');
    if (csvFileInput) {
        csvFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const content = e.target.result;
                    const lines = content.split('\n').slice(0, 5);
                    console.log('CSV Preview:', lines);
                };
                reader.readAsText(file);
            }
        });
    }

    // Recherche en temps réel (debounced)
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Auto-submit du formulaire de recherche après 500ms
                const form = this.closest('form');
                if (form && this.value.length > 2) {
                    // form.submit(); // Désactivé pour éviter trop de requêtes
                }
            }, 500);
        });
    }
});

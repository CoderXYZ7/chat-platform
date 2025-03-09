document.addEventListener('DOMContentLoaded', function() {
    // Add User Form Toggle
    const showAddUserBtn = document.getElementById('showAddUserForm');
    const cancelAddUserBtn = document.getElementById('cancelAddUser');
    const addUserForm = document.getElementById('addUserForm');
    
    if (showAddUserBtn && cancelAddUserBtn && addUserForm) {
        showAddUserBtn.addEventListener('click', function() {
            addUserForm.style.display = 'block';
        });
        
        cancelAddUserBtn.addEventListener('click', function() {
            addUserForm.style.display = 'none';
        });
    }
    
    // Add Chatroom Form Toggle
    const showAddChatroomBtn = document.getElementById('showAddChatroomForm');
    const cancelAddChatroomBtn = document.getElementById('cancelAddChatroom');
    const addChatroomForm = document.getElementById('addChatroomForm');
    
    if (showAddChatroomBtn && cancelAddChatroomBtn && addChatroomForm) {
        showAddChatroomBtn.addEventListener('click', function() {
            addChatroomForm.style.display = 'block';
        });
        
        cancelAddChatroomBtn.addEventListener('click', function() {
            addChatroomForm.style.display = 'none';
        });
    }
    
    // Edit User Modal
    const editUserModal = document.getElementById('editUserModal');
    const editUserBtns = document.querySelectorAll('.edit-user');
    const closeUserModal = editUserModal?.querySelector('.close');
    
    if (editUserModal && editUserBtns.length > 0 && closeUserModal) {
        editUserBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const username = this.getAttribute('data-username');
                const email = this.getAttribute('data-email');
                const isAdmin = this.getAttribute('data-is-admin') === '1';
                
                document.getElementById('edit-user-id').value = userId;
                document.getElementById('edit-username').value = username;
                document.getElementById('edit-email').value = email;
                document.getElementById('edit-password').value = '';
                document.getElementById('edit-is-admin').checked = isAdmin;
                
                editUserModal.style.display = 'block';
            });
        });
        
        closeUserModal.addEventListener('click', function() {
            editUserModal.style.display = 'none';
        });
    }
    
    // Edit Chatroom Modal
    const editChatroomModal = document.getElementById('editChatroomModal');
    const editChatroomBtns = document.querySelectorAll('.edit-chatroom');
    const closeChatroomModal = editChatroomModal?.querySelector('.close');
    
    if (editChatroomModal && editChatroomBtns.length > 0 && closeChatroomModal) {
        editChatroomBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const chatroomId = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const description = this.getAttribute('data-description');
                
                document.getElementById('edit-chatroom-id').value = chatroomId;
                document.getElementById('edit-name').value = name;
                document.getElementById('edit-description').value = description;
                
                editChatroomModal.style.display = 'block';
            });
        });
        
        closeChatroomModal.addEventListener('click', function() {
            editChatroomModal.style.display = 'none';
        });
    }
    
    // Delete confirmation
    const deleteForms = document.querySelectorAll('.delete-form');
    
    if (deleteForms.length > 0) {
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
    }
    
    // Close modals when clicking outside of them
    window.addEventListener('click', function(e) {
        if (editUserModal && e.target === editUserModal) {
            editUserModal.style.display = 'none';
        }
        
        if (editChatroomModal && e.target === editChatroomModal) {
            editChatroomModal.style.display = 'none';
        }
    });
});
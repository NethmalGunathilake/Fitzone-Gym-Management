$(document).ready(function() {
    // Initialize DataTable
    $('#usersTable').DataTable({
        responsive: true,
        searching: true
    });
    
    // Modal handling
    const addUserModal = $('#addUserModal');
    const editUserModal = $('#editUserModal');
    const deleteModal = $('#deleteModal');
    
    // Open Add User Modal
    $('#addUserBtn').click(function() {
        addUserModal.css('display', 'flex');
    });
    
    // Open Edit User Modal
    $(document).on('click', '.edit-user-btn', function() {
        const userId = $(this).data('id');
        const firstName = $(this).data('fname');
        const lastName = $(this).data('lname');
        const email = $(this).data('email');
        const userType = $(this).data('type');
        
        $('#editUserId').val(userId);
        $('#editFirstName').val(firstName);
        $('#editLastName').val(lastName);
        $('#editEmail').val(email);
        $('#editUserType').val(userType);
        
        editUserModal.css('display', 'flex');
    });
    
    // Open Delete Confirmation Modal
    $(document).on('click', '.delete-user-btn', function() {
        const userId = $(this).data('id');
        $('#deleteUserId').val(userId);
        deleteModal.css('display', 'flex');
    });
    
    // Close Modals
    $('.close-modal, .cancel-btn').click(function() {
        $('.modal').css('display', 'none');
    });
    
    // Close modal when clicking outside
    $(window).click(function(event) {
        if ($(event.target).is('.modal')) {
            $('.modal').css('display', 'none');
        }
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
<div class="modal" id="editUserModal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <div class="modal-header">
            <h3>Edit Member</h3>
        </div>
        <form id="editUserForm" method="post" action="manage_users.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="user_id" id="editUserId">
            <div class="modal-body">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" id="editFirstName" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" id="editLastName" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="editEmail" required>
                </div>
                <div class="form-group">
                    <label>User Type</label>
                    <select name="user_type" id="editUserType" required>
                        <option value="user">Member</option>
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel-btn">Cancel</button>
                <button type="submit" class="btn">Update Member</button>
            </div>
        </form>
    </div>
</div>
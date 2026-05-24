<div class="modal" id="deleteModal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <div class="modal-header">
            <h3>Confirm Delete</h3>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this member? This action cannot be undone.</p>
        </div>
        <form id="deleteForm" method="post" action="manage_users.php">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="user_id" id="deleteUserId">
            <div class="modal-footer">
                <button type="button" class="btn cancel-btn">Cancel</button>
                <button type="submit" class="btn delete-btn">Delete</button>
            </div>
        </form>
    </div>
</div>
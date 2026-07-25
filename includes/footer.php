  </div><!-- .page-content -->
    </div><!-- .main-content -->
</div><!-- .app-layout -->

<div id="logoutModal" class="modal-overlay" onclick="if(event.target === this) hideLogoutModal()">
    <div class="modal-box">
        <h3>Log Out?</h3>
        <p>Are you sure you want to log out of your account?</p>
        <div class="modal-actions">
            <button class="btn btn-small" onclick="hideLogoutModal()">Cancel</button>
            <a href="/task_manager/auth/logout.php" class="btn btn-danger">Log Out</a>
        </div>
    </div>
</div>
<div id="deleteModal" class="modal-overlay" onclick="if(event.target === this) hideDeleteModal()">
    <div class="modal-box">
        <h3>Delete Task?</h3>
        <p id="deleteModalText">Are you sure you want to delete this task?</p>
        <div class="modal-actions">
            <button class="btn btn-small" onclick="hideDeleteModal()">Cancel</button>
            <a id="deleteModalConfirmBtn" href="#" class="btn btn-danger">Delete</a>
        </div>
    </div>
</div>

<div id="toast" class="toast"></div>

<script src="/task_manager/assets/js/script.js"></script>
</body>
</html>
<?php
/**
 * views/admin/categories.php
 * Interface for Admin to manage job categories.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/AdminController.php';

Session::init();
Session::checkRole('admin');

$controller = new AdminController();

// Handle Actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $controller->addCategory();
        } elseif ($_POST['action'] == 'edit') {
            $controller->editCategory();
        }
    }
} elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $controller->deleteCategory(intval($_GET['id']));
}

$categories = $controller->getCategories();

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>Manage Categories</h1>
        <a href="dashboard.php" class="btn-primary" style="background: #35424a;">Back to Dashboard</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; gap: 30px;">
        <!-- Add Category Form -->
        <div style="flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid #ddd; height: fit-content;">
            <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Add New Category</h3>
            <form action="categories.php" method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Category Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Graphic Design" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Description</label>
                    <textarea name="description" class="form-control" rows="3" required placeholder="Brief description of the category..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"></textarea>
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; padding: 10px; border: none; cursor: pointer; background: #28a745;">Add Category</button>
            </form>
        </div>

        <!-- List Categories -->
        <div style="flex: 2; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid #ddd;">
            <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Existing Categories</h3>
            
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #f8f9fa; text-align: left;">
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">ID</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Name</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Description</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd; width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?= $cat['id'] ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;"><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd; font-size: 14px; color: #666;"><?= htmlspecialchars($cat['description']) ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                <button onclick='openEditModal(<?= json_encode($cat) ?>)' style="background: none; border: none; color: #007bff; cursor: pointer; text-decoration: underline; padding: 0; margin-right: 10px;">Edit</button>
                                <a href="categories.php?action=delete&id=<?= $cat['id'] ?>" style="color: #dc3545; text-decoration: underline;" onclick="return confirm('Are you sure? This might affect jobs linked to this category.');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 30px; border-radius: 8px; width: 500px; max-width: 90%;">
        <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Edit Category</h3>
        
        <form action="categories.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Category Name</label>
                <input type="text" name="name" id="edit_name" class="form-control" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Description</label>
                <textarea name="description" id="edit_description" class="form-control" rows="4" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn-primary" style="flex: 1; padding: 10px; border: none; cursor: pointer;">Save Changes</button>
                <button type="button" onclick="document.getElementById('editModal').style.display = 'none'" style="flex: 1; padding: 10px; border: 1px solid #ccc; background: #f4f4f4; border-radius: 4px; cursor: pointer;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(category) {
    document.getElementById('edit_id').value = category.id;
    document.getElementById('edit_name').value = category.name;
    document.getElementById('edit_description').value = category.description;
    document.getElementById('editModal').style.display = 'flex';
}
</script>

<?php include '../partials/footer.php'; ?>
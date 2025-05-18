<?php
require_once 'config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_task'])) {
        $task = trim($_POST['task']);
        if (!empty($task)) {
            $stmt = $conn->prepare("INSERT INTO todos (task) VALUES (?)");
            $stmt->execute([$task]);
        }
    } elseif (isset($_POST['complete_task'])) {
        $id = $_POST['task_id'];
        $stmt = $conn->prepare("UPDATE todos SET status = 'completed' WHERE id = ?");
        $stmt->execute([$id]);
    } elseif (isset($_POST['delete_task'])) {
        $id = $_POST['task_id'];
        $stmt = $conn->prepare("DELETE FROM todos WHERE id = ?");
        $stmt->execute([$id]);
    } elseif (isset($_POST['edit_task'])) {
        $id = $_POST['task_id'];
        $task = trim($_POST['task']);
        if (!empty($task)) {
            $stmt = $conn->prepare("UPDATE todos SET task = ? WHERE id = ?");
            $stmt->execute([$task, $id]);
        }
    }
    header("Location: index.php");
    exit();
}

// Fetch all todos
$stmt = $conn->query("SELECT * FROM todos ORDER BY created_at DESC");
$todos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h1 class="h4 mb-0">To Do List</h1>
                    </div>
                    <div class="card-body">
                        <!-- Add Task Form -->
                        <form method="POST" class="mb-4">
                            <div class="input-group">
                                <input type="text" name="task" class="form-control" placeholder="Enter a new task" required>
                                <button type="submit" name="add_task" class="btn btn-primary">Add Task</button>
                            </div>
                        </form>

                        <!-- Todo List -->
                        <ul class="list-group">
                            <?php foreach ($todos as $todo): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="<?php echo $todo['status'] === 'completed' ? 'text-decoration-line-through text-muted' : ''; ?>">
                                        <?php echo htmlspecialchars($todo['task']); ?>
                                    </span>
                                    <div class="btn-group">
                                        <?php if ($todo['status'] === 'pending'): ?>
                                            <form method="POST" class="me-2">
                                                <input type="hidden" name="task_id" value="<?php echo $todo['id']; ?>">
                                                <button type="submit" name="complete_task" class="btn-custom btn-complete">
                                                    Complete
                                                </button>
                                            </form>
                                            <button type="button" class="btn-custom btn-edit me-2" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal<?php echo $todo['id']; ?>">
                                                Edit
                                            </button>
                                        <?php endif; ?>
                                        <form method="POST">
                                            <input type="hidden" name="task_id" value="<?php echo $todo['id']; ?>">
                                            <button type="submit" name="delete_task" class="btn-custom btn-delete">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </li>

                                <!-- Edit Modal for each todo -->
                                <div class="modal fade" id="editModal<?php echo $todo['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Task</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="task_id" value="<?php echo $todo['id']; ?>">
                                                    <div class="mb-3">
                                                        <label for="task<?php echo $todo['id']; ?>" class="form-label">Task</label>
                                                        <input type="text" class="form-control" id="task<?php echo $todo['id']; ?>" 
                                                            name="task" value="<?php echo htmlspecialchars($todo['task']); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn-custom btn-cancel" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" name="edit_task" class="btn-custom btn-save">Save changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($todos)): ?>
                                <li class="list-group-item text-center text-muted">
                                    No tasks yet. Add a new task above!
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
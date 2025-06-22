<?php
include('config.php');
session_start();

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];
$message = "";

// Add reminder
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $type = $_POST['type'];
    $reminder_datetime = $_POST['reminder_datetime'];

    $stmt = $conn->prepare("INSERT INTO reminders (user_id, type, reminder_datetime) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $type, $reminder_datetime);
    $stmt->execute();
    $message = $stmt->affected_rows > 0 ? "Reminder added." : "Failed to add reminder.";
    $stmt->close();
}

// Update reminder
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = intval($_POST['id']);
    $type = $_POST['type'];
    $reminder_datetime = $_POST['reminder_datetime'];
    $is_complete = isset($_POST['is_complete']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE reminders SET type = ?, reminder_datetime = ?, is_complete = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssiii", $type, $reminder_datetime, $is_complete, $id, $user_id);
    $stmt->execute();
    $message = $stmt->affected_rows >= 0 ? "Reminder updated." : "Failed to update reminder.";
    $stmt->close();
}

// Delete reminder
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM reminders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
    $message = $stmt->affected_rows > 0 ? "Reminder deleted." : "Failed to delete reminder.";
    $stmt->close();
}

// Fetch reminders
$stmt = $conn->prepare("SELECT * FROM reminders WHERE user_id = ? ORDER BY reminder_datetime");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Reminders</title>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./css/style.css" />

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-4">
    <h2>Manage Reminders</h2>
    <?php if (!empty($message)) echo "<div class='alert alert-info'>$message</div>"; ?>
    <button class="btn btn-success mb-3" data-toggle="modal" data-target="#reminderModal" onclick="showAddModal()">Add Reminder</button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Type</th>
                <th>Date & Time</th>
                <th>Complete?</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['type']); ?></td>
                <td><?php echo htmlspecialchars($row['reminder_datetime']); ?></td>
                <td><?php echo $row['is_complete'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#reminderModal" onclick='showEditModal(<?php echo json_encode($row); ?>)'>Edit</button>
                    <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this reminder?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="reminderModal" tabindex="-1" role="dialog" aria-labelledby="reminderModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reminderModalLabel">Add / Edit Reminder</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <input type="hidden" name="action" id="formAction" value="add">
          <input type="hidden" name="id" id="reminderId">
          <div class="form-group">
              <label>Type</label>
              <select name="type" id="reminderType" class="form-control" required>
                  <option value="water">Water</option>
                  <option value="exercise">Exercise</option>
                  <option value="sleep">Sleep</option>
                  <option value="mood">Mood</option>
                  <option value="general">General</option>
              </select>
          </div>
          <div class="form-group">
              <label>Date & Time</label>
              <input type="datetime-local" name="reminder_datetime" id="reminderDatetime" class="form-control" required>
          </div>
          <div class="form-group">
              <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="is_complete" id="isComplete">
                  <label class="form-check-label" for="isComplete">
                      Mark as Complete
                  </label>
              </div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save Reminder</button>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
function showAddModal() {
    $('#formAction').val('add');
    $('#reminderId').val('');
    $('#reminderType').val('water');
    $('#reminderDatetime').val('');
    $('#isComplete').prop('checked', false);
    $('#reminderModalLabel').text('Add Reminder');
}

function showEditModal(data) {
    $('#formAction').val('edit');
    $('#reminderId').val(data.id);
    $('#reminderType').val(data.type);
    $('#reminderDatetime').val(data.reminder_datetime.replace(' ', 'T'));
    $('#isComplete').prop('checked', data.is_complete == 1);
    $('#reminderModalLabel').text('Edit Reminder');
}
</script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

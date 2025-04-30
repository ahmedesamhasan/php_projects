<?php

/*************** 1) إعداد الاتصال ***************/
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = mysqli_connect('localhost', 'root', '', 'student');
if (!$conn) die('DB error: ' . mysqli_connect_error());
mysqli_set_charset($conn, 'utf8mb4'); // Unicode full support

/*************** 2) دوالّ CRUD ***************/
function insertRow($c, $id, $n, $a)
{
  $st = mysqli_prepare($c, "INSERT INTO student(id,name,address)VALUES(?,?,?)");
  mysqli_stmt_bind_param($st, 'iss', $id, $n, $a);
  mysqli_stmt_execute($st);
  mysqli_stmt_close($st);
}
function updateRow($c, $id, $n, $a)
{
  $st = mysqli_prepare($c, "UPDATE student SET name=?,address=? WHERE id=?");
  mysqli_stmt_bind_param($st, 'ssi', $n, $a, $id);
  mysqli_stmt_execute($st);
  mysqli_stmt_close($st);
}
function deleteRow($c, $id)
{
  $st = mysqli_prepare($c, "DELETE FROM student WHERE id=?");
  mysqli_stmt_bind_param($st, 'i', $id);
  mysqli_stmt_execute($st);
  mysqli_stmt_close($st);
}
function fetchOne($c, $id)
{
  $st = mysqli_prepare($c, "SELECT id,name,address FROM student WHERE id=?");
  mysqli_stmt_bind_param($st, 'i', $id);
  mysqli_stmt_execute($st);
  $r = mysqli_stmt_get_result($st);
  return mysqli_fetch_assoc($r) ?: ['id' => '', 'name' => '', 'address' => ''];
}

/*************** 3) معالجة POST (بدون CSRF) ***************/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id   = intval($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $addr = trim($_POST['address'] ?? '');

  if (isset($_POST['add']))      insertRow($conn, $id, $name, $addr);
  if (isset($_POST['update']))   updateRow($conn, $id, $name, $addr);
  if (isset($_POST['delete']))   deleteRow($conn, $id);

  header('Location: home.php', true, 303);
  exit;
}

/*************** 4) تحميل بيانات الـEdit (GET) ***************/
$current = ['id' => '', 'name' => '', 'address' => ''];
if (isset($_GET['edit'])) $current = fetchOne($conn, intval($_GET['edit']));

/*************** 5) قراءة كلّ الطلاب للجدول ***************/
$list = mysqli_query($conn, 'SELECT * FROM student ORDER BY id');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Student CRUD (No CSRF)</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="//cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
</head>

<body class="container py-4">
  <h2 class="mb-3">Student Control Panel (No CSRF)</h2>
  <!-- نموذج موحَّد للإضافة/التعديل/الحذف -->
  <form class="row g-3 mb-4" method="post">
    <div class="col-md-2">
      <label class="form-label">ID</label>
      <input type="number" name="id" class="form-control" value="<?= htmlspecialchars($current['id']) ?>" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Name</label>
      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($current['name']) ?>" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Address</label>
      <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($current['address']) ?>"
        required>
    </div>
    <div class="col-12">
      <button class="btn btn-success me-2" name="add">Add</button>
      <button class="btn btn-warning me-2" name="update">Update</button>
      <button class="btn btn-danger" name="delete">Delete</button>
    </div>
  </form>
  <!-- جدول -->
  <table id="tbl" class="table table-striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Address</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($list)): ?>
      <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['address']) ?></td>
        <td><a class="btn btn-sm btn-secondary" href="?edit=<?= $row['id'] ?>">Edit</a></td>
      </tr>
      <?php endwhile;
      mysqli_free_result($list);
      mysqli_close($conn); ?>
    </tbody>
  </table>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="//cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script>
  $(function() {
    $('#tbl').DataTable();
  });
  </script>
</body>

</html>
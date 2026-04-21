<?php
$required_role = 'admin';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$db = Database::getInstance()->getConnection();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_lecturer') {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/mailer.php';
        $full_name = sanitize($_POST['full_name'] ?? '');
        $email     = strtolower(trim($_POST['email'] ?? ''));
        $phone     = sanitize($_POST['phone'] ?? '');
        $address   = sanitize($_POST['address'] ?? '');
        $dept      = sanitize($_POST['department'] ?? '');

        $error_msg = '';
        if (!$full_name || !$email || !$phone || !$address) {
            $error_msg = 'All required fields must be filled.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_msg = 'Invalid email address.';
        } else {
            $exists = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $exists->execute([$email]);
            if ($exists->fetch()) {
                $error_msg = 'Email already exists.';
            }
        }

        if (!$error_msg) {
            $temp_password = 'BC@' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $hash          = password_hash($temp_password, PASSWORD_BCRYPT, ['cost' => 12]);

            $stmt = $db->prepare("INSERT INTO users (full_name, email, phone, address, password_hash, role, status, email_verified) VALUES (?, ?, ?, ?, ?, 'lecturer', 'active', 1)");
            $stmt->execute([$full_name, $email, $phone, $address, $hash]);
            $lecturer_id = (int)$db->lastInsertId();

            $db->prepare("INSERT INTO lecturer_profiles (user_id, department) VALUES (?, ?)")
               ->execute([$lecturer_id, $dept]);

            $db->prepare("INSERT INTO activity_logs (user_id, action, target_type, target_id, description, ip_address) VALUES (?, 'create_lecturer', 'user', ?, ?, ?)")
               ->execute([$_SESSION['user_id'], $lecturer_id, "Created lecturer: $full_name", $_SERVER['REMOTE_ADDR'] ?? '']);

            $_SESSION['new_lecturer_temp_pass'] = $temp_password;
            $success_msg = "Lecturer account created successfully for $full_name.";
        }
    }

    if ($action === 'unlock') {
        $id = (int)($_POST['user_id'] ?? 0);
        $db->prepare("UPDATE users SET status='active', login_attempts=0, locked_at=NULL WHERE id=?")->execute([$id]);
        $db->prepare("INSERT INTO activity_logs (user_id, action, target_type, target_id, description, ip_address) VALUES (?, 'unlock_account', 'user', ?, 'Unlocked account', ?)")
           ->execute([$_SESSION['user_id'], $id, $_SERVER['REMOTE_ADDR'] ?? '']);
        $success_msg = 'Account unlocked successfully.';
    }

    if ($action === 'toggle_status') {
        $id     = (int)($_POST['user_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['active','inactive'])) {
            $db->prepare("UPDATE users SET status=?, updated_at=NOW() WHERE id=?")->execute([$status, $id]);
            if ($status === 'inactive') {
                $db->prepare("DELETE FROM user_sessions WHERE user_id=?")->execute([$id]);
            }
            $db->prepare("INSERT INTO activity_logs (user_id, action, target_type, target_id, description, ip_address) VALUES (?, 'toggle_status', 'user', ?, ?, ?)")
               ->execute([$_SESSION['user_id'], $id, "Set status to $status", $_SERVER['REMOTE_ADDR'] ?? '']);
            $success_msg = 'User status updated.';
        }
    }

    if ($action === 'reset_password') {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/mailer.php';
        $id   = (int)($_POST['user_id'] ?? 0);
        $stmt = $db->prepare("SELECT full_name, email FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $target = $stmt->fetch();
        if ($target) {
            $temp_password = 'BC@' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $hash          = password_hash($temp_password, PASSWORD_BCRYPT, ['cost' => 12]);
            $db->prepare("UPDATE users SET password_hash=?, updated_at=NOW() WHERE id=?")->execute([$hash, $id]);
            $db->prepare("DELETE FROM user_sessions WHERE user_id=?")->execute([$id]);
            send_temp_password_email($target['email'], $target['full_name'], $temp_password);
            $success_msg = 'New password sent to user email.';
        }
    }
}

// Filters
$search  = sanitize($_GET['search'] ?? '');
$role    = sanitize($_GET['role']   ?? '');
$status  = sanitize($_GET['status'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 20;
$offset  = ($page - 1) * $limit;

$where  = ['1=1'];
$params = [];
if ($role)   { $where[] = 'u.role = ?';   $params[] = $role; }
if ($status) { $where[] = 'u.status = ?'; $params[] = $status; }
if ($search) {
    $where[]  = '(u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
    $like     = "%$search%";
    $params   = array_merge($params, [$like, $like, $like]);
}
$where_sql = implode(' AND ', $where);

$count_stmt = $db->prepare("SELECT COUNT(*) FROM users u WHERE $where_sql");
$count_stmt->execute($params);
$total       = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total / $limit));

$stmt = $db->prepare("
    SELECT u.id, u.full_name, u.email, u.phone, u.role, u.status,
           u.email_verified, u.points, u.last_login, u.created_at,
           lp.department
    FROM users u
    LEFT JOIN lecturer_profiles lp ON lp.user_id = u.id
    WHERE $where_sql
    ORDER BY u.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$users = $stmt->fetchAll();

// Quick stats
$total_students  = $db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$total_lecturers = $db->query("SELECT COUNT(*) FROM users WHERE role='lecturer'")->fetchColumn();
$total_admins    = $db->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
$locked_accounts = $db->query("SELECT COUNT(*) FROM users WHERE status='locked'")->fetchColumn();

$page_title = 'User Management';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>

<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-admin.php'; ?>

  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">

<!-- Navbar -->
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-admin.php'; ?>

    <main class="flex-1 p-6">

      <!-- Welcome -->
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6 flex items-center justify-between">
        <div>
          <h2 class="text-white text-xl font-bold">User Management</h2>
          <p class="text-indigo-100 text-sm mt-0.5">Manage all students, lecturers and admins</p>
        </div>
        <button onclick="document.getElementById('create-lecturer-modal').classList.remove('hidden')"
          class="bg-white text-indigo-600 px-4 py-2 rounded-xl font-semibold text-sm hover:bg-indigo-50 transition-colors flex items-center gap-2">
          <i data-lucide="user-plus" class="w-4 h-4"></i> Add Lecturer
        </button>
      </div>

      <?php if (!empty($success_msg)): ?>
      <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl flex gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5"></i>
        <div>
          <p class="text-sm text-green-700 font-medium"><?= htmlspecialchars($success_msg) ?></p>
          <?php if (!empty($_SESSION['new_lecturer_temp_pass'])): ?>
          <div class="mt-2 p-3 bg-white border border-green-200 rounded-lg">
            <p class="text-xs text-gray-500 mb-1">Temporary password (copy and share securely):</p>
            <p class="font-mono text-sm font-bold text-indigo-700 select-all"><?= htmlspecialchars($_SESSION['new_lecturer_temp_pass']) ?></p>
            <p class="text-xs text-amber-600 mt-1">⚠ This will not be shown again. Copy it now.</p>
          </div>
          <?php unset($_SESSION['new_lecturer_temp_pass']); ?>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($error_msg)): ?>
      <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 flex-shrink-0"></i>
        <p class="text-sm text-red-700"><?= htmlspecialchars($error_msg) ?></p>
      </div>
      <?php endif; ?>

      <!-- Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <?php foreach ([
          ['label'=>'Students',  'value'=>$total_students,  'color'=>'bg-green-500',  'icon'=>'users'],
          ['label'=>'Lecturers', 'value'=>$total_lecturers, 'color'=>'bg-cyan-500',   'icon'=>'graduation-cap'],
          ['label'=>'Admins',    'value'=>$total_admins,    'color'=>'bg-indigo-500', 'icon'=>'shield'],
          ['label'=>'Locked',    'value'=>$locked_accounts, 'color'=>'bg-red-500',    'icon'=>'lock'],
        ] as $s): ?>
        <div class="bg-white rounded-2xl p-4 border border-gray-100 flex items-center gap-3">
          <div class="w-10 h-10 <?= $s['color'] ?> rounded-xl flex items-center justify-center">
            <i data-lucide="<?= $s['icon'] ?>" class="w-5 h-5 text-white"></i>
          </div>
          <div>
            <p class="text-xl font-bold text-gray-900"><?= number_format($s['value']) ?></p>
            <p class="text-xs text-gray-500"><?= $s['label'] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Filters -->
      <form method="GET" action="" class="bg-white rounded-2xl border border-gray-100 p-4 mb-4 flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-48">
          <div class="relative">
            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
              placeholder="Search by name, email or phone..."
              class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
          </div>
        </div>
        <select name="role" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="">All roles</option>
          <option value="student"  <?= $role==='student'  ? 'selected':'' ?>>Students</option>
          <option value="lecturer" <?= $role==='lecturer' ? 'selected':'' ?>>Lecturers</option>
          <option value="admin"    <?= $role==='admin'    ? 'selected':'' ?>>Admins</option>
        </select>
        <select name="status" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="">All statuses</option>
          <option value="active"   <?= $status==='active'   ? 'selected':'' ?>>Active</option>
          <option value="inactive" <?= $status==='inactive' ? 'selected':'' ?>>Inactive</option>
          <option value="locked"   <?= $status==='locked'   ? 'selected':'' ?>>Locked</option>
          <option value="pending"  <?= $status==='pending'  ? 'selected':'' ?>>Pending</option>
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2">
          <i data-lucide="filter" class="w-4 h-4"></i> Filter
        </button>
        <?php if ($search || $role || $status): ?>
        <a href="<?= APP_URL ?>/admin/users.php" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50">
          Clear
        </a>
        <?php endif; ?>
      </form>

      <!-- Users Table -->
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <?php if (empty($users)): ?>
              <tr>
                <td colspan="6" class="px-5 py-12 text-center">
                  <div class="flex flex-col items-center gap-3">
                    <i data-lucide="users" class="w-10 h-10 text-gray-300"></i>
                    <p class="text-gray-400 text-sm">No users found</p>
                    <?php if ($search || $role || $status): ?>
                    <a href="<?= APP_URL ?>/admin/users.php" class="text-indigo-600 text-sm hover:underline">Clear filters</a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php else: foreach ($users as $u):
                $role_color = match($u['role']) {
                  'admin'    => 'bg-indigo-100 text-indigo-700',
                  'lecturer' => 'bg-cyan-100 text-cyan-700',
                  default    => 'bg-green-100 text-green-700',
                };
                $status_color = match($u['status']) {
                  'active'   => 'bg-green-100 text-green-700',
                  'inactive' => 'bg-gray-100 text-gray-600',
                  'locked'   => 'bg-red-100 text-red-700',
                  default    => 'bg-amber-100 text-amber-700',
                };
                $avatar_color = match($u['role']) {
                  'admin'    => 'bg-indigo-500',
                  'lecturer' => 'bg-cyan-500',
                  default    => 'bg-green-500',
                };
              ?>
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 <?= $avatar_color ?> rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                      <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($u['full_name']) ?></p>
                      <p class="text-xs text-gray-400"><?= htmlspecialchars($u['email']) ?></p>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-4">
                  <p class="text-sm text-gray-700"><?= htmlspecialchars($u['phone']) ?></p>
                  <?php if ($u['department']): ?>
                  <p class="text-xs text-gray-400"><?= htmlspecialchars($u['department']) ?></p>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-4">
                  <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $role_color ?>">
                    <?= ucfirst($u['role']) ?>
                  </span>
                </td>
                <td class="px-5 py-4">
                  <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $status_color ?>">
                    <?= ucfirst($u['status']) ?>
                  </span>
                </td>
                <td class="px-5 py-4 text-sm text-gray-500">
                  <?= date('M d, Y', strtotime($u['created_at'])) ?>
                </td>
                <td class="px-5 py-4">
                  <div class="flex items-center gap-1">
                    <?php if ($u['status'] === 'locked'): ?>
                    <form method="POST" class="inline">
                      <input type="hidden" name="action" value="unlock">
                      <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                      <button type="submit" title="Unlock account"
                        class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors">
                        <i data-lucide="unlock" class="w-4 h-4"></i>
                      </button>
                    </form>
                    <?php endif; ?>

                    <?php if ($u['role'] !== 'admin'): ?>
                    <form method="POST" class="inline" onsubmit="return confirm('<?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?> this account?')">
                      <input type="hidden" name="action" value="toggle_status">
                      <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                      <input type="hidden" name="status" value="<?= $u['status'] === 'active' ? 'inactive' : 'active' ?>">
                      <button type="submit" title="<?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?>"
                        class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors">
                        <i data-lucide="<?= $u['status'] === 'active' ? 'user-x' : 'user-check' ?>" class="w-4 h-4"></i>
                      </button>
                    </form>

                    <form method="POST" class="inline" onsubmit="return confirm('Send a new temporary password to this user?')">
                      <input type="hidden" name="action" value="reset_password">
                      <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                      <button type="submit" title="Reset password"
                        class="p-2 text-gray-400 hover:text-cyan-600 hover:bg-cyan-50 rounded-lg transition-colors">
                        <i data-lucide="key" class="w-4 h-4"></i>
                      </button>
                    </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
          <p class="text-sm text-gray-500">
            Showing page <?= $page ?> of <?= $total_pages ?> (<?= number_format($total) ?> total users)
          </p>
          <div class="flex gap-2">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>&status=<?= urlencode($status) ?>"
               class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">
              Previous
            </a>
            <?php endif; ?>
            <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>&status=<?= urlencode($status) ?>"
               class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">
              Next
            </a>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>

<!-- CREATE LECTURER MODAL -->
<div id="create-lecturer-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
  <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
      <h3 class="font-semibold text-gray-900 flex items-center gap-2">
        <i data-lucide="user-plus" class="w-5 h-5 text-indigo-600"></i>
        Create Lecturer Account
      </h3>
      <button onclick="document.getElementById('create-lecturer-modal').classList.add('hidden')"
        class="text-gray-400 hover:text-gray-600">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <form method="POST" action="" class="p-6 space-y-4">
      <input type="hidden" name="action" value="create_lecturer">
      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Full name *</label>
          <input type="text" name="full_name" required placeholder="As it will appear on certificates"
            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Email *</label>
          <input type="email" name="email" required placeholder="lecturer@email.com"
            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone *</label>
          <input type="tel" name="phone" required placeholder="+254700000000"
            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Address *</label>
          <input type="text" name="address" required placeholder="Nairobi, Kenya"
            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
          <input type="text" name="department" placeholder="e.g. Cybersecurity"
            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
      </div>
      <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex gap-2">
        <i data-lucide="info" class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5"></i>
        <p class="text-xs text-amber-700">A temporary password will be auto-generated and sent to the lecturer's email. They must change it on first login.</p>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="button" onclick="document.getElementById('create-lecturer-modal').classList.add('hidden')"
          class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">
          Cancel
        </button>
        <button type="submit"
          class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-xl text-sm font-medium flex items-center justify-center gap-2">
          <i data-lucide="send" class="w-4 h-4"></i>
          Create & Send Email
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>





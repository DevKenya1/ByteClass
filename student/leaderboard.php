<?php
$required_role = 'student';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/auth-check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/helpers/response.php';

$db = Database::getInstance()->getConnection();
$id = (int)$_SESSION['user_id'];

$lb = $db->query("
    SELECT u.id, u.full_name, u.profile_photo, u.points, u.created_at,
        RANK() OVER (ORDER BY u.points DESC) AS rnk,
        (SELECT COUNT(*) FROM enrollments e WHERE e.student_id=u.id AND e.completed_at IS NOT NULL) AS completed
    FROM users u WHERE u.role='student' AND u.status='active'
    ORDER BY u.points DESC LIMIT 50
")->fetchAll();

$my_rank_stmt = $db->prepare("SELECT COUNT(*)+1 FROM users WHERE role='student' AND points > (SELECT points FROM users WHERE id=?)");
$my_rank_stmt->execute([$id]);
$my_rank = (int)$my_rank_stmt->fetchColumn();
$my_info = $db->prepare("SELECT full_name, profile_photo, points FROM users WHERE id=?");
$my_info->execute([$id]); $me = $my_info->fetch();

$page_title = 'Leaderboard';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/head.php';
?>
<div class="flex min-h-screen bg-gray-50">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/sidebar-student.php'; ?>
  <div id="main-content" class="flex-1 flex flex-col" style="margin-left:256px;">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/navbar-student.php'; ?>
    <main class="flex-1 p-6">
      <div class="bg-gradient-to-r from-indigo-600 to-cyan-500 rounded-2xl px-6 py-5 mb-6">
        <h2 class="text-white text-xl font-bold">🏆 Leaderboard</h2>
        <p class="text-indigo-100 text-sm mt-0.5">Top performing students on ByteClass</p>
      </div>

      <!-- My rank card -->
      <div class="bg-white rounded-2xl border border-indigo-200 p-5 mb-6 flex items-center gap-4">
        <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white text-xl font-black">#<?= $my_rank ?></div>
        <div class="flex items-center gap-3 flex-1">
          <?php if ($me['profile_photo']): ?>
          <img src="<?= htmlspecialchars($me['profile_photo']) ?>" class="w-10 h-10 rounded-full object-cover border-2 border-indigo-200" />
          <?php else: ?>
          <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center text-white font-bold">
            <?= strtoupper(substr($me['full_name'],0,1)) ?>
          </div>
          <?php endif; ?>
          <div>
            <p class="font-semibold text-gray-900"><?= htmlspecialchars($me['full_name']) ?> <span class="text-indigo-600 text-sm">(You)</span></p>
            <p class="text-sm text-gray-500"><?= number_format($me['points']) ?> points</p>
          </div>
        </div>
        <div class="text-right">
          <p class="text-sm text-gray-500">Your rank</p>
          <p class="text-2xl font-black text-indigo-600">#<?= $my_rank ?></p>
        </div>
      </div>

      <!-- Top 3 podium -->
      <?php $top3 = array_slice($lb, 0, 3); $podium = [$top3[1] ?? null, $top3[0] ?? null, $top3[2] ?? null]; ?>
      <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
        <h3 class="font-semibold text-gray-900 mb-6 text-center">Top 3</h3>
        <div class="flex items-end justify-center gap-6">
          <?php
          $heights = ['h-16','h-24','h-12'];
          $colors  = ['bg-gray-300 text-gray-700','bg-amber-400 text-white','bg-amber-800 text-white'];
          foreach ($podium as $pi => $person): if (!$person) continue; ?>
          <div class="flex flex-col items-center gap-2">
            <?php if ($person['profile_photo']): ?>
            <img src="<?= htmlspecialchars($person['profile_photo']) ?>" class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-md" />
            <?php else: ?>
            <div class="w-12 h-12 bg-indigo-200 rounded-full flex items-center justify-center font-bold text-indigo-700 text-lg shadow-md">
              <?= strtoupper(substr($person['full_name'],0,1)) ?>
            </div>
            <?php endif; ?>
            <p class="text-sm font-semibold text-gray-800 text-center max-w-24 truncate"><?= htmlspecialchars(explode(' ',$person['full_name'])[0]) ?></p>
            <p class="text-xs text-indigo-600 font-bold"><?= number_format($person['points']) ?> pts</p>
            <div class="w-20 <?= $heights[$pi] ?> <?= $colors[$pi] ?> rounded-t-xl flex items-center justify-center font-black text-lg shadow">
              <?= $person['rnk'] ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Full list -->
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
          <p class="text-sm font-semibold text-gray-600">All Rankings</p>
        </div>
        <div class="divide-y divide-gray-50">
          <?php foreach ($lb as $person):
            $is_me = (int)$person['id'] === $id;
          ?>
          <div class="px-5 py-4 flex items-center gap-4 <?= $is_me ? 'bg-indigo-50' : 'hover:bg-gray-50' ?> transition-colors">
            <span class="text-lg font-black <?= $person['rnk'] <= 3 ? 'text-amber-500' : 'text-gray-400' ?> w-8 text-center">
              <?= $person['rnk'] <= 3 ? ['🥇','🥈','🥉'][$person['rnk']-1] : '#'.$person['rnk'] ?>
            </span>
            <?php if ($person['profile_photo']): ?>
            <img src="<?= htmlspecialchars($person['profile_photo']) ?>" class="w-10 h-10 rounded-full object-cover border-2 <?= $is_me ? 'border-indigo-300' : 'border-gray-100' ?>" />
            <?php else: ?>
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white flex-shrink-0
              <?= $is_me ? 'bg-indigo-600' : 'bg-indigo-300' ?>">
              <?= strtoupper(substr($person['full_name'],0,1)) ?>
            </div>
            <?php endif; ?>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-gray-900 truncate">
                <?= htmlspecialchars($person['full_name']) ?>
                <?php if ($is_me): ?><span class="text-indigo-600 text-xs ml-1">(You)</span><?php endif; ?>
              </p>
              <p class="text-xs text-gray-400"><?= $person['completed'] ?> course<?= $person['completed']!=1?'s':'' ?> completed</p>
            </div>
            <div class="text-right">
              <p class="font-bold text-indigo-600"><?= number_format($person['points']) ?></p>
              <p class="text-xs text-gray-400">points</p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </main>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/footer-minimal.php'; ?>
  </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/includes/scripts.php'; ?>

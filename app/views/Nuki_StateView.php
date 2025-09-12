<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();
session_start();

require_once '../controllers/Nuki_State_Controller.php';

$controller = new Nuki_StateController();
$controller->fetchAndLogSmartlocks(false); // keep history
$history = $controller->getSmartlockHistory();

// Compute statistics per group
$groupStats = [];

foreach ($history as $entry) {
    $group = $entry['category'] ?? 'Unknown';
    $state = $entry['state'];
    $duration = $entry['duration'];

    if (!isset($groupStats[$group])) {
        $groupStats[$group] = [
            'online_count' => 0, 'offline_count' => 0,
            'online_duration' => 0, 'offline_duration' => 0
        ];
    }

    if ($state == 0) {
        $groupStats[$group]['online_count']++;
        $groupStats[$group]['online_duration'] += $duration;
    } else {
        $groupStats[$group]['offline_count']++;
        $groupStats[$group]['offline_duration'] += $duration;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Nuki Smartlock Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/styles.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include '../views/nav.php'; ?>

<div class="container mt-5">
  <h2 class="mb-4">Smartlock State Overview</h2>

  <div class="row gy-4">
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header">Change Counts</div>
        <div class="card-body">
          <canvas id="countChart"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header">Total Duration (Hours)</div>
        <div class="card-body">
          <canvas id="durationChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <h4 class="mt-5">Full Smartlock History</h4>
  <input id="searchInput" class="form-control mb-3" placeholder="Search by name...">
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>Name</th>
          <th>Status</th>
          <th>Start</th>
          <th>End</th>
          <th>Duration</th>
        </tr>
      </thead>
      <tbody id="historyBody">
        <?php foreach ($history as $r):
          $start = strtotime($r['start_time']);
          $end = $r['end_time'] ? strtotime($r['end_time']) : time();
          $sec = $end - $start;
          $d = floor($sec / 86400);
          $h = floor(($sec % 86400) / 3600);
          $m = floor(($sec % 3600) / 60);
          $s = $sec % 60;
          $duration = ($d ? "{$d}d " : '') . sprintf('%02d:%02d:%02d', $h, $m, $s);
          $label = $r['state'] == 0
              ? '<span class="text-success">🟢 Online</span>'
              : '<span class="text-danger">🔴 Offline</span>';
        ?>
        <tr>
          <td><?= htmlspecialchars($r['name']) ?></td>
          <td><?= $label ?></td>
          <td><?= date('Y-m-d H:i:s', $start) ?></td>
          <td><?= $r['end_time'] ? date('Y-m-d H:i:s', $end) : 'Ongoing' ?></td>
          <td><?= $duration ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const stats = <?= json_encode($groupStats) ?>;
  const groups = Object.keys(stats);

  const countOnline = groups.map(g => stats[g].online_count);
  const countOffline = groups.map(g => stats[g].offline_count);

  // Duration in days instead of hours
  const durOnline = groups.map(g => (stats[g].online_duration / 86400).toFixed(2));
  const durOffline = groups.map(g => (stats[g].offline_duration / 86400).toFixed(2));

  new Chart(document.getElementById('countChart'), {
    type: 'bar',
    data: {
      labels: groups,
      datasets: [
        { label: 'Online', data: countOnline, backgroundColor: 'rgba(54,162,235,0.6)' },
        { label: 'Offline', data: countOffline, backgroundColor: 'rgba(255,99,132,0.6)' }
      ]
    },
    options: {
      plugins: {
        title: { display: true, text: 'State Change Count' },
        legend: { position: 'bottom' }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: { display: true, text: 'Count' }
        }
      }
    }
  });

  new Chart(document.getElementById('durationChart'), {
    type: 'bar',
    data: {
      labels: groups,
      datasets: [
        { label: 'Online Days', data: durOnline, backgroundColor: 'rgba(54,162,235,0.6)' },
        { label: 'Offline Days', data: durOffline, backgroundColor: 'rgba(255,99,132,0.6)' }
      ]
    },
    options: {
      plugins: {
        title: { display: true, text: 'Online / Offline Duration (days)' },
        legend: { position: 'bottom' }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: { display: true, text: 'Days' }
        }
      }
    }
  });

  document.getElementById('searchInput').addEventListener('keyup', e => {
    const query = e.target.value.toLowerCase();
    document.querySelectorAll('#historyBody tr').forEach(row => {
      row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
    });
  });
});
</script>

</body>
</html>

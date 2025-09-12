<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
session_write_close();

require_once '../controllers/AccountController.php';
require_once '../controllers/DeviceSmarlockController.php';

$search = $_GET['search'] ?? '';
$onlyExpired = isset($_GET['expired']) && $_GET['expired'] == '1';

$accountController = new AccountController();
$deviceController = new SmartlockDeviceController();

$smartLockAuths = $accountController->getSmartLockAuthList($search, $onlyExpired);
$smartLockDevices = $deviceController->fetchSmartlockDevice();

$deviceNames = [];
foreach ($smartLockDevices as $device) {
    $deviceNames[$device->getSmartlockId()] = $device->getName();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Locks List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/styles.css">
</head>

<body>
    <?php include '../views/nav.php'; ?>

    <div class="container mt-5">
        <h2>Authorized Smart Locks List</h2>

        <form method="GET" class="mb-3 d-flex">
            <input type="text" name="search" class="form-control" placeholder="Search Smart Lock..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit" class="btn btn-primary mx-2">Search</button>
            <button type="submit" name="expired" value="1" class="btn btn-danger">View Expired</button>
        </form>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>SmartLock ID</th>
                    <th>Device Name</th>
                    <th>Auth ID</th>
                    <th>Name</th>
                    <th>Authorized Until</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($smartLockAuths)): ?>
                    <?php foreach ($smartLockAuths as $auth): ?>
                        <tr id="row-<?= htmlspecialchars($auth['authId'], ENT_QUOTES, 'UTF-8'); ?>">
                            <td><?= htmlspecialchars($auth['smartlockId'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($deviceNames[$auth['smartlockId']] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($auth['authId'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($auth['name'] ?? 'No Name', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($auth['allowedUntilTime'] ?? 'Permanent Access', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($auth['state'] ?? '⚪ Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <button class="btn btn-danger btn-sm delete-btn"
                                    data-auth-id="<?= htmlspecialchars($auth['authId'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-smartlock-id="<?= htmlspecialchars($auth['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-name="<?= htmlspecialchars($auth['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?>"
                                    data-state="<?= htmlspecialchars($auth['state'] ?? '⚪ Unknown', ENT_QUOTES, 'UTF-8'); ?>"
                                    <?= $auth['state'] === '🔴 Offline' ? 'disabled' : ''; ?>>Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No authorized Smart Locks found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const deleteButtons = document.querySelectorAll(".delete-btn");

        deleteButtons.forEach(button => {
            const row = button.closest("tr");
            const statusCell = row.querySelector("td:nth-child(5)");

            // Disable button if device is offline
            if (statusCell && statusCell.textContent.trim() === "🔴 Offline") {
                button.disabled = true;
            }

            button.addEventListener("click", function() {
                if (button.disabled) return;

                const id = this.getAttribute("data-auth-id");
                const smartlockId = this.getAttribute("data-smartlock-id");
                const name = this.getAttribute("data-name");
                const state = this.getAttribute("data-state");

                if (!confirm(`Are you sure you want to delete the authorization for "${name}" (${state})?`)) {
                    return;
                }

                fetch("./delete_auth.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            id: id,
                            smartlockId: smartlockId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById(`row-${id}`).remove();
                        } else {
                            alert("Delete failed: " + data.error);
                        }
                    })
                    .catch(error => console.error("Request error:", error));
            });
        });
    });
</script>
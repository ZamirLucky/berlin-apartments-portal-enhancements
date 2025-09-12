<?php

// ../views/UserView.php
require_once '../controllers/LoginController.php';
require_once '../controllers/User.php';


// Check login
$loginController = new LoginController();
if (!$loginController->isLoggedIn()) {
    exit(); // or redirect to login
}

// Create an instance of UserController
$controller = new UserController();

try {
    // Fetch user data as an array of ModelUser
    $userDataArray = $controller->fetchUserData();
} catch (Exception $e) {
    error_log('Error fetching user data: ' . $e->getMessage());
    $userDataArray = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Information</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../public/css/styles.css">

    <!-- FontAwesome for icons -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <?php
include '../views/nav.php'; ?>
    <div class="container mt-5">
        <!-- User Information Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-title mb-0">User Information</h2>
                <?php if (!empty($userDataArray)): ?>
                    <span class="badge bg-info text-dark">
                        Total: <?php echo count($userDataArray); ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <!-- Search Input, Button, Sort Button, and Reset Button -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <input
                                type="text"
                                id="userSearch"
                                class="form-control"
                                placeholder="Search Users by Name or Email">
                            <button class="btn btn-primary" id="searchButton" type="button">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <button class="btn btn-secondary ms-2" id="sortButton" type="button">
                                <i class="fas fa-sort-alpha-down"></i> Sort
                            </button>
                            <button class="btn btn-warning ms-2" id="resetButton" type="button">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- User Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered" id="userTable">
                        <thead class="table-dark">
                            <tr>
                                <th>User ID</th>
                                <th>Account ID</th>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Creation Date</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <!-- Rows inserted by JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div id="paginationControls" class="mt-3"></div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS + dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script
        src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js">
    </script>
    <script
        src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js">
    </script>

    <!-- Pass PHP data to JavaScript -->
    <script>
        // Convert ModelUser objects to a standard array for JS
        // If your model doesn't have all fields as public, 
        // you might see them as stdClass when JSON-encoded. 
        // But it should be fine if the constructor sets them.
        const userData = <?php echo json_encode($userDataArray); ?>;
    </script>

    <!-- Custom JS -->
    <script src="../../public/js/user.js"></script>
</body>

</html>
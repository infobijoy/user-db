<?php
session_start();

// Database connection
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    // XAMPP Server
    $host = "localhost";
    $username = "root";
    $password = "";
    $dbname = "result";
} else {
    // Live Server
    $host = "localhost"; // Usually remains the same
    $username = "bijoydev_result";
    $password = "bijoydev_result";
    $dbname = "bijoydev_result";
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle user login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $login_error = "Invalid username or password.";
    }
}

// Handle user signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle form submission for community members
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
    $name = $_POST['name'];
    $podobi = $_POST['podobi'];
    $mobile_number = $_POST['mobile_number'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO community_members (name, podobi, mobile_number, address) VALUES (:name, :podobi, :mobile_number, :address)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':podobi', $podobi);
    $stmt->bindParam(':mobile_number', $mobile_number);
    $stmt->bindParam(':address', $address);
    $stmt->execute();
}

// Fetch all members ordered by podobi
$stmt = $conn->query("SELECT * FROM community_members ORDER BY 
    CASE 
        WHEN podobi = 'আহবায়ক' THEN 1
        WHEN podobi = 'সিঃ যুগ্ম আহ্বায়ক' THEN 2
        WHEN podobi = 'যুগ্ম আহ্বায়ক' THEN 3
        WHEN podobi = 'সদস্য সচিব' THEN 4
        WHEN podobi = 'সিঃ যুগ্ম সদস্য সচিব' THEN 5
        WHEN podobi = 'যুগ্ম সদস্য সচিব' THEN 6
        WHEN podobi = 'সদস্য' THEN 7
        ELSE 8
    END");
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Member Info</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Login/Signup Section -->
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h2 class="text-2xl font-bold mb-4">Login</h2>
                <?php if (isset($login_error)): ?>
                    <p class="text-red-500 mb-4"><?= $login_error ?></p>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="grid grid-cols-1 gap-4">
                        <input type="text" name="username" placeholder="Username" class="p-2 border rounded" required>
                        <input type="password" name="password" placeholder="Password" class="p-2 border rounded" required>
                    </div>
                    <button type="submit" name="login" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Login</button>
                </form>
            </div>
        <?php else: ?>
            <!-- Logout Button -->
            <div class="text-right mb-4">
                <a href="?logout" class="text-red-500 hover:text-red-700">Logout</a>
            </div>

            <!-- Add New Member Form (Visible to Logged-in Users) -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h2 class="text-2xl font-bold mb-4">Add New Member</h2>
                <form method="POST" action="">
                    <div class="grid grid-cols-1 gap-4">
                        <input type="text" name="name" placeholder="Name" class="p-2 border rounded" required>
                        <select name="podobi" class="p-2 border rounded" required>
                            <option value="" disabled selected>Select one</option>
                            <option value="আহবায়ক">আহবায়ক</option>
                            <option value="সিঃ যুগ্ম আহ্বায়ক">সিঃ যুগ্ম আহ্বায়ক</option>
                            <option value="যুগ্ম আহ্বায়ক">যুগ্ম আহ্বায়ক</option>
                            <option value="সদস্য সচিব">সদস্য সচিব</option>
                            <option value="সিঃ যুগ্ম সদস্য সচিব">সিঃ যুগ্ম সদস্য সচিব</option>
                            <option value="যুগ্ম সদস্য সচিব">যুগ্ম সদস্য সচিব</option>
                            <option value="সদস্য">সদস্য</option>
                        </select>
                        <input type="text" name="mobile_number" placeholder="Mobile Number" class="p-2 border rounded" required>
                        <textarea name="address" placeholder="Address" class="p-2 border rounded" required></textarea>
                    </div>
                    <button type="submit" name="add_member" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Submit</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Display Members Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($members as $member): ?>
                <?php
                // Assign different colors based on podobi
                $colors = [
                    'আহবায়ক' => 'bg-red-100',
                    'সিঃ যুগ্ম আহ্বায়ক' => 'bg-blue-100',
                    'যুগ্ম আহ্বায়ক' => 'bg-green-100',
                    'সদস্য সচিব' => 'bg-yellow-100',
                    'সিঃ যুগ্ম সদস্য সচিব' => 'bg-purple-100',
                    'যুগ্ম সদস্য সচিব' => 'bg-pink-100',
                    'সদস্য' => 'bg-gray-100',
                ];
                $color = $colors[$member['podobi']] ?? 'bg-gray-100';
                ?>
                <div class="<?= $color ?> p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-bold"><?= htmlspecialchars($member['name']) ?></h3>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($member['podobi']) ?></p>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($member['mobile_number']) ?></p>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($member['address']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
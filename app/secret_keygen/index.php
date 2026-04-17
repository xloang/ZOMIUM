<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/utilities/userutils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/connection.php';

$user = UserUtils::RetrieveUser();



function generateKey($length = 32)
{
    return strtoupper(bin2hex(random_bytes($length / 2)));
}

function sendWebhook($message)
{
    $webhook = "https://discord.com/api/webhooks/1483934633635610686/Ju3IzcpVeR3IAYwkrK2j_ZN3LVreVw9bUiLWw7Mli6GnG0ZiBYEbzlxKVheSq0X3Z627";
    if (empty($webhook)) {
        return;
    }

    $data = ["content" => $message];
    $options = [
        "http" => [
            "header" => "Content-type: application/json",
            "method" => "POST",
            "content" => json_encode($data)
        ]
    ];

    @file_get_contents($webhook, false, stream_context_create($options));
}

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $new_key = generateKey(32);
    $discord_uid = trim($_POST['discord_uid'] ?? '');

    $stmt = $con->prepare("INSERT INTO accesskeys (access_key, access_discorduid) VALUES (?, ?)");
    if ($stmt === false) {
        $error_msg = "Database error: " . $con->error;
    } else {
        $stmt->bind_param("ss", $new_key, $discord_uid);

        if ($stmt->execute()) {
            $success_msg = "Successfully generated key: <code>$new_key</code>";
            sendWebhook("[KEY GENERATED] $new_key" . ($discord_uid ? " (For: $discord_uid)" : ""));
        } else {
            $error_msg = "Database error: " . $con->error;
        }

        $stmt->close();
    }
}

if (isset($_GET['delete'])) {
    $key_to_delete = trim((string) $_GET['delete']);
    $stmt = $con->prepare("DELETE FROM accesskeys WHERE access_key = ?");

    if ($stmt !== false) {
        $stmt->bind_param("s", $key_to_delete);
        if ($stmt->execute()) {
            $success_msg = "Key deleted: $key_to_delete";
            sendWebhook("[KEY DELETED] $key_to_delete");
        }
        $stmt->close();
    }

    header("Location: index.php?success=" . urlencode($success_msg));
    exit;
}

if (isset($_GET['success'])) {
    $success_msg = $_GET['success'];
}

$result_keys = $con->query("SELECT access_key, access_discorduid FROM accesskeys ORDER BY access_key ASC");
?>
<!DOCTYPE html>
<html>

<head>
    <?php
    $page_title = 'Key Generator - Zomium';
    include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/head.php';
    ?>
    <style>
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
        }

        .bg-active {
            background: #28a745;
        }
    </style>
</head>

<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/header.php'; ?>
    <main class="app-main">
        <div class="container py-4">
            <div class="hero-panel mb-4">
                <div>
                    <div class="section-title mb-2">Internal Tools</div>
                    <h1 class="display-6 fw-bold mb-2">Invite Key Generator</h1>
                    <p class="mb-0 text-white-50">Manage and generate registration keys for new users.</p>
                </div>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success mb-4"><?= $success_msg ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-danger mb-4"><?= $error_msg ?></div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card border-0 mb-4">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-3">Generate New Key</h2>
                            <form method="POST" class="d-grid gap-3">
                                <div>
                                    <label class="small text-secondary mb-1">Discord UID (Optional)</label>
                                    <input type="text" name="discord_uid" class="form-control" placeholder="e.g. 1234567890">
                                </div>
                                <button type="submit" name="generate" class="btn btn-primary py-2 mt-2">
                                    <i class="fas fa-plus-circle me-2"></i>Generate Key
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card border-0">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-2">About Keys</h2>
                            <p class="small text-secondary mb-0">Keys are one-time use tokens. Once a user registers with a key, it is removed from the database and cannot be used again. Deleting a key renders it invalid immediately.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card border-0">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-3">Existing Keys</h2>
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle mb-0">
                                    <thead class="text-secondary small">
                                        <tr>
                                            <th>ACCESS KEY</th>
                                            <th>DISCORD UID</th>
                                            <th>STATUS</th>
                                            <th class="text-end">ACTIONS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result_keys && $result_keys->num_rows > 0): ?>
                                            <?php while ($row = $result_keys->fetch_assoc()): ?>
                                                <tr class="key-row">
                                                    <td><code class="text-info"><?= htmlspecialchars($row['access_key'], ENT_QUOTES, 'UTF-8') ?></code></td>
                                                    <td><span class="text-secondary"><?= htmlspecialchars($row['access_discorduid'] ?: 'Not set', ENT_QUOTES, 'UTF-8') ?></span></td>
                                                    <td><span class="status-badge bg-active">Active</span></td>
                                                    <td class="text-end">
                                                        <a href="?delete=<?= urlencode($row['access_key']) ?>" class="text-danger small" onclick="return confirm('Are you sure you want to delete this key?')">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-secondary">No keys found in database.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/core/ui/footer.php'; ?>
</body>

</html>

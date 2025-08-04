<?php
include "../Components/checkifadmin.php";
include "../../Config/db.php";

$name = $email = "";
$error = "";
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];

    $sql = "UPDATE users SET Username = ?, Email = ? WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $email, $id);

    if ($stmt->execute()) {
        header("Location: admin.users.php"); 
        exit();
    } else {
        $error = "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}
$sql = "SELECT Username, Email FROM users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $name = $row['Username'];
    $email = $row['Email'];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Profile</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#6366f1',secondary:'#8b5cf6'},borderRadius:{'none':'0px','sm':'4px',DEFAULT:'8px','md':'12px','lg':'16px','xl':'20px','2xl':'24px','3xl':'32px','full':'9999px','button':'8px'}}}}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        #sidebar.collapsed ~ #main-content {
            margin-left: 4rem; /* Matches collapsed sidebar width (w-16) */
        }
        #sidebar:not(.collapsed) ~ #main-content {
            margin-left: 16rem; /* Matches expanded sidebar width (w-64) */
        }
        :where([class^="ri-"])::before { content: "\f3c2"; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9ff 0%, #f1f5ff 100%);
            min-height: 100vh;
        }
        
        .form-input {
            transition: all 0.3s ease;
            border: none;
            border-bottom: 2px solid #e2e8f0;
            background: transparent;
            outline: none;
        }
        
        .form-input:focus {
            border-color: #6366f1;
        }
        
        .input-icon {
            color: #94a3b8;
            transition: all 0.3s ease;
        }
        
        .form-input:focus + .input-icon {
            color: #6366f1;
        }
        
        .error-message {
            animation: slideIn 0.3s ease forwards;
            transform: translateY(-10px);
            opacity: 0;
        }
        
        @keyframes slideIn {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .submit-button {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <?php include "../Components/sider.php";?>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-8 transition-all duration-300">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-semibold text-gray-800">Update Profile</h1>
            <p class="text-gray-500 mt-2">Make changes to your profile information</p>
        </div>
        
        <div id="errorContainer" class="hidden mb-6 bg-red-50 text-red-600 p-4 rounded-md flex items-start">
            <div class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5 flex items-center justify-center">
                <i class="ri-error-warning-line"></i>
            </div>
            <div id="errorMessage"></div>
        </div>
        
        <form id="updateForm" method="POST" class="space-y-6">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <div class="relative">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <div class="relative">
                    <input type="text" name="name" id="name" required value="<?php echo $name; ?>"
                    class="form-input w-full px-4 py-3 pr-10 rounded-button text-gray-800" value="Alexander Mitchell">
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center">
                        <i class="ri-user-line input-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="relative">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <div class="relative">
                    <input type="text" name="email" id="email" required value="<?php echo $email; ?>"
                    class="form-input w-full px-4 py-3 pr-10 rounded-button text-gray-800" value="alex.mitchell@example.com">
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center">
                        <i class="ri-mail-line input-icon"></i>
                    </div>
                </div>
            </div>
            
            <div class="pt-4">
                <button type="submit" class="submit-button w-full py-3 px-4 text-white font-medium !rounded-button whitespace-nowrap flex items-center justify-center">
                    <span>Save Changes</span>
                    <div class="w-5 h-5 ml-2 flex items-center justify-center">
                        <i class="ri-save-line"></i>
                    </div>
                </button>
            </div>
        </form>
        
        <div class="mt-6 text-center">
            <a href="admin.users.php" class="text-primary hover:text-primary/80 text-sm font-medium transition-colors">Cancel and return to dashboard</a>
        </div>
    </div>
</body>
</html>

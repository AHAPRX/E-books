<?php
include "../Components/checkifadmin.php";
include "../../Config/db.php";

$title = $author = $price = $pdfPath = $category = "";
$isFree = $hasCD = $hasHardCopy = $stock = 0;
$error = "";
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $pdfPath = $_POST['pdfPath'];
    $hasHardCopy = isset($_POST['hasHardCopy']) ? 1 : 0;
    $hasCD = isset($_POST['hasCD']) ? 1 : 0;
    $stock = $_POST['stock'];

    $sql = "UPDATE books SET Title = ?, Author = ?, Category = ?, Price = ? , PDFPath = ?, HasHardCopy = ?, HasCD = ?, Stock = ? WHERE BookID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdsiiii", $title, $author, $category, $price, $pdfPath, $hasHardCopy, $hasCD, $stock, $id);

    if ($stmt->execute()) {
        header("Location: admin.books.php");
        exit();
    } else {
        $error = "Error updating book: " . $stmt->error;
    }

    $stmt->close();
}

$sql = "SELECT * FROM books WHERE BookID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $title = $row['Title'];
    $author = $row['Author'];
    $category = $row['Category'];
    $price = $row['Price'];
    $pdfPath = $row['PDFPath'];
    $hasHardCopy = $row['HasHardCopy'];
    $hasCD = $row['HasCD'];
    $stock = $row['Stock'];
} else {
    die("Book not found.");
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Update Book | E - Books</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        slate: {
                            950: '#020617',
                            900: '#0f172a',
                            800: '#1e293b',
                            700: '#334155',
                            600: '#475569',
                            500: '#64748b',
                            400: '#94a3b8',
                            300: '#cbd5e1'
                        },
                        emerald: {
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857'
                        }
                    },
                    borderRadius: {
                        none: "0px",
                        sm: "4px",
                        DEFAULT: "8px",
                        md: "12px",
                        lg: "16px",
                        xl: "20px",
                        '2xl': "24px",
                        '3xl': "32px",
                        full: "9999px",
                        button: "8px",
                    }
                }
            }
        }
    </script>
    <style>
        #sidebar.collapsed ~ #main-content {
            margin-left: 4rem; /* Matches collapsed sidebar width (w-16) */
        }
        #sidebar:not(.collapsed) ~ #main-content {
            margin-left: 16rem; /* Matches expanded sidebar width (w-64) */
        }
        .scroll-animate {
            opacity: 0;
            transform: translateY(20px);
            transition: all 1s ease-out;
        }
        .scroll-animate.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .dropdown-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            transition: all 0.3s ease;
        }
        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .mobile-menu.open {
            max-height: 500px;
        }
        #sidebar.collapsed {
            width: 64px;
        }
        #main-content {
            margin-left: 256px;
            transition: margin-left 0.3s ease;
        }
        #main-content.expanded {
            margin-left: 64px;
        }
        #sidebar.collapsed .nav-text,
        #sidebar.collapsed .logo-text {
            display: none;
        }
        #sidebar.collapsed #toggle-sidebar {
            width: 64px;
            margin-left: 2px;
        }
        .form-input {
            transition: all 0.3s ease;
            border: none;
            border-bottom: 2px solid #475569;
            background: transparent;
            outline: none;
        }
        .form-input:focus {
            border-color: #34d399;
        }
        .input-icon {
            color: #94a3b8;
            transition: all 0.3s ease;
        }
        .form-input:focus + .input-icon {
            color: #34d399;
        }
        .submit-button {
            background: #10b981;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
</head>
<body class="bg-slate-950 text-white flex min-h-screen">
    <div class="flex min-h-screen w-full">
        <!-- Sidebar -->
        <?php include "../Components/sider.php"; ?>
        <!-- Main Content -->
        <main id="main-content" class="min-h-screen flex-1">
            <!-- Header -->
            <!-- Page Content -->
            <div class="pt-24 p-6">
                <div class="container mx-auto">
                    <div class="backdrop-blur-2xl bg-slate-800/30 rounded-2xl shadow-2xl hover:shadow-emerald-500/20 transition-all duration-500 p-8 max-w-md mx-auto scroll-animate">
                        <div class="text-center mb-8">
                            <h1 class="text-2xl font-semibold text-white">Update Book</h1>
                            <p class="text-slate-400 mt-2">Edit book details below</p>
                        </div>
                        <?php if ($error): ?>
                            <div class="text-red-400 text-sm mb-4"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Title</label>
                                <input type="text" name="title" required class="form-input w-full px-4 py-3 rounded-button text-white" value="<?php echo htmlspecialchars($title); ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Author</label>
                                <input type="text" name="author" class="form-input w-full px-4 py-3 rounded-button text-white" value="<?php echo htmlspecialchars($author); ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Category</label>
                                <select name="category" class="form-input w-full px-4 py-3 rounded-button text-white">
                                    <?php
                                    $catQuery = $conn->query("SELECT CategoryID, CategoryName FROM categories");
                                    while ($cat = $catQuery->fetch_assoc()) {
                                        $selected = ($cat['CategoryID'] == $category) ? 'selected' : '';
                                        echo "<option value='{$cat['CategoryID']}' $selected>" . htmlspecialchars($cat['CategoryName']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Price</label>
                                <input type="number" step="0.01" name="price" class="form-input w-full px-4 py-3 rounded-button text-white" value="<?php echo htmlspecialchars($price); ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">PDF Path</label>
                                <input type="text" name="pdfPath" class="form-input w-full px-4 py-3 rounded-button text-white" value="<?php echo htmlspecialchars($pdfPath); ?>">
                            </div>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="isFree" <?php echo $isFree ? 'checked' : ''; ?> class="text-emerald-500 focus:ring-emerald-500">
                                    <span class="text-sm text-slate-300">Is Free</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="hasHardCopy" <?php echo $hasHardCopy ? 'checked' : ''; ?> class="text-emerald-500 focus:ring-emerald-500">
                                    <span class="text-sm text-slate-300">Has Hard Copy</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="hasCD" <?php echo $hasCD ? 'checked' : ''; ?> class="text-emerald-500 focus:ring-emerald-500">
                                    <span class="text-sm text-slate-300">Has CD</span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Stock</label>
                                <input type="number" name="stock" class="form-input w-full px-4 py-3 rounded-button text-white" value="<?php echo htmlspecialchars($stock); ?>">
                            </div>
                            <button type="submit" class="submit-button w-full py-3 px-4 text-white font-medium rounded-button hover:bg-emerald-600">Save Changes</button>
                        </form>
                        <div class="mt-6 text-center">
                            <a href="admin.books.php" class="text-emerald-400 hover:text-emerald-500 text-sm font-medium">Cancel and return to dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '50px'
        };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);
        document.querySelectorAll('.scroll-animate').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>
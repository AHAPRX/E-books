<?php
include "../Components/checkifadmin.php";
include "../../Config/db.php";

if (!isset($_GET['id'])) {
    echo "Submission ID missing.";
    exit;
}

$submissionID = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isWinner = isset($_POST['isWinner']) ? 1 : 0;
    $timeLimitExceeded = isset($_POST['timeLimitExceeded']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE submissions SET IsWinner = ?, TimeLimitExceeded = ? WHERE SubmissionID = ?");
    $stmt->bind_param("iii", $isWinner, $timeLimitExceeded, $submissionID);

    if ($stmt->execute()) {
        header("Location: submisions.php?success=Submission updated.");
        exit;
    } else {
        $error = "Update failed.";
    }

    $stmt->close();
}

// Fetch current submission data
$stmt = $conn->prepare("SELECT * FROM submissions WHERE SubmissionID = ?");
$stmt->bind_param("i", $submissionID);
$stmt->execute();
$result = $stmt->get_result();
$submission = $result->fetch_assoc();
$stmt->close();

if (!$submission) {
    echo "Submission not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Submission - E - Books</title>
  <script src="https://cdn.tailwindcss.com"></script>
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
          }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
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
    .parallax-bg {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: -1;
    }
    .bg-blur {
      filter: blur(3rem);
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
  </style>
</head>
<body class="bg-slate-950 text-white">
  <!-- Include Navigation -->
  <!-- Include Sidebar -->
  <?php include "../Components/sider.php"; ?>

  <!-- Main Content -->
  <div id="main-content" class="ml-64 transition-all duration-300">
    <section class="py-24 px-6 relative">
      <div class="container mx-auto max-w-lg">
        <div id="form-container" class="backdrop-blur-2xl bg-slate-800/30 border border-slate-700/50 shadow-2xl hover:shadow-emerald-500/20 transition-all duration-500 group scroll-animate rounded-2xl">
          <div class="absolute inset-0 bg-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl"></div>
          <div class="p-8 relative">
            <h1 class="text-4xl font-black mb-8 text-white group-hover:text-emerald-400 transition-colors duration-300">
              Edit <span class="text-emerald-400">Submission</span>
            </h1>

            <?php if (!empty($error)): ?>
              <div class="mb-6 p-4 text-red-400 bg-red-500/10 border border-red-500/30 rounded-xl">
                <?php echo htmlspecialchars($error); ?>
              </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
              <!-- Is Winner Checkbox -->
              <div>
                <label class="inline-flex items-center">
                  <input type="checkbox" name="isWinner" <?php if ($submission['IsWinner']) echo 'checked'; ?> class="form-checkbox h-5 w-5 text-emerald-500 bg-slate-900/50 border-slate-700/50 focus:ring-emerald-500 rounded" />
                  <span class="ml-2 text-slate-300 font-semibold">Mark as Winner</span>
                </label>
              </div>

              <!-- Time Limit Exceeded Checkbox -->
              <div>
                <label class="inline-flex items-center">
                  <input type="checkbox" name="timeLimitExceeded" <?php if ($submission['TimeLimitExceeded']) echo 'checked'; ?> class="form-checkbox h-5 w-5 text-red-500 bg-slate-900/50 border-slate-700/50 focus:ring-red-500 rounded" />
                  <span class="ml-2 text-slate-300 font-semibold">Time Limit Exceeded</span>
                </label>
              </div>

              <!-- Buttons -->
              <div class="flex justify-between items-center mt-8">
                <a href="submisions.php" class="text-emerald-400 hover:text-emerald-500 font-semibold transition-colors duration-300 flex items-center gap-2">
                  <i class="ri-arrow-left-line"></i> Back
                </a>
                <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 text-lg font-semibold rounded-xl shadow-2xl shadow-emerald-500/30 transition-all duration-300 hover:scale-105">
                  Update
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
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

    // Parallax background effect
    let scrollY = 0;
    
    function updateParallax() {
      scrollY = window.scrollY;
      
      const bg1 = document.getElementById('bg1');
      const bg2 = document.getElementById('bg2');
      const bg3 = document.getElementById('bg3');
      
      if (bg1) bg1.style.transform = `translateY(${scrollY * 0.1}px)`;
      if (bg2) bg2.style.transform = `translateY(${scrollY * -0.15}px)`;
      if (bg3) bg3.style.transform = `translateY(${scrollY * 0.05}px)`;
    }

    window.addEventListener('scroll', updateParallax);
    updateParallax();
  </script>
</body>
</html>
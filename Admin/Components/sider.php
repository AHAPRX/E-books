<?php
$currentPage = basename($_SERVER['PHP_SELF']);
function isActive($page) {
    global $currentPage;
    return $currentPage === $page ? 'bg-slate-700/50 text-emerald-400 border-emerald-500/30' : 'text-slate-300 hover:bg-slate-700/50 hover:text-emerald-400';
}
?>

<style>
    #sidebar.collapsed .nav-text {
        opacity: 0;
        visibility: hidden;
        position: absolute;
        left: calc(100% + 0.5rem);
        top: 50%;
        transform: translateY(-50%);
        background: #1e293b; /* slate-800 */
        color: #cbd5e1; /* slate-300 */
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        white-space: nowrap;
        pointer-events: none;
        transition: opacity 0.2s ease, visibility 0.2s ease;
        z-index: 30;
    }

    #sidebar.collapsed .nav-link:hover .nav-text {
        opacity: 1;
        visibility: visible;
    }

    #sidebar.collapsed {
        width: 5rem; /* 80px */
    }

    #sidebar {
        transition: width 0.3s ease;
        overflow-x: hidden;
        overflow-y: hidden;
    }

    #sidebar-content-hide {
        overflow-x: hidden;
        overflow-y: hidden;
    }

    .nav-text {
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    #sidebar.collapsed .nav-text:not(.tooltip) {
        transform: translateX(-10px);
    }

    /* Hide logo when sidebar is open, show when collapsed */
    #sidebar:not(.collapsed) .header-logo {
        display: none;
    }

    #sidebar.collapsed .header-logo {
        display: block;
    }

    @media (max-width: 639px) {
        #sidebar {
            width: 5rem; /* 80px */
        }

        #sidebar:not(.collapsed) .nav-text {
            opacity: 0;
            visibility: hidden;
        }

        /* Always show logo on mobile (sidebar is always collapsed) */
        .header-logo {
            display: block !important;
        }
    }
</style>
<header class="bg-gradient-to-r from-slate-900/50 to-slate-950/50 backdrop-blur-2xl border-b border-slate-600/40 shadow-md fixed top-0 right-0 z-10 w-full h-14 flex items-center px-4 sm:px-6 scroll-animate">
    <nav class="flex items-center justify-between w-full">
        <div class="flex items-center">
            <img src="../../logos/logo_white.png" width="100" alt="E - Books Logo" class="mx-16 header-logo">
        </div>
        <!-- User Info -->
        <div class="flex items-center gap-2 ml-auto">
            <div class="relative group">
                <div class="w-10 h-10 rounded-full bg-emerald-400/20 border border-emerald-400/50 flex items-center justify-center text-slate-200 font-semibold text-base group-hover:scale-105 group-hover:shadow-[0_0_10px_rgba(16,185,129,0.3)] transition-all duration-300">
                    <?php echo strtoupper(substr($_SESSION['user'], 0, 2)); ?>
                </div>
            </div>
            <div class="text-base font-semibold text-slate-200 group-hover:text-emerald-400 transition-colors duration-300">
                <?php echo htmlspecialchars($_SESSION['user']); ?>
            </div>
        </div>
    </nav>
</header>
<aside id="sidebar" class="w-20 sm:w-64 bg-slate-900/50 backdrop-blur-xl border-r border-slate-700/50 shadow-2xl h-screen fixed left-0 top-0 z-20 flex flex-col scroll-animate">
    <div class="p-4 sm:p-4 flex items-center justify-between border-b border-slate-700/50">
        <div class="flex items-center gap-2 overflow-hidden">
            <a href="../../Admin/Admin/admin.php" class="group">
                <img src="../../Logos/logo_white.png" width="100" alt="E - Books Logo" class="group-hover:scale-105 transition-all duration-300">
            </a>
        </div>
        <div class="flex justify-end p-2">
            <button id="toggle-sidebar" class="w-10 h-10 flex items-center justify-center text-slate-400 hover:bg-gradient-to-r from-emerald-500/20 to-teal-600/20 hover:text-emerald-400 rounded-full transition-all duration-300">
                <i class="ri-arrow-right-s-line ri-xl transition-transform duration-300"></i>
            </button>
        </div>
    </div>

    <div id="sidebar-content-hide" class="py-4 flex-1 scroll-animate">
        <a href="../../Admin/Admin/admin.php" class="nav-link flex items-center gap-3 px-4 py-3 font-medium border-l-4 border-transparent <?php echo isActive('admin.php'); ?> transition-all duration-300 group relative">
            <div class="w-12 h-8 flex items-center justify-center"><i class="ri-dashboard-line group-hover:scale-110 transition-transform duration-300"></i></div>
            <span class="nav-text tooltip">Dashboard</span>
        </a>

        <a href="../../Admin/AdminBook/admin.books.php" class="nav-link flex items-center gap-3 px-4 py-3 font-medium border-l-4 border-transparent <?php echo isActive('admin.books.php'); ?> transition-all duration-300 group relative">
            <div class="w-12 h-8 flex items-center justify-center"><i class="ri-book-line group-hover:scale-110 transition-transform duration-300"></i></div>
            <span class="nav-text tooltip">Books</span>
        </a>

        <a href="../../Admin/AdminCategory/admin.category.php" class="nav-link flex items-center gap-3 px-4 py-3 font-medium border-l-4 border-transparent <?php echo isActive('admin.category.php'); ?> transition-all duration-300 group relative">
            <div class="w-12 h-8 flex items-center justify-center"><i class="ri-folder-add-line group-hover:scale-110 transition-transform duration-300"></i></div>
            <span class="nav-text tooltip">Add Category</span>
        </a>

        <a href="../../Admin/AdminUser/admin.users.php" class="nav-link flex items-center gap-3 px-4 py-3 font-medium border-l-4 border-transparent <?php echo isActive('admin.users.php'); ?> transition-all duration-300 group relative">
            <div class="w-12 h-8 flex items-center justify-center"><i class="ri-user-line group-hover:scale-110 transition-transform duration-300"></i></div>
            <span class="nav-text tooltip">Users</span>
        </a>

        <a href="../../Admin/AdminCompetition/admin.competitions.php" class="nav-link flex items-center gap-3 px-4 py-3 font-medium border-l-4 border-transparent <?php echo isActive('admin.competitions.php'); ?> transition-all duration-300 group relative">
            <div class="w-12 h-8 flex items-center justify-center"><i class="ri-trophy-line group-hover:scale-110 transition-transform duration-300"></i></div>
            <span class="nav-text tooltip">Competitions</span>
        </a>

        <a href="../../Admin/AdminWinner/admin.winners.php" class="nav-link flex items-center gap-3 px-4 py-3 font-medium border-l-4 border-transparent <?php echo isActive('admin.winners.php'); ?> transition-all duration-300 group relative">
            <div class="w-12 h-8 flex items-center justify-center"><i class="ri-medal-line group-hover:scale-110 transition-transform duration-300"></i></div>
            <span class="nav-text tooltip">Winners</span>
        </a>

        <a href="../../Admin/AdminSubmissions/submisions.php" class="nav-link flex items-center gap-3 px-4 py-3 font-medium border-l-4 border-transparent <?php echo isActive('submisions.php'); ?> transition-all duration-300 group relative">
            <div class="w-12 h-8 flex items-center justify-center"><i class="ri-upload-cloud-line group-hover:scale-110 transition-transform duration-300"></i></div>
            <span class="nav-text tooltip">Submissions</span>
        </a>

        <a href="../../Admin/AdminOrder/admin.order.php" class="nav-link flex items-center gap-3 px-4 py-3 font-medium border-l-4 border-transparent <?php echo isActive('admin.order.php'); ?> transition-all duration-300 group relative">
            <div class="w-12 h-8 flex items-center justify-center"><i class="ri-shopping-cart-line group-hover:scale-110 transition-transform duration-300"></i></div>
            <span class="nav-text tooltip">Orders</span>
        </a>

        <a href="../../Auth/logout.php" class="nav-link flex items-center gap-3 px-4 py-3 font-medium text-slate-300 hover:bg-slate-700/50 hover:text-red-400 border-l-4 border-transparent transition-all duration-300 group relative">
            <div class="w-12 h-8 flex items-center justify-center"><i class="ri-logout-box-line group-hover:scale-110 transition-transform duration-300"></i></div>
            <span class="nav-text tooltip">Logout</span>
        </a>
    </div>
</aside>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const sidebar = document.getElementById("sidebar");
        const toggleBtn = document.getElementById("toggle-sidebar");
        const mainContent = document.getElementById("main-content");
        const header = document.querySelector("header");
        const icon = toggleBtn.querySelector("i");

        function updateSidebar() {
            const isMobile = window.matchMedia("(max-width: 639px)").matches;

            if (isMobile) {
                // Force collapsed on mobile
                sidebar.classList.add("collapsed");
                sidebar.classList.remove("w-64");
                sidebar.classList.add("w-20");
                header.classList.add("sidebar-collapsed");

                if (mainContent) {
                    mainContent.classList.remove("ml-64");
                    mainContent.classList.add("ml-20");
                }

                icon.className = "ri-arrow-right-s-line ri-xl";
                console.log("Mobile: Sidebar collapsed, logo visible");
            } else {
                // Expand by default on desktop
                sidebar.classList.remove("collapsed");
                sidebar.classList.remove("w-20");
                sidebar.classList.add("w-64");
                header.classList.remove("sidebar-collapsed");

                if (mainContent) {
                    mainContent.classList.remove("ml-20");
                    mainContent.classList.add("ml-64");
                }

                icon.className = "ri-arrow-left-line ri-xl transition-transform duration-300";
                console.log("Desktop: Sidebar open, logo hidden");
            }
        }

        // Run on load
        updateSidebar();

        // Update on resize
        window.addEventListener("resize", updateSidebar);

        // Handle toggle for desktop only
        toggleBtn.addEventListener("click", function () {
            const isMobile = window.matchMedia("(max-width: 639px)").matches;
            if (isMobile) return; // Don't toggle on mobile

            const isCollapsed = sidebar.classList.toggle("collapsed");
            sidebar.classList.toggle("w-20");
            sidebar.classList.toggle("w-64");
            header.classList.toggle("sidebar-collapsed");

            if (mainContent) {
                mainContent.classList.toggle("ml-20");
                mainContent.classList.toggle("ml-64");
            }

            icon.className = isCollapsed
                ? "ri-arrow-right-s-line ri-xl transition-transform duration-300"
                : "ri-arrow-left-line ri-xl transition-transform duration-300";

            console.log(isCollapsed ? "Sidebar collapsed, logo visible" : "Sidebar open, logo hidden");
        });

        // Scroll animation
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.scroll-animate, .nav-link').forEach((el, index) => {
            el.style.animationDelay = `${index * 50}ms`;
            observer.observe(el);
        });
    });
</script>
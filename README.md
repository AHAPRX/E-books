  <h1>📚 E_Books</h1>
  <p><strong>E_Books</strong> is a modern and responsive web application built using <strong>PHP</strong>, <strong>MySQL</strong>, <strong>Tailwind CSS</strong>, and <strong>JavaScript</strong>. It provides an interactive digital library platform where users can register, explore books by categories or authors, participate in reading competitions, and more. Admins can manage books and users through a dedicated panel, while smooth transitions and a dark mode theme enhance the user experience.</p>

  <h2>🛠️ Features</h2>
  <ul>
    <li>🔐 <strong>User Authentication</strong> – Secure sign-up and login with email verification using PHPMailer</li>
    <li>📖 <strong>Dynamic Book Listings</strong> – Display books with real-time data from the database</li>
    <li>📚 <strong>Category & Author Filters</strong> – Filter books by category and author for quick discovery</li>
    <li>🏆 <strong>Reading Competitions</strong> – Compete with others and track your reading progress</li>
    <li>📄 <strong>Admin Panel</strong> – Manage books, users, and content from a secure admin interface</li>
    <li>⚡ <strong>Smooth Animations</strong> – Enhanced with Tailwind transitions and effects</li>
    <li>📱 <strong>Responsive Design</strong> – Fully mobile-friendly and accessible across devices</li>
    <li>🌙 <strong>Dark Mode Ready</strong> – Elegant UI with dark mode support using Tailwind</li>
  </ul>

  <h2>🧩 Tech Stack</h2>
  <ul>
    <li><strong>Frontend:</strong> HTML, Tailwind CSS, JavaScript</li>
    <li><strong>Backend:</strong> PHP</li>
    <li><strong>Database:</strong> MySQL</li>
    <li><strong>Mailer:</strong> PHPMailer</li>
  </ul>

  <h2>📂 Folder Structure</h2>
  <pre><code>E_Books/
├── Auth/         # Login, signup, email verification
├── Config/       # Database configuration and environment setup
├── User/         # User-side pages (home, dashboard, book view)
├── Uploads/      # Uploaded files and images
├── Admin/        # Admin dashboard and controls
├── Vendor/       # Composer dependencies (e.g., PHPMailer)
├── index.php     # Main landing page
├── .env          # Environment variables (DB, SMTP credentials)
├── .gitignore    # Git ignored files
└── README.md     # Project documentation</code></pre>

  <h2>🖥️ Setup Instructions</h2>
  <h3>1. Clone the Repository</h3>
  <pre><code>git clone https://github.com/RoaraxAli/E_Books.git
cd E_Books</code></pre>

  <h3>2. Import the Database</h3>
  <p>Use <code>finalbook1.sql</code> to import into MySQL using phpMyAdmin or CLI.</p>

  <h3>3. Configure Database</h3>
  <p>Update <code>/Config/db.php</code>:</p>
  <pre><code>$host = "localhost";
$username = "root";
$password = "";
$database = "finalbook1";</code></pre>

  <h3>4. Configure PHPMailer (Optional)</h3>
  <p>Set your Gmail credentials either in the <code>.env</code> file or directly in <code>signups.php</code> to enable email verification via PHPMailer.</p>
  <p><strong>Example <code>.env</code> file:</strong></p>
  <pre><code>GMAIL_USERNAME=your-email@gmail.com
GMAIL_APP_PASSWORD=your-app-password




# 🎓 Student Wellbeing & Success Portal

A full-stack web application built with **PHP** and **MySQL** to support student mental health, academic success, and campus resources — featuring session-based authentication, an AI-powered chatbot, a research paper search tool, and a complete admin dashboard.

---

## 🚀 Features

### 👤 Authentication System
- Secure **session-based login and registration** for students
- Passwords hashed using `password_hash()` (bcrypt)
- Session validation on every protected page
- Clean logout that destroys session data

### 🤖 AI Chatbot (Ollama Integration)
- Integrated a locally-running **Ollama** LLM for conversational AI support
- Students can chat with the AI for guidance, stress management tips, and academic advice
- PHP backend sends prompts to the Ollama REST API and streams responses back to the frontend

### 🛠️ Admin Dashboard
- Separate admin login with role-based access control
- View and manage registered students
- Monitor portal activity
- Full CRUD operations on student records via a clean dashboard UI

---

## 🧰 Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML, CSS, JavaScript |
| Backend | PHP (procedural) |
| Database | MySQL |
| AI Integration | Ollama (local LLM API) |
| Auth | PHP Sessions (`$_SESSION`) |
| Server | Apache / XAMPP (local) |

---

## 📁 Project Structure

```
student-wellbeing-portal/
├── index.php               # Landing page
├── login.php               # Student login
├── register.php            # Student registration
├── logout.php              # Session destroy
├── dashboard.php           # Student dashboard (protected)
├── chatbot.php             # AI chatbot interface
├── research.php            # Research paper search
├── admin/
│   ├── login.php           # Admin login
│   ├── dashboard.php       # Admin panel
│   └── manage_students.php # CRUD for student records
├── includes/
│   ├── db.php              # Database connection
│   ├── auth.php            # Session auth helpers
│   └── ollama.php          # Ollama API integration
├── assets/
│   ├── css/
│   └── js/
└── sql/
    └── schema.sql          # Database schema
```

---

## 🗄️ Database Schema

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## ⚙️ Setup & Installation

### Prerequisites
- PHP 8.x
- MySQL 5.7+
- XAMPP / WAMP / Laragon (or any Apache server)
- [Ollama](https://ollama.com/) installed locally

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/aribamalickprvt/student-wellbeing-portal.git
   cd student-wellbeing-portal
   ```

2. **Set up the database**
   - Open phpMyAdmin or MySQL CLI
   - Create a database: `CREATE DATABASE wellbeing_portal;`
   - Import the schema:
     ```bash
     mysql -u root -p wellbeing_portal < sql/schema.sql
     ```

3. **Configure database connection**

   Edit `includes/db.php`:
   ```php
   <?php
   $host = 'localhost';
   $db   = 'wellbeing_portal';
   $user = 'root';
   $pass = '';
   $conn = new mysqli($host, $user, $pass, $db);

   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }
   ```

4. **Start Ollama**
   ```bash
   ollama run llama3
   ```
   Make sure Ollama is running at `http://localhost:11434`

5. **Start the local server**
   - Place the project in your XAMPP `htdocs` folder
   - Start Apache and MySQL from XAMPP Control Panel
   - Visit: `http://localhost/student-wellbeing-portal`

---

## 🔐 How Authentication Works

```php
// Login flow
session_start();
$password_input = $_POST['password'];
$user = fetchUserByEmail($email); // query DB

if (password_verify($password_input, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role']    = $user['role'];
    header("Location: dashboard.php");
}

// Protecting pages
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
```

---

## 🤖 Ollama Chatbot Integration

```php
// includes/ollama.php
function askOllama($prompt) {
    $url  = 'http://localhost:11434/api/generate';
    $data = json_encode([
        'model'  => 'llama3',
        'prompt' => $prompt,
        'stream' => false
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS,     $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $decoded = json_decode($response, true);
    return $decoded['response'] ?? 'No response from AI.';
}
```

---

## 📸 Screenshots

> *(Add screenshots of the login page, student dashboard, chatbot, and admin panel here)*

---

## 🔮 Future Improvements

- [ ] Migrate to a PHP framework (Laravel)
- [ ] Add JWT-based API authentication
- [ ] Deploy to a live server (cPanel / DigitalOcean)
- [ ] Add mood tracking and wellbeing analytics dashboard
- [ ] Email notifications for admin alerts

---

## 👩‍💻 Author

**Ariba Amir**  
BS Software Engineering — University of Lahore  
[github.com/aribamalickprvt](https://github.com/aribamalickprvt)

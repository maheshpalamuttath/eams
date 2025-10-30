# 📚 Event Attendance Management System (EAMS)

The **Event Attendance Management System (EAMS)** is a lightweight PHP–MySQL web application designed to manage and track attendance for library and campus events. It helps librarians and administrators efficiently record, generate, and store attendance reports for various programs, workshops, and academic events.


## 🌟 Features

- ✅ **Event-Based Attendance Tracking** — Manage and record attendance for multiple events.  
- 👥 **Koha Integration** — Verifies users based on the Koha Library Management System database.  
- 🧾 **PDF Report Generation** — Automatically generates attendance reports in PDF format.  
- 🔒 **Admin Login Panel** — Secure access for event organizers and library administrators.  
- 📅 **Date-wise Report Sorting** — Displays the latest reports first for easy access.  
- ⚙️ **Configurable & Lightweight** — Easy to install and customize for institutional needs.

---

## 🖥️ Requirements

Since this system is typically installed on a **Koha server**, most dependencies such as Apache and MariaDB/MySQL are already available.  
You only need to install PHP and the required extensions.

---

## ⚙️ Install PHP and Required Extensions

```bash
sudo apt update
sudo apt install php php-mysqli php-mbstring php-dom php-gd php-zip git unzip vim -y
````

This installs:

* `php` – Core PHP interpreter
* `php-mysqli` – MySQL database connection support
* `php-mbstring` – Multibyte string handling
* `php-dom` – DOM document processing
* `php-gd` – Image handling (used in PDF generation)
* `php-zip` – Compression and ZIP support
* `git` – For cloning the repository
* `unzip` – For extracting archives if needed
* `vim` – For editing configuration files

---

### 1. Create Database User

```bash
sudo mysql -uroot -p
```

Then run the following commands inside MySQL:

```sql
CREATE DATABASE eams;
CREATE USER 'eams'@'localhost' IDENTIFIED BY 'eams123';
GRANT ALL PRIVILEGES ON *.* TO 'eams'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
QUIT;
```

---

### 2. Clone the Repository

```bash
cd /var/www/html
sudo git clone https://github.com/maheshpalamuttath/eams.git
```

---

### 3. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/html/eams
sudo find /var/www/html/eams -type d -exec chmod 755 {} \;
sudo find /var/www/html/eams -type f -exec chmod 644 {} \;
```

---

### 4. Update Configuration

Edit the configuration file:

```bash
sudo vim /var/www/html/eams/config.php
```

Update your database credentials:

```php
// Database configuration
$servername = "localhost";
$username = "eams";
$password = "eams123";
$dbname = "eams";
$koha_dbname = "koha_library";
```

---

### 5. Import Database

```bash
sudo su
mysql -uroot -pmysqlrootpassword eams < /var/www/html/eams/DB_sample/eams.sql
```

---

### 6. Access the Application

Open your browser and visit:

```
http://localhost/eams
```

**Default Login Credentials**

```
Username: admin
Password: admin
```

---

## 📄 Usage

1. Log in using admin credentials.
2. Add new events or view existing ones.
3. Take attendance by scanning or entering user IDs (validated from Koha).
4. Generate PDF reports automatically stored in the `/report/` folder.
5. Download or print the attendance report when needed.

---

## 🧩 Folder Structure

```
/eams
 ├── config.php               # Database connection file
 ├── login.php                # Admin login page
 ├── take_attendance.php      # Attendance submission page
 ├── generate_pdf.php         # PDF report generator
 ├── report/                  # Stores generated attendance reports
 ├── DB_sample/eams.sql       # Sample database structure
 └── assets/                  # Stylesheets and JS files
```

---

## 🏛️ Use Case

This system was developed for **Fr. Francis Sales Library, Sacred Heart College (Autonomous), Kochi**, to digitally manage participation records of library events, reading programs, and academic workshops.
It simplifies documentation and reporting for **NAAC** and institutional quality records.

---

## 🤝 Contributors

**Author:** [Mahesh Palamuttath](https://maheshpalamuttath.info)
**Institution:** Sacred Heart College (Autonomous), Kochi
```

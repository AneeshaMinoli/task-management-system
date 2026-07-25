##### **Task Management System**



A full-stack task management web application built with PHP and MySQL, featuring user authentication, a dynamic dashboard, and full task CRUD functionality.



Live Demo:https://taskmanagerbyminoli.atwebpages.com



Demo Credentials: 

\- Username: admin

\- Password: admin123



(Or register your own account on the login page.)





###### **Features**



**Core Features**

1. User authentication (login \& registration) with hashed passwords
2. Interactive dashboard with an overview of task activity
3. Add, edit, and delete tasks
4. Search tasks by title
5. Filter tasks by status (Pending / Completed)
6. Task fields: Title, Description, Due Date, Status
7. Fully responsive design (desktop, tablet, mobile)



**Bonus Features**

1. Dark mode toggle with saved preference
2. Progress ring charts showing Pending / Completed / Overall task stats
3. Pagination on the task list
4. Toast notifications for add/edit/delete actions
5. Interactive schedule calendar with month navigation and per-day task view
6. Notification bell showing tasks due within 3 days
7. Per-user task lists — each account only sees its own tasks
8. Login/Register with a CSS flip-card animation\*\*
9. Deployed online



**Tech Stack**

1. Backend: PHP (PDO for database access)
2. Database: MySQL
3. Frontend: HTML5, CSS3 (custom, no framework), JavaScript (vanilla)
4. Hosting: AwardSpace (free tier)



**Project Structure**



task\_manager/

auth/ index.php    # Combined login + register (flip card)

config/ db.php        # Database connection

includes/ header.php          # Sidebar, topbar, notification bell

&#x20;       / footer.php          # Closing layout, modals, toast container

&#x20;assets/css/style.css

&#x20;     js/script.js

&#x20;     img/

dashboard.php            # Overview: stats, schedule, rings, recent activity

my\_tasks.php              # Search, filter, table, pagination

add\_task.php

edit\_task.php

delete\_task.php

database/task\_manager.sql



**Local Setup on your device**



1\. Install XAMPP

2\. Clone or copy this project into your **htdocs** folder in XAMPP Folder you find in local disk

3\. Start Apache and MySQL from the XAMPP control panel

4\. Open phpMyAdmin, create a database named **task\_manager**, and import **database/task\_manager.sql**

5\. Update **config/db.php** with your local database credentials

6\. Visit **http://localhost/task_manager/auth/index.php** in your browser



Author



Aneesha Minoli Wijesekara Subasinghe

Higher Diploma in Computing and Software Engineering — ICBT Campus, Kandy (affiliated with Cardiff Metropolitan University)


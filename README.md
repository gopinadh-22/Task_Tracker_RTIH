### Task_Tracker

## Section 1:
A simple multi-user Task Tracker built for the RTIH Intern – IT technical assignment. Users register, log in, and manage their own tasks (add, edit, delete, change status, search and filter). An admin role can view and remove any task.

## Section 2:
Stack: core PHP 8 (PDO), MySQL, HTML and Bootstrap 5, with a little JavaScript.
+---------+----------+-----------------------------------------------------------------------+
| Level   | Status   | What is included                                                      |
+---------+----------+-----------------------------------------------------------------------+
| Level 1 | Complete | Add, list, edit and delete tasks (with a confirmation prompt) and     |
|         |          | success messages                                                      |
+---------+----------+-----------------------------------------------------------------------+
| Level 2 | Complete | Three statuses (Pending, In Progress, Completed), status change,      |
|         |          | search by title, status filter, frontend and backend validation with  |
|         |          | friendly errors                                                       |
+---------+----------+-----------------------------------------------------------------------+
| Level 3 | Complete | Registration and login, hashed passwords, login-protected pages,      |
|         |          | ownership checks, prepared statements, output escaping, CSRF tokens,  |
|         |          | user and admin roles                                                  |
+---------+----------+-----------------------------------------------------------------------+
PHP 8.0 or higher (developed on PHP 8.5) with the pdo_mysql and mbstring extensions enabled
MySQL 8 (or higher)
A web browser, and an internet connection (Bootstrap is loaded from a CDN)
Git, if you clone the repository

Enabling the PHP extensions (Windows): if php --ini shows no loaded configuration file, copy php.ini-development to php.ini in the PHP folder. In php.ini, set extension_dir to the full path of the ext folder, and remove the ; in front of extension=pdo_mysql and extension=mbstring. Open a new terminal and run php -m to confirm both appear in the list.

# Section 3: Setup
Step 1: Clone or unzip the project, then open a terminal in the project folder.
Step 2: Import the database:
mysql -u root -p -e "source database.sql"

This creates the database task_tracker_db, the two tables, and sample data. Importing again resets the data to the sample data. 

Step 3: Copy the config sample, then edit your copy with your own MySQL username and password:

copy config\db.sample.php config\db.php

(On macOS or Linux use cp config/db.sample.php config/db.php.)
The file config/db.php is ignored by Git and must never be committed. 
Step 4: Make sure a logs folder exists in the project root. The app creates it automatically if it can; if not, create it yourself. Errors are written there.

Start 5: Start PHP's built-in server. The -t public option means only the public folder is reachable from the browser:

php -S localhost:3000 -t public

Step 6: Open http://localhost:3000/login.php and log in with a test account

# Setup
1. Clone or unzip the project, then open a terminal in the project folder.
2. Import the database:
   mysql -u root -p -e "source database.sql"

# Section 4: Project Structure
.
├── config/
│   └── db.sample.php        Sample settings (copy to db.php)
├── includes/
│   ├── db.php               PDO connection function
│   ├── helpers.php          Escaping, sessions, flash messages, CSRF, roles, validation
│   └── header.php           Shared page top and navbar
├── public/                  The only folder served to the browser
│   ├── index.php            Task list, search and filter
│   ├── task_create.php      Add a task
│   ├── task_edit.php        Edit a task
│   ├── task_delete.php      Delete a task (POST only)
│   ├── task_status.php      Quick status change (POST only)
│   ├── register.php, login.php, logout.php
│   ├── admin_tasks.php      Admin-only list of all tasks
│   ├── css/style.css
│   └── js/app.js
├── screenshots/
├── database.sql             Schema and sample data
└── README.md


# logins, roles, security and database sections

## Section 5: Test logins

Create a table with the columns Role, Email, Password, and fill in one row per sample user from database.sql:

  1. Copy the emails exactly as they are in your SQL file, so the admin row uses your current admin email.
  2. For creating the hash use the command - "php -r "echo password_hash('Test@123', PASSWORD_DEFAULT);" for inserting the table password data of users/admin.
  3. For the password, write the plain fake password that matches the hash (all three users share one hash, so it’s probably Test@123). Check it by logging in with each          account before you write it down.
  4. The used accounts are throwaway test accounts created for this project, and that importing database.sql resets them.

## Section 6: Roles and permissions
+----------------------------------------------------+------+-------+
| Action                                             | User | Admin |
+----------------------------------------------------+------+-------+
| Register, log in, log out                          |  ✔   |   ✔   |
| Create, view, edit, delete own tasks               |  ✔   |   ✔   |
| Change status of own tasks                         |  ✔   |   ✔   |
| View all users’ tasks (admin page)                 |  ✘   |   ✔   |
| Delete any task                                    |  ✘   |   ✔   |
| Edit or change status of another user’s task       |  ✘   |   ✘   |
+----------------------------------------------------+------+-------+

1. Every query for tasks includes AND user_id = ?, and the id comes from the server-side session, never from the request.
2. The role is set to 'user' by the server at registration, so nobody can sign up as an admin.
3. The role is read from the database on each request, so changing it takes effect immediately.
4. Admins can see and remove tasks for moderation, but can’t edit other people’s work.

## Section 7: Security measures

1. passwords hashed with password_hash() and checked with password_verify()
2. prepared statements (PDO) for every query that includes user input
3. output escaped with a helper e() that wraps htmlspecialchars()
4. CSRF token on every state-changing form, checked with hash_equals()
5. login required on all task pages, and the session id regenerated at login
6. server-side validation, with a status whitelist, on top of the browser checks
7. errors logged to logs/, never shown to the user
8. database credentials kept in config/db.php, which is git-ignored, with only a sample file committed

## Section 8: Database design
User and Task tables: users_table (id, name, email, password hash, role, created date) and task_table (id, user id, title, description, status, created date).

1. user_id is a foreign key to the users table, so every task has an owner, and ON DELETE CASCADE removes a user’s tasks with the user.
2. email is UNIQUE, so two accounts can’t share an address.
3. status and role are ENUMs, so the database itself rejects invalid values.
4. password_hash is VARCHAR(255), so it fits the output of password_hash() even if the algorithm changes.

## Section 9: Test cases and results

Environment: Windows, PHP 8.5.12 built-in server, local MySQL, Chrome browser.
+----+-------------------------------+-----------------------------------+-----------------------------------+-------------+--------+
| #  | Test case                     | Steps                             | Expected result                   | Actual      | Result |
+----+-------------------------------+-----------------------------------+-----------------------------------+-------------+--------+
| 1  | Add a task with valid data    | Add Task, enter a title and       | Task is saved, green message,     | As expected | Pass   |
|    |                               | description, Save                 | task appears in the list          |             |        |
+----+-------------------------------+-----------------------------------+-----------------------------------+-------------+--------+
| 2  | Empty title is rejected       | Submit the form with an empty     | Browser message, and a server     | As expected | Pass   |
|    |                               | title, then with only spaces      | error "Title is required." when   |             |        |
|    |                               |                                   | bypassed. Nothing saved           |             |        |
+----+-------------------------------+-----------------------------------+-----------------------------------+-------------+--------+
| 3  | Edit and change status        | Edit a task, change title and     | Values update, success message,   | As expected | Pass   |
|    |                               | status. Then change status from   | badge color changes               |             |        |
|    |                               | the list dropdown                 |                                   |             |        |
+----+-------------------------------+-----------------------------------+-----------------------------------+-------------+--------+
| 4  | Delete with confirmation      | Click Delete, choose Cancel,      | Cancel keeps the task. OK         | As expected | Pass   |
|    |                               | then repeat and choose OK         | removes it with a success message |             |        |
+----+-------------------------------+-----------------------------------+-----------------------------------+-------------+--------+
| 5  | Search and status filter      | Search by part of a title, filter | Only matching tasks are listed.   | As expected | Pass   |
|    |                               | by each status, then both         | Empty results show a friendly msg |             |        |
+----+-------------------------------+-----------------------------------+-----------------------------------+-------------+--------+
| 6  | Logged-out access             | Log out, open index.php and       | Redirected to login with a        | As expected | Pass   |
|    |                               | task_create.php directly          | message                           |             |        |
+----+-------------------------------+-----------------------------------+-----------------------------------+-------------+--------+
| 7  | Password storage              | Run SELECT password_hash FROM     | Every value is a bcrypt hash      | As expected | Pass   |
|    |                               | users_table;                      | starting $2y$, with no plain text |             |        |
+----+-------------------------------+-----------------------------------+-----------------------------------+-------------+--------+
| 8  | Another user's task by URL    | As one user, open task_edit.php   | "Task not found." and the task    | As expected | Pass   |
|    |                               | ?id= with another user's task id  | is unchanged                      |             |        |
+----+-------------------------------+-----------------------------------+-----------------------------------+-------------+--------+
| 9  | Forged delete and status      | Change hidden id in the form to   | "Task not found." and the task    | As expected | Pass   |
|    | requests                      | another user's task, then submit  | is unchanged                      |             |        |
+----+-------------------------------+-----------------------------------+-----------------------------------+-------------+--------+
| 10 | CSRF protection               | Remove or change hidden csrf_     | 403 "Invalid or expired form" and | As expected | Pass   |
|    |                               | token field and submit each form  | nothing changes                   |             |        |
+----+-------------------------------+-----------------------------------+-----------------------------------+-------------+--------+
| 11 | Admin page access             | As normal user, open admin_tasks  | "Access denied." (403) for user,  | As expected | Pass   |
|    |                               | .php. As logged-out, open it      | redirect to login when logged out |             |        |
+----+-------------------------------+-----------------------------------+-----------------------------------+-------------+--------+
| 12 | Database error handling       | Put a wrong password in config/   | Only a generic message is shown.  | As expected | Pass   |
|    |                               | db.php and open the app           | Real error in logs/php-error.log  |             |        |
+----+-------------------------------+-----------------------------------+-----------------------------------+-------------+--------+
| 13 | Output escaping               | Save a task titled                | The text is displayed as plain    | As expected | Pass   |
|    |                               | <script>alert(1)</script>         | text and no script runs           |             |        |
+----+-------------------------------+-----------------------------------+-----------------------------------+-------------+--------+
| 14 | Tampered status value         | Change a status option's value to | "Please choose a valid status."   | As expected | Pass   |
|    |                               | Hacked with DevTools and submit   | and nothing saved                 |             |        |
+----+-------------------------------+-----------------------------------+-----------------------------------+-------------+--------+

## Section 10: Bugs found and fixed

## Bugs found and fixed

1. **Real database password file was not protected by Git.**
   - *Symptom:* `git check-ignore -v config/db.php` printed nothing, so the file holding my MySQL password could have been committed. A scratch helper file also reached GitHub.
   - *Cause:* I had put the ignore rule in the `.gitignore` inside my virtual environment folder, which only applies to files inside that folder, and the project root had no `.gitignore`. A second ignore rule had a typo (an extra leading dot).
   - *Fix:* created a `.gitignore` in the project root, corrected the typo, ran `git rm --cached` on the files already tracked, and re-checked with `git check-ignore` and `git status`.
   - *Lesson:* never assume an ignore rule works; verify it, and check the repository on GitHub.

2. **Edit form silently saved nothing.**
   - *Symptom:* after adding task statuses, clicking Update on the Edit page reloaded the page with no message and no change.
   - *Cause:* the server validated a `status` field, but the Edit form had no status dropdown, so the value was always empty, the validation failed, and there was no field to display the error under.
   - *Fix:* added the Status dropdown to the Edit form, built from the same `TASK_STATUSES` list the server checks.
   - *Lesson:* a validation error with nowhere to display is invisible; the form and the validator must be changed together.

3. **Status values didn't match the interface.**
   - *Symptom:* a task showed `In_Progress`, while the interface, the PDF, and the filter use `In Progress`.
   - *Cause:* my table's `ENUM` was defined with an underscore.
   - *Fix:* altered the column to allow both values, updated the existing row, then altered it again to keep only the correct value, and updated `database.sql`. A direct change would have failed because an existing row would no longer fit the new `ENUM`.

4. **Database connection code leaked errors and lived in the config files.**
   - *Symptom:* my config files printed "Connected successfully" and, on failure, the raw MySQL error message.
   - *Cause:* I had pasted a tutorial's connection example into the config files, and it echoed `$e->getMessage()`, which can reveal usernames and hosts.
   - *Fix:* the config files now only return an array of settings. One function in `includes/db.php` creates the connection, logs the real error to a file outside the web root, and shows only a generic message.

5. **SQL import failed with a duplicate-key error.**
   - *Symptom:* `ERROR 1062 Duplicate entry 'alice@example.com' for key 'users_table.email'` at line 55 of `database.sql`.
   - *Cause:* the script inserted sample users into tables that still held earlier rows, so the unique email rule rejected them. My first version used `CREATE TABLE IF NOT EXISTS`, which keeps old data.
   - *Fix:* added `DROP TABLE IF EXISTS` for the tasks table first and then the users table (the order matters because of the foreign key), so the file can be imported repeatedly.

6. **The web server exposed the whole project folder.**
   - *Symptom:* my pages were reachable at `localhost:3000/public/index.php`, which meant `config/` and `includes/` were also reachable by URL.
   - *Cause:* I started `php -S` from the project root.
   - *Fix:* started the server with `php -S localhost:3000 -t public`, so only the `public` folder is served and the config, includes, and logs folders can't be requested.

7. **PHP could not connect to MySQL at all.**
   - *Symptom:* `php -m` didn't list `pdo_mysql` or `mbstring`.
   - *Cause:* PHP had no `php.ini` (`php --ini` showed "Loaded Configuration File: (none)"), so the extensions were never enabled.
   - *Fix:* copied `php.ini-development` to `php.ini`, set `extension_dir` to the full path of my `ext` folder, and enabled both extensions. I confirmed it with `php -m`.
  

## Section 11: Known limitations

1. No limit on repeated failed login attempts (no rate limiting).
2. The search and filter reset after a status change or a delete from the list.
3. The session cookie is not marked Secure, because the project runs on plain HTTP locally. It should be enabled when the site is served over HTTPS.
4. No password reset, email verification, or account deletion.
5. Admins cannot assign tasks to other users.
6. The optional bonus features (pagination, dashboard summary, due dates or priority) are not implemented.
7. Errors are hidden from the browser by code. In a production setup, display_errors should also be turned off in php.ini, so that errors raised before the code runs are hidden too.

Section 12: AI assistant note
Note: I used Claude (Sonnet 5.5) as a helping tool.

# Fresh-install test (end to end)
1. Push everything. Run git status (it should be clean), then git push.
2. Clone into a new folder, away from your working copy:
   git clone <your-repo-url> F:\fresh_test
   cd F:\fresh_test

## Sample Data for the database
+----+-------+-------------------+-------+
| id | name  | email             | role  |
+----+-------+-------------------+-------+
|  1 | Gopi  | admin@gmail.com   | admin |
|  2 | Alice | alice@example.com | user  |
|  3 | Bob   | bob@example.com   | user  |
+----+-------+-------------------+-------+
+----+-------+-------------------+-------+--------------------------------------------------------------+
| id | name  | email             | role  | password_hash                                                |
+----+-------+-------------------+-------+--------------------------------------------------------------+
|  1 | Gopi  | admin@gmail.com   | admin | $2y$12$tAe2SgmzMumoonTpp4hFRO8UjcrrsU2f05eQaOY30goEMO3eA/ZZi |
|  2 | Alice | alice@example.com | user  | $2y$12$tAe2SgmzMumoonTpp4hFRO8UjcrrsU2f05eQaOY30goEMO3eA/ZZi |
|  3 | Bob   | bob@example.com   | user  | $2y$12$tAe2SgmzMumoonTpp4hFRO8UjcrrsU2f05eQaOY30goEMO3eA/ZZi |
+----+-------+-------------------+-------+--------------------------------------------------------------+
+----+---------+------------------------+---------------------------------------+-------------+
| id | user_id | title                  | description                           | status      |
+----+---------+------------------------+---------------------------------------+-------------+
|  1 |       2 | Prepare project report | Write the weekly progress report.     | Pending     |
|  2 |       3 | Fix login bug          | Session not regenerating after login. | In Progress |
|  3 |       2 | Buy study material     | PHP and MySQL reference book.         | Completed   |
+----+---------+------------------------+---------------------------------------+-------------+
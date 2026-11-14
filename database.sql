-- users for the Level 3 login and tasks for the main feature
-- Two tables required: users, tasks
-- users data will be stored for login credentials
-- tasks are stored w.r.to users.


-- Column	Type	Rules
-- id	unsigned integer	primary key, auto increment
-- name	VARCHAR(100)	required
-- email	VARCHAR(255)	required, must be unique
-- password_hash	VARCHAR(255)	required
-- role	ENUM of user and admin	required, default user
-- created_at	TIMESTAMP	required, default is the current time


create database if not exists task_tracker_db;


use task_tracker_db;

DROP TABLE IF EXISTS task_table;
DROP TABLE IF EXISTS users_table;

-- user's table
create table if not exists users_table (
    id int unsigned primary key auto_increment,
    name VARCHAR(100) not null,
    email VARCHAR(255) not null unique,
    password_hash VARCHAR(255) not null,
    role ENUM('user','admin') not null default 'user',
    created_at TIMESTAMP not null default current_timestamp
); 


-- Column	Type	Rules
-- id	unsigned integer	primary key, auto increment
-- user_id	unsigned integer	required, foreign key to users.id
-- title	VARCHAR(150)	required
-- description	TEXT	optional (allows NULL)
-- status	ENUM of Pending, In Progress, Completed	required, default Pending
-- created_at	TIMESTAMP	required, default is the current time

create table if not exists task_table (
    id int unsigned primary key auto_increment,
    user_id int unsigned not null,
    title varchar(150) not null,
    description text,
    status enum('Pending','In Progress','Completed') not null default 'Pending',
    created_at TIMESTAMP not null default current_timestamp,
    key idx_tasks_user (user_id),
    constraint fk_task_users foreign key (user_id) references users_table (id) on delete cascade 
);

-- level-1 : add, list, edit, delete -- crud operations

-- 1. Insert a user's first (if users_table is currently empty)

USE task_tracker_db;

INSERT INTO users_table (name, email, password_hash, role) VALUES
('Gopi', 'admin@gmail.com','$2y$12$tAe2SgmzMumoonTpp4hFRO8UjcrrsU2f05eQaOY30goEMO3eA/ZZi','admin'),
('Alice', 'alice@example.com', '$2y$12$tAe2SgmzMumoonTpp4hFRO8UjcrrsU2f05eQaOY30goEMO3eA/ZZi', 'user'),
('Bob',   'bob@example.com',   '$2y$12$tAe2SgmzMumoonTpp4hFRO8UjcrrsU2f05eQaOY30goEMO3eA/ZZi', 'user');


-- select * from users_table;

-- select * from task_table;

-- 1. Insert a user first (if users_table is currently empty)
USE task_tracker_db;

INSERT INTO task_table (user_id, title, description, status) VALUES
(2, 'Prepare project report', 'Write the weekly progress report.', 'Pending'),
(3, 'Fix login bug', 'Session not regenerating after login.', 'In Progress'),
(2, 'Buy study material', 'PHP and MySQL reference book.', 'Completed');

-- UPDATE task_table SET title = 'Prepare project report' WHERE id = 1;

-- DELETE FROM task_table;

-- alter table task_table auto_increment = 1; 

-- SELECT id, user_id, status FROM task_table;


UPDATE users_table SET role = 'admin' WHERE email = 'gopinath.napa@gmail.com';
SELECT id, name, email, role FROM users_table;


SELECT id, name, email, role, password_hash FROM users_table;
SELECT id, user_id, title, description, status FROM task_table;
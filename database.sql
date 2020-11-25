CREATE DATABASE IF NOT EXISTS api_rest_laravel;

USE api_rest_laravel;

CREATE TABLE users(
    id int(255) auto_increment NOT NULL,
    name varchar(50) NOT NULL,
    surname varchar(100) NOT NULL,
    role varchar(20) NOT NULL,
    email varchar(255) NOT NULL,
    password varchar(255) NOT NULL,
    description text,
    image varchar(255),
    created_at datetime DEFAULT NULL,
    updated_at datetime DEFAULT NULL,
    remember_token varchar(255),

    CONSTRAINT pk_users PRIMARY KEY(id)
)ENGINE=InnoDb;

CREATE TABLE categories(
    id int(255) auto_increment NOT NULL,
    name varchar(100) NOT NULL,
    created_at datetime DEFAULT NULL,
    updated_at datetime DEFAULT NULL,

    CONSTRAINT pk_categories PRIMARY KEY(id)
)ENGINE=InnoDb;

CREATE TABLE posts(
    id int(255) auto_increment NOT NULL,
    user_id int(255) NOT NULL,
    category_id int(255) NOT NULL,
    title varchar(255) NOT NULL,
    content TEXT NOT NULL,
    image varchar(255),
    created_at datetime DEFAULT NULL,
    updated_at datetime DEFAULT NULL,

    CONSTRAINT pk_posts PRIMARY KEY(id),
    CONSTRAINT fk_post_user FOREIGN KEY(user_id) REFERENCES users(id),
    CONSTRAINT fk_post_category FOREIGN KEY(category_id) REFERENCES categories(id)
)ENGINE=InnoDb;


/* INSERCCION DE INFORMACION */

#users
INSERT INTO users VALUES(null,'admin','admin1','ROLE_ADMIN','admin@admin.com','admin','admin descripton',null,'2020-02-12 00:00:00','2020-02-12 00:00:00',null);


#categories
INSERT INTO categories VALUES(NULL, 'ordenadores','2020-02-12 00:00:00','2020-02-12 00:00:00');
INSERT INTO categories VALUES(NULL, 'Moviles y Tablets','2020-02-12 00:00:00','2020-02-12 00:00:00');

#posts
INSERT INTO posts VALUES(NULL, 1, 2,'Samsung Galaxy S8','Caracteristicas Samsung Galaxy S8',null,'2020-02-12 00:00:00','2020-02-12 00:00:00');

INSERT INTO posts VALUES(NULL, 1, 1,'Asus gamer','Caracteristicas Asus gamer',null,'2020-02-12 00:00:00','2020-02-12 00:00:00');

INSERT INTO posts VALUES(NULL, 1, 1,'Toshiba Satellite','Caracteristicas Toshiba Satellite',null,'2020-02-12 00:00:00','2020-02-12 00:00:00');

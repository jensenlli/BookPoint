create table if not exists admins  (
    id int primary key AUTO_INCREMENT,
    username varchar(100) not null,
    password varchar(100) not null,
    email varchar(50) not null,
    firstName varchar(50) not null,
    lastName varchar(100) not null
) engine=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE if not exists authors (
    id int primary key AUTO_INCREMENT,
    name varchar(255) not null,
    authorAddedBy int not null,
    foreign key (authorAddedBy) references admins (id) on delete cascade
) engine=MyISAM DEFAULT CHARSET=latin1 ;

CREATE TABLE if not exists genres(
    id int primary key AUTO_INCREMENT,
    name varchar(255) not null,
    genreAddedBy int not null,
    foreign key (genreAddedBy) references admins (id) on delete cascade
) engine=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE if not exists book (
    id int primary key AUTO_INCREMENT,
    ISBN int not null,
    name varchar(255) not null,
    rating long not null,
    yearPub int not null,
    Publisher varchar(255),
    img TEXT not null,
    bookAddedBy int not null,
    authorId int not null,
    genreId int not null,
    foreign key (bookAddedBy) references admins (id) on delete cascade,
    foreign key (authorId) references authors (id) on delete cascade,
    foreign key (genreId) references genres (id) on delete cascade
) engine=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `digitalbook`.`transactions` (
  `id` INT NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`id`));

insert into admins values (1, 'anna', 'password', 'anna@gmail.com', 'Anna', 'Lyakhova');
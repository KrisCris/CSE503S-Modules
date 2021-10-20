CREATE DATABASE module5 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
use module5;
create table user
(
    id mediumint auto_increment not null,
    username varchar(50) not null,
    password varchar(200) not null,
    primary key (id)
) engine = InnoDB default CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table event
(
  id mediumint auto_increment not null,
  uid mediumint not null,
  title varchar(200),
  detail varchar(500),
  location varchar(500),
  color varchar(100),
  isFullDay boolean default false,
  doRepeat char(1) default '',
  start timestamp not null,
  end timestamp,
  primary key (id),
  foreign key (uid) references user(id)
) engine = InnoDB default CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
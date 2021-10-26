use module5;
create table user
(
    id mediumint auto_increment not null,
    username varchar(50) not null,
    password varchar(200) not null,
    token varchar(100),
    primary key (id)
) engine = InnoDB default CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table category
(
    id mediumint auto_increment not null,
    uid mediumint not null,
    name varchar(100) not null,
    color varchar(100),
    primary key (id),
    foreign key (uid) references user(id)
) engine = InnoDB default CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table grp
(
    id mediumint auto_increment not null,
    uid mediumint not null,
    uuid varchar(100) not null;
    name varchar(100),
    primary key (id),
    foreign key (uid) references user(id)
) engine = InnoDB default CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table event
(
  id mediumint auto_increment not null,
  uid mediumint not null,
  cid mediumint,
  gid mediumint default null,
  title varchar(200) not null,
  detail varchar(500),
  isFullDay boolean not null default false,
  start int not null,
  end int,
  shareToken varchar(200),
  primary key (id),
  foreign key (uid) references user(id),
  foreign key (cid) references category(id),
  foreign key (gid) references grp(id)
) engine = InnoDB default CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table groupMember
(
    id mediumint auto_increment not null,
    uid mediumint not null,
    gid mediumint not null,
    primary key (id),
    foreign key (uid) references user(id),
    foreign key (gid) references grp(id)
) engine = InnoDB default CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
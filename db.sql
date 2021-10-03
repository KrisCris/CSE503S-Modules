create table user(
    id int auto_increment not null,
    username varchar(50) not null,
    password varchar(200) not null,
    time timestamp not null default current_timestamp,
    photo varchar(500) not null default '/media/module3res/userPhoto/defaultPhoto.png',
    admin boolean not null default false,
    primary key (id)
) engine = InnoDB default character set = utf8 collate = utf8_general_ci;

create table story(
    id int auto_increment not null,
    userId int not null,
    title varchar(300) not null,
    content longtext,
    link longtext,
    time timestamp not null default current_timestamp,
    click int default 0,
    primary key (id),
    foreign key (userId) references user (id)
) engine = InnoDB default character set = utf8 collate = utf8_general_ci;

create table comments(
    id int auto_increment not null,
    userId int not null,
    storyId int not null,
    comment longtext not null,
    time timestamp not null default current_timestamp,
    seen boolean not null default false,
    primary key (id),
    foreign key (userId) references user (id),
    foreign key (storyId) references story (id) on delete cascade
) engine = InnoDB default character set = utf8 collate = utf8_general_ci;

create table subComments(
    id int auto_increment not null,
    userId int not null,
    commentsId int not null,
    time timestamp not null default current_timestamp,
    seen boolean not null default false,
    primary key (id),
    foreign key (commentsId) references comments(id) on delete cascade,
    foreign key (userId) references user(id)
) engine = InnoDB default character set = utf8 collate = utf8_general_ci;

create table rate(
    id int auto_increment not null,
    userId int not null,
    storyId int not null,
    value int not null,
    time timestamp not null default current_timestamp,
    seen boolean not null default false,
    primary key (id),
    foreign key (userId) references user(id),
    foreign key (storyId) references story(id) on delete cascade
) engine = InnoDB default character set = utf8 collate = utf8_general_ci;

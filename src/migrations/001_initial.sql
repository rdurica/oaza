create table faq
(
    id         int auto_increment
        primary key,
    question   varchar(100)                          not null,
    answer     mediumtext                            not null,
    created_ts timestamp default current_timestamp() not null,
    updated_ts timestamp                             null on update current_timestamp(),
    enabled    tinyint   default 1                   not null
)
    collate = utf8mb4_czech_ci;

create table news
(
    id            int auto_increment
        primary key,
    name          varchar(50)                           not null,
    text          text                                  not null,
    creation      timestamp default current_timestamp() not null,
    `show`        int       default 1                   null,
    show_homepage int       default 1                   null
)
    collate = utf8mb4_czech_ci;

create table restrictions
(
    id      int auto_increment
        primary key,
    `from`  timestamp null,
    `to`    timestamp null,
    message text      null,
    constraint uq_from__to
        unique (`from`, `to`)
)
    collate = utf8mb4_czech_ci;

create table rules
(
    id      int auto_increment
        primary key,
    message longtext null
);

create table user
(
    id              int(32) auto_increment
        primary key,
    email           varchar(45)                             not null,
    name            varchar(45)                             not null,
    telephone       varchar(20)                             null,
    password        varchar(120)                            not null,
    role            varchar(45) default 'user'              not null,
    registered      timestamp   default current_timestamp() not null,
    enabled         tinyint(1)  default 1                   not null,
    password_resset tinyint(1)  default 0                   not null,
    constraint email
        unique (email)
)
    collate = utf8mb4_czech_ci;

create table reservation
(
    id           int auto_increment
        primary key,
    count        int                                       null,
    telephone    varchar(50) collate utf8mb4_czech_ci null,
    name         varchar(50) collate utf8mb4_czech_ci null,
    has_children tinyint(1) default 0                      not null,
    email        varchar(50) collate utf8mb4_czech_ci null,
    user_id      int                                       null,
    date         datetime                                  null,
    comment      varchar(50) collate utf8mb4_czech_ci null,
    constraint FK_rezervations_user
        foreign key (user_id) references user (id)
);
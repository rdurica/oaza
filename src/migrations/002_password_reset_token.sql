create table password_reset_token (
    id          int auto_increment primary key,
    user_id     int          not null,
    token       varchar(64)  not null unique,
    expires_at  datetime     not null,
    created_at  timestamp    default current_timestamp not null,
    constraint fk_reset_token_user foreign key (user_id) references user(id) on delete cascade,
    index idx_token (token),
    index idx_expires (expires_at)
);

alter table user drop column password_resset;

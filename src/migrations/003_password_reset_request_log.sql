create table password_reset_request_log (
    id          int auto_increment primary key,
    email_hash  char(64)  not null,
    created_at  timestamp default current_timestamp not null,
    index idx_email_hash_created (email_hash, created_at),
    index idx_created_at (created_at)
);

create table if not exists users
(
    id          integer unsigned auto_increment primary key,
    name        varchar(255),
    email       varchar(255),
    password    varchar(255),
    phone       varchar(255),
    status      char(1),
    created_at  datetime,
    updated_at  datetime,

    key         user_email(email(15)),
    key         user_created_at(created_at),
    key         user_updated_at(updated_at)
);

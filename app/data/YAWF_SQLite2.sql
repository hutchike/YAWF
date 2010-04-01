create table yawf_admins
(
    id                  integer primary key,
    created_at          datetime key,
    modified_at         datetime key,
    username            varchar(255),
    password            varchar(255),
    name                varchar(255),
    email               varchar(255),
    status              char(1)
);

create table yawf_issues
(
    id                  integer primary key,
    created_at          datetime key,
    modified_at         datetime key,
    fixed_at            datetime key,
    owned_by            varchar(255),
    summary             varchar(255),
    test_that_failed    varchar(255),
    what_should_happen  text,
    what_really_happens text,
    how_it_was_fixed    text,
    notes               text,
    units_of_work       integer,
    status              char(1)
);

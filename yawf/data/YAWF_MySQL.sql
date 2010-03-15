create table if not exists yawf_admins
(
    id                  INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at          DATETIME,
    modified_at         DATETIME,
    username            VARCHAR(255),
    password            VARCHAR(255),
    name                VARCHAR(255),
    email               VARCHAR(255),
    status              CHAR(1),

    KEY                 yawf_admin_created_at(created_at),
    KEY                 yawf_admin_modified_at(modified_at),
    KEY                 yawf_admin_username(username)
);

create table if not exists yawf_issues
(
    id                  INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at          DATETIME,
    modified_at         DATETIME,
    fixed_at            DATETIME,
    owned_by            VARCHAR(255),
    summary             VARCHAR(255),
    test_that_failed    VARCHAR(255),
    what_should_happen  TEXT,
    what_really_happens TEXT,
    how_it_was_fixed    TEXT,
    notes               TEXT,
    units_of_work       INTEGER UNSIGNED,
    status              CHAR(1),

    KEY                 yawf_issue_created_at(created_at),
    KEY                 yawf_issue_modified_at(modified_at),
    KEY                 yawf_issue_fixed_at(fixed_at)
);

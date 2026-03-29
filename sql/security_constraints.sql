ALTER TABLE USER
    ADD CONSTRAINT uq_user_username UNIQUE (username);

ALTER TABLE USER
    ADD CONSTRAINT uq_user_email UNIQUE (user_email);


CREATE TABLE b_mail_mailbox (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  TIMESTAMP_X timestamp DEFAULT CURRENT_TIMESTAMP,
  LID char(2) NOT NULL,
  ACTIVE char(1) NOT NULL DEFAULT 'Y',
  SERVICE_ID int NOT NULL DEFAULT 0,
  EMAIL varchar(255),
  USERNAME varchar(255),
  NAME varchar(255),
  SERVER varchar(255),
  PORT int NOT NULL DEFAULT '110',
  LINK varchar(255),
  LOGIN varchar(255),
  CHARSET varchar(255),
  PASSWORD varchar(255),
  DESCRIPTION text,
  USE_MD5 char(1) NOT NULL DEFAULT 'N',
  DELETE_MESSAGES char(1) NOT NULL DEFAULT 'N',
  PERIOD_CHECK int,
  MAX_MSG_COUNT int DEFAULT '0',
  MAX_MSG_SIZE int DEFAULT '0',
  MAX_KEEP_DAYS int DEFAULT '0',
  USE_TLS char(1) NOT NULL DEFAULT 'N',
  SERVER_TYPE varchar(10) NOT NULL DEFAULT 'pop3',
  DOMAINS varchar(255),
  RELAY char(1) NOT NULL DEFAULT 'Y',
  AUTH_RELAY char(1) NOT NULL DEFAULT 'Y',
  USER_ID int NOT NULL DEFAULT 0,
  SYNC_LOCK int,
  OPTIONS text,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_mail_mailbox_user_id ON b_mail_mailbox (user_id);

CREATE TABLE b_mail_filter (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  TIMESTAMP_X timestamp DEFAULT CURRENT_TIMESTAMP,
  MAILBOX_ID int NOT NULL,
  PARENT_FILTER_ID int,
  NAME varchar(255),
  DESCRIPTION text,
  SORT int NOT NULL DEFAULT '500',
  ACTIVE char(1) NOT NULL DEFAULT 'Y',
  PHP_CONDITION text,
  WHEN_MAIL_RECEIVED char(1) NOT NULL DEFAULT 'N',
  WHEN_MANUALLY_RUN char(1) NOT NULL DEFAULT 'N',
  SPAM_RATING decimal,
  SPAM_RATING_TYPE char(1) DEFAULT '<',
  MESSAGE_SIZE int,
  MESSAGE_SIZE_TYPE char(1) DEFAULT '<',
  MESSAGE_SIZE_UNIT char(1),
  ACTION_STOP_EXEC char(1) NOT NULL DEFAULT 'N',
  ACTION_DELETE_MESSAGE char(1) NOT NULL DEFAULT 'N',
  ACTION_READ char(1) NOT NULL DEFAULT '-',
  ACTION_PHP text,
  ACTION_TYPE varchar(50),
  ACTION_VARS text,
  ACTION_SPAM char(1) NOT NULL DEFAULT '-',
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_mail_filter_mailbox_id ON b_mail_filter (mailbox_id);

CREATE TABLE b_mail_filter_cond (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  FILTER_ID int NOT NULL,
  TYPE varchar(50) NOT NULL,
  STRINGS text NOT NULL,
  COMPARE_TYPE varchar(30) NOT NULL DEFAULT 'CONTAIN',
  PRIMARY KEY (ID)
);

CREATE TABLE b_mail_message (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  MAILBOX_ID int NOT NULL,
  DATE_INSERT timestamp NOT NULL,
  FULL_TEXT text,
  MESSAGE_SIZE int NOT NULL,
  HEADER text,
  FIELD_DATE timestamp,
  FIELD_FROM varchar(255),
  FIELD_REPLY_TO varchar(255),
  FIELD_TO varchar(255),
  FIELD_CC varchar(255),
  FIELD_BCC varchar(255),
  FIELD_PRIORITY int NOT NULL DEFAULT '3',
  SUBJECT varchar(255),
  BODY text,
  BODY_HTML text,
  SEARCH_CONTENT text,
  INDEX_VERSION int NOT NULL DEFAULT 0,
  ATTACHMENTS int DEFAULT '0',
  NEW_MESSAGE char(1) DEFAULT 'Y',
  SPAM char(1) NOT NULL DEFAULT '?',
  SPAM_RATING decimal,
  SPAM_WORDS varchar(255),
  SPAM_LAST_RESULT char(1) NOT NULL DEFAULT 'N',
  EXTERNAL_ID varchar(255),
  MSG_ID varchar(255),
  IN_REPLY_TO varchar(255),
  LEFT_MARGIN int8,
  RIGHT_MARGIN int8,
  READ_CONFIRMED timestamp,
  OPTIONS text,
  SANITIZE_ON_VIEW smallint null default null,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_mail_message_mailbox_id_in_reply_to_msg_id ON b_mail_message (mailbox_id, in_reply_to, msg_id);
CREATE INDEX ix_b_mail_message_mailbox_id_msg_id ON b_mail_message (mailbox_id, msg_id);
CREATE INDEX ix_b_mail_message_date_insert_mailbox_id ON b_mail_message (date_insert, mailbox_id);
CREATE INDEX ix_b_mail_message_mailbox_id_field_date ON b_mail_message (mailbox_id, field_date);
CREATE INDEX ix_b_mail_message_msg_id ON b_mail_message (msg_id);
CREATE INDEX ix_b_mail_message_in_reply_to ON b_mail_message (in_reply_to);
CREATE INDEX ix_b_mail_message_index_version ON b_mail_message (index_version);
CREATE INDEX ix_b_mail_message_left_margin_right_margin_mailbox_id ON b_mail_message (left_margin, right_margin, mailbox_id);

CREATE TABLE b_mail_message_uid (
  ID varchar(32) NOT NULL,
  MAILBOX_ID int NOT NULL,
  DIR_MD5 varchar(32),
  DIR_UIDV int8,
  MSG_UID int8,
  INTERNALDATE timestamp,
  HEADER_MD5 varchar(32),
  IS_SEEN char(1) NOT NULL DEFAULT 'N',
  SESSION_ID varchar(32) NOT NULL,
  TIMESTAMP_X timestamp DEFAULT CURRENT_TIMESTAMP,
  DATE_INSERT timestamp NOT NULL,
  MESSAGE_ID int NOT NULL,
  DELETE_TIME int NOT NULL DEFAULT 0,
  IS_OLD char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (ID, MAILBOX_ID)
);
CREATE INDEX ix_b_mail_message_uid_mailbox_id_dir_md5_dir_uidv_msg_uid ON b_mail_message_uid (mailbox_id, dir_md5, dir_uidv, msg_uid);
CREATE INDEX ix_b_mail_message_uid_header_md5 ON b_mail_message_uid (header_md5);
CREATE INDEX ix_b_mail_message_uid_mbx_id_md5_del_time_msg_uid ON b_mail_message_uid (mailbox_id, dir_md5, delete_time, msg_uid);
CREATE INDEX ix_b_mail_message_uid_mailbox_id_dir_md5_is_seen_message_id ON b_mail_message_uid (mailbox_id, dir_md5, is_seen, message_id);
CREATE INDEX ix_b_mail_message_uid_mailbox_id_message_id_dir_md5_is_seen ON b_mail_message_uid (mailbox_id, message_id, dir_md5, is_seen);
CREATE INDEX ix_b_mail_message_uid_mailbox_id_message_id_dir_md5_is_old ON b_mail_message_uid (mailbox_id, message_id, dir_md5, is_old);
CREATE INDEX ix_b_mail_message_uid_mailbox_id_message_id_internaldate ON b_mail_message_uid (mailbox_id, message_id, internaldate);
CREATE INDEX ix_b_mail_message_uid_mailbox_id_message_id_is_old_date_insert ON b_mail_message_uid (mailbox_id, message_id, is_old, date_insert);
CREATE INDEX ix_b_mail_message_uid_message_id_mailbox_id_dir_md5_delete_time ON b_mail_message_uid (message_id, mailbox_id, dir_md5, delete_time);
CREATE INDEX ix_b_mail_message_uid_mailbox_id_dir_md5_message_id ON b_mail_message_uid (mailbox_id, dir_md5, message_id);
CREATE INDEX ix_b_mail_message_uid_mailbox_id_delete_time_2 ON b_mail_message_uid (mailbox_id, delete_time, dir_md5);
CREATE INDEX ix_b_mail_sync_dir_delete_messages ON b_mail_message_uid (msg_uid, mailbox_id, dir_md5, message_id);

CREATE TABLE b_mail_msg_attachment (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  MESSAGE_ID int NOT NULL,
  FILE_ID int NOT NULL DEFAULT '0',
  FILE_NAME varchar(255),
  FILE_SIZE int NOT NULL DEFAULT '0',
  FILE_DATA bytea,
  CONTENT_TYPE varchar(255),
  IMAGE_WIDTH int,
  IMAGE_HEIGHT int,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_mail_msg_attachment_message_id ON b_mail_msg_attachment (message_id);
CREATE INDEX ix_b_mail_msg_attachment_file_id ON b_mail_msg_attachment (file_id);

CREATE TABLE b_mail_spam_weight (
  WORD_ID varchar(32) NOT NULL,
  WORD_REAL varchar(50) NOT NULL,
  GOOD_CNT int NOT NULL DEFAULT '0',
  BAD_CNT int NOT NULL DEFAULT '0',
  TOTAL_CNT int NOT NULL DEFAULT '0',
  TIMESTAMP_X timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (WORD_ID)
);
CREATE INDEX ix_b_mail_spam_weight_good_cnt ON b_mail_spam_weight (good_cnt);
CREATE INDEX ix_b_mail_spam_weight_bad_cnt ON b_mail_spam_weight (bad_cnt);

CREATE TABLE b_mail_log (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  MAILBOX_ID int NOT NULL DEFAULT '0',
  FILTER_ID int,
  MESSAGE_ID int,
  LOG_TYPE varchar(50),
  DATE_INSERT timestamp NOT NULL,
  STATUS_GOOD char(1) NOT NULL DEFAULT 'Y',
  MESSAGE varchar(255),
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_mail_log_mailbox_id ON b_mail_log (mailbox_id);
CREATE INDEX ix_b_mail_log_message_id ON b_mail_log (message_id);

CREATE TABLE b_mail_mailservices (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  SITE_ID varchar(255) NOT NULL,
  ACTIVE char(1) NOT NULL DEFAULT 'Y',
  SERVICE_TYPE varchar(10) NOT NULL DEFAULT 'imap',
  NAME varchar(255) NOT NULL,
  SERVER varchar(255),
  PORT int,
  ENCRYPTION char(1),
  LINK varchar(255),
  ICON int,
  TOKEN varchar(255),
  FLAGS int NOT NULL DEFAULT 0,
  SORT int NOT NULL DEFAULT 100,
  SMTP_SERVER varchar(255),
  SMTP_PORT int,
  SMTP_ENCRYPTION char(1),
  SMTP_LOGIN_AS_IMAP char(1) NOT NULL DEFAULT 'N',
  SMTP_PASSWORD_AS_IMAP char(1) NOT NULL DEFAULT 'N',
  UPLOAD_OUTGOING char(1),
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_mail_mailservices_active ON b_mail_mailservices (active);

CREATE TABLE b_mail_user_relations (
  TOKEN varchar(32) NOT NULL,
  SITE_ID char(2),
  USER_ID int NOT NULL,
  ENTITY_TYPE varchar(255) NOT NULL,
  ENTITY_ID varchar(255),
  ENTITY_LINK varchar(255),
  BACKURL varchar(255),
  PRIMARY KEY (TOKEN)
);
CREATE UNIQUE INDEX ux_b_mail_user_relations_user_id_entity_type_entity_id_site_id ON b_mail_user_relations (user_id, entity_type, entity_id, site_id);

CREATE TABLE b_mail_blacklist (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  SITE_ID char(2) NOT NULL,
  MAILBOX_ID int NOT NULL DEFAULT 0,
  USER_ID int8 NOT NULL DEFAULT 0,
  ITEM_TYPE int NOT NULL,
  ITEM_VALUE varchar(255) NOT NULL,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_mail_blacklist_mailbox_id_site_id ON b_mail_blacklist (mailbox_id, site_id);
CREATE UNIQUE INDEX ux_b_mail_blacklist_mailbox_id_user_id_item_value ON b_mail_blacklist (mailbox_id, user_id, item_value);

CREATE TABLE b_mail_contact (
  ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  EMAIL varchar(255) DEFAULT NULL,
  NAME varchar(255) DEFAULT NULL,
  ICON varchar(255) DEFAULT NULL,
  FILE_ID int8 DEFAULT NULL,
  USER_ID int8 DEFAULT NULL,
  ADDED_FROM varchar(50) DEFAULT NULL,
  PRIMARY KEY (ID)
);
CREATE UNIQUE INDEX ux_b_mail_contact_user_id_email ON b_mail_contact (user_id, email);

CREATE TABLE b_mail_domain_email (
  DOMAIN varchar(255) NOT NULL,
  LOGIN varchar(255) NOT NULL,
  PRIMARY KEY (LOGIN, DOMAIN)
);
CREATE INDEX ix_b_mail_domain_email_domain ON b_mail_domain_email (domain);

CREATE TABLE b_mail_oauth (
  ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  UID varchar(32) NOT NULL,
  TOKEN text,
  REFRESH_TOKEN text,
  TOKEN_EXPIRES int8,
  SECRET varchar(250),
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_mail_oauth_uid ON b_mail_oauth (uid);

CREATE TABLE b_mail_mailbox_access (
  ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  MAILBOX_ID int8 NOT NULL,
  TASK_ID int8 NOT NULL,
  ACCESS_CODE varchar(50) NOT NULL,
  PRIMARY KEY (ID)
);
CREATE INDEX ix_b_mail_mailbox_access_access_code_task_id ON b_mail_mailbox_access (access_code, task_id);

CREATE TABLE b_mail_message_access (
  TOKEN varchar(32) NOT NULL,
  SECRET varchar(32) NOT NULL,
  MAILBOX_ID int8 NOT NULL,
  MESSAGE_ID int8 NOT NULL,
  ENTITY_UF_ID int8 NOT NULL,
  ENTITY_TYPE varchar(20) NOT NULL,
  ENTITY_ID int8 NOT NULL,
  OPTIONS text,
  PRIMARY KEY (TOKEN)
);
CREATE INDEX ix_b_mail_message_access_message_id_entity_id_entity_uf_id_mail ON b_mail_message_access (message_id, entity_id, entity_uf_id, mailbox_id);
CREATE INDEX ix_b_mail_message_access_entity_id_entity_type ON b_mail_message_access (entity_id, entity_type);

CREATE TABLE b_mail_message_upload_queue (
  ID varchar(32) NOT NULL,
  MAILBOX_ID int NOT NULL,
  SYNC_STAGE int NOT NULL DEFAULT 0,
  SYNC_LOCK int NOT NULL DEFAULT 0,
  ATTEMPTS int NOT NULL DEFAULT 0,
  PRIMARY KEY (ID, MAILBOX_ID)
);

CREATE TABLE b_mail_user_signature (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  USER_ID int NOT NULL,
  SIGNATURE text,
  SENDER varchar(255),
  PRIMARY KEY (ID)
);

CREATE TABLE b_mail_user_message (
  ID int GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  TYPE varchar(10) NOT NULL,
  SITE_ID char(2) NOT NULL,
  ENTITY_TYPE varchar(255) NOT NULL,
  ENTITY_ID varchar(255),
  USER_ID int NOT NULL,
  SUBJECT varchar(255),
  CONTENT text,
  ATTACHMENTS text,
  HEADERS text,
  PRIMARY KEY (ID)
);

CREATE TABLE b_mail_message_closure (
  ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  MESSAGE_ID int8 NOT NULL,
  PARENT_ID int8 NOT NULL,
  PRIMARY KEY (ID)
);
CREATE UNIQUE INDEX ux_b_mail_message_closure_message_id_parent_id ON b_mail_message_closure (message_id, parent_id);
CREATE INDEX ix_b_mail_message_closure_parent_id_message_id ON b_mail_message_closure (parent_id, message_id);

CREATE TABLE b_mail_message_delete_queue (
  PK int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
  ID varchar(32) NOT NULL,
  MAILBOX_ID int8 NOT NULL,
  MESSAGE_ID int8 NOT NULL,
  PRIMARY KEY (PK)
);
CREATE UNIQUE INDEX ux_b_mail_message_delete_queue_id_mailbox_id_message_id ON b_mail_message_delete_queue (id, mailbox_id, message_id);
CREATE INDEX ix_b_mail_message_delete_queue_mailbox_id_message_id ON b_mail_message_delete_queue (mailbox_id, message_id);

CREATE TABLE b_mail_mailbox_dir (
	ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
	MAILBOX_ID int8 NOT NULL,
	NAME varchar(255) NOT NULL,
	PATH text NOT NULL,
	FLAGS text,
	DELIMITER varchar(1),
	DIR_MD5 varchar(32),
	LEVEL int8 NOT NULL,
	PARENT_ID int8,
	ROOT_ID int8,
	MESSAGE_COUNT int8,
	IS_SYNC int,
	IS_DISABLED int,
	IS_INCOME int,
	IS_OUTCOME int,
	IS_DRAFT int,
	IS_TRASH int,
	IS_SPAM int,
	SYNC_TIME int,
	SYNC_LOCK int,
	IS_DATE_CACHED smallint default null,
	INTERNAL_START_DATE timestamp default null,
	PRIMARY KEY (ID)
);
CREATE INDEX ix_b_mail_mailbox_dir_mailbox_id_dir_md5 ON b_mail_mailbox_dir (mailbox_id, dir_md5);
CREATE INDEX ix_b_mail_mailbox_dir_mailbox_id_level ON b_mail_mailbox_dir (mailbox_id, level);

CREATE TABLE b_mail_counter (
  MAILBOX_ID int8 NOT NULL,
  ENTITY_TYPE varchar(32) NOT NULL,
  ENTITY_ID varchar(32) NOT NULL,
  VALUE int8 NOT NULL DEFAULT 0,
  PRIMARY KEY (MAILBOX_ID, ENTITY_ID, ENTITY_TYPE)
);
CREATE INDEX ix_b_mail_counter_entity_type_entity_id ON b_mail_counter (entity_type, entity_id);

CREATE TABLE b_mail_entity_options (
  MAILBOX_ID int8 NOT NULL,
  ENTITY_TYPE varchar(32) NOT NULL,
  ENTITY_ID varchar(32) NOT NULL,
  PROPERTY_NAME varchar(32) NOT NULL,
  VALUE varchar(32) NOT NULL,
  DATE_INSERT timestamp,
  PRIMARY KEY (MAILBOX_ID, ENTITY_TYPE, ENTITY_ID, PROPERTY_NAME)
);
CREATE INDEX ix_b_mail_entity_options_mailbox_id_entity_type_entity_id_prope ON b_mail_entity_options (mailbox_id, entity_type, entity_id, property_name, value, date_insert);

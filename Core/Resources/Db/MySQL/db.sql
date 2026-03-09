CREATE TABLE addressbooks (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    principaluri VARBINARY(255),
    displayname VARCHAR(255),
    uri VARBINARY(200),
    description TEXT,
    synctoken INT(11) UNSIGNED NOT NULL DEFAULT '1',
    UNIQUE(principaluri(100), uri(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cards (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    addressbookid INT(11) UNSIGNED NOT NULL,
    carddata MEDIUMBLOB,
    uri VARBINARY(200),
    lastmodified INT(11) UNSIGNED,
    etag VARBINARY(32),
    size INT(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE addressbookchanges (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    synctoken INT(11) UNSIGNED NOT NULL,
    addressbookid INT(11) UNSIGNED NOT NULL,
    operation TINYINT(1) NOT NULL,
    INDEX addressbookid_synctoken (addressbookid, synctoken)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE calendarobjects (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    calendardata MEDIUMBLOB,
    uri VARBINARY(200),
    calendarid INTEGER UNSIGNED NOT NULL,
    lastmodified INT(11) UNSIGNED,
    etag VARBINARY(32),
    size INT(11) UNSIGNED NOT NULL,
    componenttype VARBINARY(8),
    firstoccurence INT(11) UNSIGNED,
    lastoccurence INT(11) UNSIGNED,
    uid VARBINARY(200),
    UNIQUE(calendarid, uri)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE calendars (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    synctoken INTEGER UNSIGNED NOT NULL DEFAULT '1',
    components VARBINARY(21)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE calendarinstances (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    calendarid INTEGER UNSIGNED NOT NULL,
    principaluri VARBINARY(100),
    access TINYINT(1) NOT NULL DEFAULT '1',
    displayname VARCHAR(100),
    uri VARBINARY(200),
    description TEXT,
    calendarorder INT(11) UNSIGNED NOT NULL DEFAULT '0',
    calendarcolor VARBINARY(10),
    timezone TEXT,
    transparent TINYINT(1) NOT NULL DEFAULT '0',
    share_href VARBINARY(100),
    share_displayname VARCHAR(100),
    share_invitestatus TINYINT(1) NOT NULL DEFAULT '2',
    UNIQUE(principaluri, uri),
    UNIQUE(calendarid, principaluri),
    UNIQUE(calendarid, share_href)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE calendarchanges (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    synctoken INT(11) UNSIGNED NOT NULL,
    calendarid INT(11) UNSIGNED NOT NULL,
    operation TINYINT(1) NOT NULL,
    INDEX calendarid_synctoken (calendarid, synctoken)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE calendarsubscriptions (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    principaluri VARBINARY(100) NOT NULL,
    source TEXT,
    displayname VARCHAR(100),
    refreshrate VARCHAR(10),
    calendarorder INT(11) UNSIGNED NOT NULL DEFAULT '0',
    calendarcolor VARBINARY(10),
    striptodos TINYINT(1) NULL,
    stripalarms TINYINT(1) NULL,
    stripattachments TINYINT(1) NULL,
    lastmodified INT(11) UNSIGNED,
    UNIQUE(principaluri, uri)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE schedulingobjects (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    principaluri VARBINARY(255),
    calendardata MEDIUMBLOB,
    uri VARBINARY(200),
    lastmodified INT(11) UNSIGNED,
    etag VARBINARY(32),
    size INT(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE locks (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    owner VARCHAR(100),
    timeout INTEGER UNSIGNED,
    created INTEGER,
    token VARBINARY(100),
    scope TINYINT,
    depth TINYINT,
    uri VARBINARY(1000),
    INDEX(token),
    INDEX(uri(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE principals (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    email VARBINARY(80),
    displayname VARCHAR(80),
    UNIQUE(uri)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE groupmembers (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    principal_id INTEGER UNSIGNED NOT NULL,
    member_id INTEGER UNSIGNED NOT NULL,
    UNIQUE(principal_id, member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE propertystorage (
    id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    path VARBINARY(1024) NOT NULL,
    name VARBINARY(100) NOT NULL,
    valuetype INT UNSIGNED,
    value MEDIUMBLOB
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE UNIQUE INDEX path_property ON propertystorage (path(600), name(100));
CREATE TABLE user (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50),
    password_hash VARBINARY(200),
    access_token VARCHAR(200),
    UNIQUE(username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE recurrence ( 
`id` INT NOT NULL AUTO_INCREMENT,
`uid` VARCHAR(100) NOT NULL COMMENT 'reference to calendarobjects table', 
`recur_scale` INT NOT NULL DEFAULT '1' COMMENT '(INTERVAL) hours,days,weeks,months,years = 0,1,2,3,4' , 
`recur_units` INT NULL COMMENT '(FREQ) number of time units before reminder will recur quietly; null values indicate non-recurrence', 
`recur_float` INT NOT NULL DEFAULT '1' COMMENT 'recur after complete_date rather than after start_date; 0=false, other=true',
`grace_scale` INT NOT NULL DEFAULT '1' COMMENT '(DUE) hours,days,weeks,months,years = 0,1,2,3,4' , 
`grace_units` INT NULL  COMMENT '(DUE) amount of time after start_date before reminder will appear as overdue', 
`passive_scale` INT NOT NULL DEFAULT '1' COMMENT 'hours,days,weeks,months,years = 0,1,2,3,4' , 
`passive_units` INT NULL  COMMENT 'amount of time between start_date and alarm condition', 
`snooze_scale` INT NOT NULL DEFAULT '1' COMMENT 'hours,days,weeks,months,years = 0,1,2,3,4' , 
`snooze_units` INT NULL  COMMENT 'default amount of time to delay this reminder if user snoozes it', 
`alarm_interval_scale` INT NOT NULL DEFAULT '2' COMMENT 'hours,days,weeks,months,years = 0,1,2,3,4' , 
`alarm_interval_units` FLOAT NULL  COMMENT 'how often to send active reminders ', 
`alarm_sent_date` DATETIME NULL COMMENT 'date of last reminder sent',
`complete_date` DATETIME NULL COMMENT 'date last completed', 
`snooze_date` DATETIME NULL  COMMENT 'defer reminder until this date', 
`end_date` DATETIME NULL  COMMENT 'reminder will not recur after this date', 
`start_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when this reminder will become current', 
`due_date` DATETIME NULL  COMMENT 'calculated based on start_date and days_grace', 
`active_date` DATETIME NULL COMMENT 'calculated based on start_date and days_passive', 
`days_of_week` CHAR(7)  NULL  COMMENT 'days of week this reminder is active: MTWtFSs; null implies all' , 
`season_start` INT  NULL COMMENT '0-364; days after January 1 that the season for this reminder starts'  , 
`season_end` INT NULL COMMENT '0-364; days after January 1 that the season for this reminder ends', 
`day_start` INT  NULL COMMENT '0-2359; military time for time of day that this reminder becomes active (mod 100 values over 59 will round down to 59)' ,
`day_end` INT  NULL COMMENT '0-2359; military time for time of day that this reminder becomes inactive (mod 100 values over 59 will round down to 59)'  , 
`last_modified` VARCHAR(25) NULL COMMENT 'date of last change to this record (as CalDAV string, GMT)', 
`sequence` BIGINT NOT NULL  COMMENT 'used for preventing duplicate updates on page refresh', 
`created` VARCHAR(25) NULL COMMENT 'date created (as CalDAV string, GMT)', 
PRIMARY KEY (`id`), 
UNIQUE KEY(`uid`), 
UNIQUE KEY(`sequence`), 
INDEX `recur_snooze_date_indx` (`snooze_date`),
INDEX `recur_init_date_indx` (`complete_date`),
INDEX `recur_end_date_indx` (`end_date`),
INDEX `recur_start_date_indx` (`start_date`),
INDEX `recur_due_date_indx` (`due_date`),
INDEX `recur_active_date_indx` (`active_date`)
);

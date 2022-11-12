-- add column in user table
ALTER TABLE wcf1_user ADD isTracked TINYINT(1) NOT NULL DEFAULT 0;

-- Tracker
DROP TABLE IF EXISTS wcf1_user_tracker;
CREATE TABLE wcf1_user_tracker (
    trackerID                INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    days                    INT(10) NOT NULL DEFAULT 7,
    isActive                TINYINT(1) NOT NULL DEFAULT 1,
    time                    INT(10) NOT NULL DEFAULT 0,
    userID                    INT(10) DEFAULT NULL,
    username                VARCHAR(255) NOT NULL DEFAULT '',

    usersIgnore                TINYINT(1) NOT NULL DEFAULT 1,
    usersFollow                TINYINT(1) NOT NULL DEFAULT 1,
    usersLogin                TINYINT(1) NOT NULL DEFAULT 1,
    usersReport                TINYINT(1) NOT NULL DEFAULT 1,
    usersInfraction            TINYINT(1) NOT NULL DEFAULT 1,

    accountUsername            TINYINT(1) NOT NULL DEFAULT 1,
    accountEmail            TINYINT(1) NOT NULL DEFAULT 1,
    accountDeletion            TINYINT(1) NOT NULL DEFAULT 1,
    accountPassword            TINYINT(1) NOT NULL DEFAULT 1,

    profileAvatar            TINYINT(1) NOT NULL DEFAULT 1,
    profileTitle            TINYINT(1) NOT NULL DEFAULT 1,
    profileSignature        TINYINT(1) NOT NULL DEFAULT 1,
    profileOther            TINYINT(1) NOT NULL DEFAULT 1,

    contentArticle            TINYINT(1) NOT NULL DEFAULT 1,
    contentAttachment        TINYINT(1) NOT NULL DEFAULT 1,
    contentComment            TINYINT(1) NOT NULL DEFAULT 1,
    contentConversation        TINYINT(1) NOT NULL DEFAULT 1,
    contentLike                TINYINT(1) NOT NULL DEFAULT 1,
    contentTag                TINYINT(1) NOT NULL DEFAULT 1,
    contentPoll                TINYINT(1) NOT NULL DEFAULT 1,

    otherModeration            TINYINT(1) NOT NULL DEFAULT 1,
    otherPage                TINYINT(1) NOT NULL DEFAULT 1,

    UNIQUE KEY (userID)
);

DROP TABLE IF EXISTS wcf1_user_tracker_log;
CREATE TABLE wcf1_user_tracker_log (
    trackerLogID            INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    description                VARCHAR(255) NOT NULL DEFAULT '',
    ipAddress                VARCHAR(39) NOT NULL DEFAULT '',
    link                    TEXT NOT NULL,
    name                    VARCHAR(255) NOT NULL DEFAULT '',
    time                    INT(10) NOT NULL DEFAULT 0,
    trackerID                INT(10),
    type                    VARCHAR(191) NOT NULL DEFAULT '',
    userID                    INT(10) DEFAULT NULL,
    username                VARCHAR(255) NOT NULL DEFAULT '',
    packageID                INT(10) NOT NULL DEFAULT 1,
    userAgent                VARCHAR(255) NOT NULL DEFAULT '',
    content                    MEDIUMTEXT,

    KEY (trackerID),
    KEY (type),
    KEY (packageID)
);

DROP TABLE IF EXISTS wcf1_user_tracker_page;
CREATE TABLE wcf1_user_tracker_page (
    class                VARCHAR(255) NOT NULL DEFAULT '',
    page                VARCHAR(25) NOT NULL DEFAULT '',
    isPublic            TINYINT(1) NOT NULL DEFAULT 0
);

-- foreign keys
ALTER TABLE wcf1_user_tracker ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_tracker_log ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_user_tracker_log ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_tracker_log ADD FOREIGN KEY (trackerID) REFERENCES wcf1_user_tracker (trackerID) ON DELETE SET NULL;

-- page inserts
INSERT INTO    wcf1_user_tracker_page (class, page, isPublic) VALUES ('wcf\\page\\CmsPage', 'cms', 1);
INSERT INTO    wcf1_user_tracker_page (class, page, isPublic) VALUES ('wcf\\page\\ArticlePage', 'article', 1);
INSERT INTO    wcf1_user_tracker_page (class, page, isPublic) VALUES ('wcf\\page\\ArticleFeedPage', 'rssFeedArticle', 1);
INSERT INTO    wcf1_user_tracker_page (class, page, isPublic) VALUES ('wcf\\page\\CategoryArticleListPage', 'articleCategory', 1);
INSERT INTO    wcf1_user_tracker_page (class, page, isPublic) VALUES ('wcf\\page\\ConversationPage', 'conversation', 0);
INSERT INTO    wcf1_user_tracker_page (class, page, isPublic) VALUES ('wcf\\page\\ConversationFeedPage', 'conversationFeed', 0);
INSERT INTO    wcf1_user_tracker_page (class, page, isPublic) VALUES ('wcf\\page\\MediaPage', 'media', 1);
INSERT INTO    wcf1_user_tracker_page (class, page, isPublic) VALUES ('wcf\\page\\UserPage', 'profile', 1);
INSERT INTO    wcf1_user_tracker_page (class, page, isPublic) VALUES ('wcf\\page\\AttachmentPage', 'attachment', 1);
INSERT INTO    wcf1_user_tracker_page (class, page, isPublic) VALUES ('wcf\\form\\SearchForm', 'search', 1);
INSERT INTO    wcf1_user_tracker_page (class, page, isPublic) VALUES ('wcf\\page\\TrophyPage', 'trophy', 1);
INSERT INTO wcf1_user_tracker_page (class, page, isPublic) VALUES ('wcf\\page\\CategoryTrophyListPage', 'trophyCategory', 1);

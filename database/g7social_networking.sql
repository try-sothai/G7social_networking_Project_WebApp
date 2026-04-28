-- =================================================================
-- g7social_networking.sql  |  Version 3.0  (Indexes Integrated)
-- G7 Social Networking WebApp - Full Database Schema
-- Updated: 2026-04-29
--
-- FIXES APPLIED:
--   [1]  លុប ALTER TABLE PeerID ដែល Duplicate
--   [2]  admin_reports Schema ថ្មី ត្រូវជាមួយ AdminModel.php
--   [3]  CreatePostWithHashtags - 9 params + GroupID + Hashtag Loop
--   [4]  after_user_register Trigger - Insert user_settings AND user_presence
--   [5]  posts.GroupID - FK Constraint ពិតប្រាកដ
--   [6]  DROP + CREATE Procedure (លុប CREATE OR REPLACE ដែល Invalid)
--   [7]  CreatePostWithHashtags - 1 Version តែប៉ុណ្ណោះ (លុបស្ទួន)
--   [8]  after_interaction_insert - DECLARE ត្រូវ Position
--   [9]  Trigger after_mention_insert - Notification 'Mention' Type
--   [10] GetMutualFriendsCount - Fixed Logic
--   [11] Performance Indexes — COMPOSITE + FULLTEXT ​ក្នុង CREATE TABLE ដោយផ្ទាល់
--        users, posts, friends, notifications, messages, interactions,
--        stories, events, event_participants, group_members, shares,
--        saved_posts, post_hashtags, admin_reports, user_presence
--   [12] VerificationToken column — ​បន្ថែមទៅ users table
-- =================================================================

CREATE DATABASE IF NOT EXISTS g7social_networking
    CHARACTER SET utf8mb3
    COLLATE utf8mb3_general_ci;

USE g7social_networking;

-- បិទ FK Checks ពេល Setup
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS
    post_hashtags, hashtags,
    admin_reports, saved_posts, mentions, shares,
    event_participants, events,
    notifications, user_interests, friends,
    messages, group_members, `groups`,
    post_views, story_views,
    interactions, stories, posts,
    call_logs, user_presence, user_settings, users;

SET FOREIGN_KEY_CHECKS = 1;


-- =================================================================
-- ១. USERS + SETTINGS + PRESENCE
-- =================================================================

CREATE TABLE users (
    UserID             INT           AUTO_INCREMENT PRIMARY KEY,
    Username           VARCHAR(255)  NOT NULL,
    Password           VARCHAR(255)  NOT NULL,
    Email              VARCHAR(255)  NOT NULL UNIQUE,
    Phone              VARCHAR(20)   UNIQUE,
    Locations          VARCHAR(255),
    ProfilePic         VARCHAR(255)  DEFAULT 'default.png',
    CoverPic           VARCHAR(255)  DEFAULT 'default_cover.jpg',
    Bio                VARCHAR(255),
    IsVerified         TINYINT(1)    DEFAULT 0,
    Website            VARCHAR(255)  NULL,
    Role               ENUM('User', 'Admin', 'SuperAdmin') DEFAULT 'User',
    AccountStatus      ENUM('Active', 'Suspended', 'Banned') DEFAULT 'Active',
    PeerID             VARCHAR(255)  NULL,
    LastActive         TIMESTAMP     NULL,
    VerificationToken  VARCHAR(100)  NULL DEFAULT NULL,
    CreatedAt          DATETIME      DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username            (Username),
    INDEX idx_email               (Email),
    INDEX idx_last_active         (LastActive),
    INDEX idx_account_status      (AccountStatus),
    INDEX idx_role_status         (Role, AccountStatus),
    INDEX idx_verification_token  (VerificationToken)
) ENGINE=InnoDB;

-- [FIX #1] PeerID មានក្នុង CREATE TABLE ហើយ — លុប ALTER TABLE ចោល
-- ALTER TABLE users ADD COLUMN PeerID ... ← លុបចោល (ស្ទួន)

CREATE TABLE user_settings (
    SettingID          INT        AUTO_INCREMENT PRIMARY KEY,
    UserID             INT        NOT NULL UNIQUE,
    Theme              ENUM('Light', 'Dark', 'System') DEFAULT 'Light',
    Language           ENUM('Khmer', 'English', 'Chinese') DEFAULT 'Khmer',
    NotificationSound  TINYINT(1) DEFAULT 1,
    EmailNotifications TINYINT(1) DEFAULT 1,
    UpdatedAt          TIMESTAMP  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_setting_user FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE user_presence (
    PresenceID INT        AUTO_INCREMENT PRIMARY KEY,
    UserID     INT        NOT NULL UNIQUE,
    IsOnline   TINYINT(1) DEFAULT 0,
    LastSeen   TIMESTAMP  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_presence_user FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE CASCADE,
    INDEX idx_presence_online (IsOnline, LastSeen)
) ENGINE=InnoDB;


-- =================================================================
-- ២. CALL LOGS
-- =================================================================

CREATE TABLE call_logs (
    CallID     INT      AUTO_INCREMENT PRIMARY KEY,
    CallerID   INT      NOT NULL,
    ReceiverID INT      NOT NULL,
    CallType   ENUM('Audio', 'Video') DEFAULT 'Video',
    StartTime  DATETIME DEFAULT CURRENT_TIMESTAMP,
    EndTime    DATETIME NULL,
    Status     ENUM('Missed', 'Answered', 'Declined') DEFAULT 'Missed',
    CONSTRAINT fk_call_caller   FOREIGN KEY (CallerID)   REFERENCES users(UserID) ON DELETE CASCADE,
    CONSTRAINT fk_call_receiver FOREIGN KEY (ReceiverID) REFERENCES users(UserID) ON DELETE CASCADE,
    INDEX idx_call_caller   (CallerID),
    INDEX idx_call_receiver (ReceiverID)
) ENGINE=InnoDB;


-- =================================================================
-- ៣. GROUPS (បង្កើតមុន posts ដើម្បី posts.GroupID FK អាចដំណើរការ)
-- =================================================================

CREATE TABLE `groups` (
    GroupID          INT           AUTO_INCREMENT PRIMARY KEY,
    GroupName        VARCHAR(255)  NOT NULL,
    GroupDescription TEXT,
    GroupAvatar      VARCHAR(255)  DEFAULT 'default_group.png',
    GroupBanner      VARCHAR(255)  DEFAULT 'default_banner.jpg',
    Privacy          ENUM('Public', 'Private') DEFAULT 'Public',
    Category         VARCHAR(100),
    CreatedBy        INT           NOT NULL,
    CreatedAt        TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt        TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_group_creator FOREIGN KEY (CreatedBy) REFERENCES users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB;


-- =================================================================
-- ៤. POSTS (GroupID + FK ត្រឹមត្រូវ ព្រោះ groups ត្រូវបានបង្កើតហើយ)
-- =================================================================

-- [FIX #5] GroupID ឥឡូវមាន CONSTRAINT FK ពិតប្រាកដ
CREATE TABLE posts (
    PostID         INT           AUTO_INCREMENT PRIMARY KEY,
    UserID         INT           NOT NULL,
    Content        TEXT,
    ImagePath      TEXT          NULL,
    OriginalPostID INT           NULL,
    GroupID        INT           NULL,         -- [FIX] FK ទៅ groups
    Category       VARCHAR(100)  DEFAULT 'General',
    Privacy        ENUM('Public', 'Private', 'Friends') DEFAULT 'Public',
    Location       VARCHAR(255)  DEFAULT NULL,
    Feeling        VARCHAR(100)  DEFAULT NULL,
    MediaPath      VARCHAR(255)  DEFAULT NULL,
    MediaType      ENUM('image', 'video', 'text') DEFAULT 'text',
    ShareCount     INT           DEFAULT 0,
    CreatedAt      DATETIME      DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_post_user     FOREIGN KEY (UserID)         REFERENCES users(UserID)      ON DELETE CASCADE  ON UPDATE CASCADE,
    CONSTRAINT fk_post_original FOREIGN KEY (OriginalPostID) REFERENCES posts(PostID)      ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_post_group    FOREIGN KEY (GroupID)        REFERENCES `groups`(GroupID)  ON DELETE SET NULL ON UPDATE CASCADE,

    INDEX idx_privacy_created          (Privacy, CreatedAt),
    INDEX idx_user_created             (UserID, CreatedAt),
    INDEX idx_original_post            (OriginalPostID),
    INDEX idx_category                 (Category),
    INDEX idx_group_posts              (GroupID),
    INDEX idx_category_privacy_created (Category, Privacy, CreatedAt),
    FULLTEXT KEY ft_content            (Content)
) ENGINE=InnoDB;


-- =================================================================
-- ៥. GROUP MEMBERS
-- =================================================================

CREATE TABLE group_members (
    GroupMemberID INT       AUTO_INCREMENT PRIMARY KEY,
    GroupID       INT       NOT NULL,
    UserID        INT       NOT NULL,
    Role          ENUM('Admin', 'Moderator', 'Member') DEFAULT 'Member',
    Status        ENUM('Pending', 'Approved', 'Blocked') DEFAULT 'Approved',
    JoinedAt      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_gm_group FOREIGN KEY (GroupID) REFERENCES `groups`(GroupID) ON DELETE CASCADE,
    CONSTRAINT fk_gm_user  FOREIGN KEY (UserID)  REFERENCES users(UserID)     ON DELETE CASCADE,
    UNIQUE KEY unique_membership    (GroupID, UserID),
    INDEX idx_gm_user_status        (UserID, Status),
    INDEX idx_gm_group_status       (GroupID, Status)
) ENGINE=InnoDB;


-- =================================================================
-- ៦. STORIES
-- =================================================================

CREATE TABLE stories (
    StoryID    INT           AUTO_INCREMENT PRIMARY KEY,
    UserID     INT           NOT NULL,
    Content    TEXT,
    StoryMedia VARCHAR(255),
    CreatedAt  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    ExpiresAt  TIMESTAMP     DEFAULT (CURRENT_TIMESTAMP + INTERVAL 1 DAY),
    CONSTRAINT fk_story_user FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE CASCADE,
    INDEX idx_stories_expires    (ExpiresAt),
    INDEX idx_story_user_expires (UserID, ExpiresAt)
) ENGINE=InnoDB;


-- =================================================================
-- ៧. INTERACTIONS (Like / Comment / Reply)
-- =================================================================

CREATE TABLE interactions (
    InteractionID       INT       AUTO_INCREMENT PRIMARY KEY,
    PostID              INT       NOT NULL,
    StoryID             INT       NULL,
    UserID              INT       NOT NULL,
    ParentInteractionID INT       NULL,      -- Reply ទៅ Comment
    Type                ENUM('like', 'love', 'haha', 'comment') NOT NULL,
    CommentText         TEXT      DEFAULT NULL,
    CreatedAt           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_int_post   FOREIGN KEY (PostID)              REFERENCES posts(PostID)              ON DELETE CASCADE,
    CONSTRAINT fk_int_user   FOREIGN KEY (UserID)              REFERENCES users(UserID)              ON DELETE CASCADE,
    CONSTRAINT fk_int_story  FOREIGN KEY (StoryID)             REFERENCES stories(StoryID)           ON DELETE CASCADE,
    CONSTRAINT fk_int_parent FOREIGN KEY (ParentInteractionID) REFERENCES interactions(InteractionID) ON DELETE CASCADE,

    INDEX idx_post_user       (PostID, UserID),
    INDEX idx_type            (Type),
    INDEX idx_created_at      (CreatedAt),
    INDEX idx_post_type       (PostID, Type),
    INDEX idx_user_post_type  (UserID, PostID, Type)
) ENGINE=InnoDB;


-- =================================================================
-- ៨. STORY VIEWS + POST VIEWS
-- =================================================================

CREATE TABLE story_views (
    ViewID   INT       AUTO_INCREMENT PRIMARY KEY,
    StoryID  INT       NOT NULL,
    UserID   INT       NOT NULL,
    ViewedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sv_story FOREIGN KEY (StoryID) REFERENCES stories(StoryID) ON DELETE CASCADE,
    CONSTRAINT fk_sv_user  FOREIGN KEY (UserID)  REFERENCES users(UserID)    ON DELETE CASCADE,
    UNIQUE KEY unique_story_view (StoryID, UserID)
) ENGINE=InnoDB;

CREATE TABLE post_views (
    ViewID   INT       AUTO_INCREMENT PRIMARY KEY,
    PostID   INT       NOT NULL,
    UserID   INT       NOT NULL,
    ViewedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pv_post FOREIGN KEY (PostID) REFERENCES posts(PostID) ON DELETE CASCADE,
    CONSTRAINT fk_pv_user FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE CASCADE,
    UNIQUE KEY unique_post_view (PostID, UserID),
    INDEX idx_post_view (PostID)
) ENGINE=InnoDB;


-- =================================================================
-- ៩. MESSAGES (Private Chat + Group Chat)
-- =================================================================

CREATE TABLE messages (
    MessageID   INT           AUTO_INCREMENT PRIMARY KEY,
    SenderID    INT           NOT NULL,
    ReceiverID  INT           NULL,          -- NULL ពេល Group Chat
    GroupID     INT           NULL,          -- NULL ពេល Private Chat
    MessageBody TEXT          NOT NULL,
    MessageType ENUM('text', 'image', 'video', 'file') DEFAULT 'text',
    FileUrl     VARCHAR(255)  NULL,
    IsRead      TINYINT(1)    DEFAULT 0,
    SentAt      DATETIME      DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_msg_sender   FOREIGN KEY (SenderID)   REFERENCES users(UserID)     ON DELETE CASCADE,
    CONSTRAINT fk_msg_receiver FOREIGN KEY (ReceiverID) REFERENCES users(UserID)     ON DELETE CASCADE,
    CONSTRAINT fk_msg_group    FOREIGN KEY (GroupID)    REFERENCES `groups`(GroupID) ON DELETE CASCADE,

    INDEX idx_conversation         (SenderID, ReceiverID),
    INDEX idx_messages_isread      (IsRead),
    INDEX idx_receiver_read        (ReceiverID, IsRead),
    INDEX idx_conversation_time    (SenderID, ReceiverID, SentAt),
    INDEX idx_rev_conversation_time (ReceiverID, SenderID, SentAt)
) ENGINE=InnoDB;


-- =================================================================
-- ១០. FRIENDS
-- =================================================================

CREATE TABLE friends (
    FriendshipID INT       AUTO_INCREMENT PRIMARY KEY,
    RequesterID  INT       NOT NULL,
    ReceiverID   INT       NOT NULL,
    Status       ENUM('Pending', 'Accepted', 'Blocked') DEFAULT 'Pending',
    CreatedAt    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_friend_req FOREIGN KEY (RequesterID) REFERENCES users(UserID) ON DELETE CASCADE,
    CONSTRAINT fk_friend_rec FOREIGN KEY (ReceiverID)  REFERENCES users(UserID) ON DELETE CASCADE,
    INDEX idx_friends_status (Status),
    INDEX idx_req_status     (RequesterID, Status),
    INDEX idx_rec_status     (ReceiverID,  Status),
    INDEX idx_req_rec        (RequesterID, ReceiverID)
) ENGINE=InnoDB;


-- =================================================================
-- ១១. USER INTERESTS
-- =================================================================

CREATE TABLE user_interests (
    InterestID   INT          AUTO_INCREMENT PRIMARY KEY,
    UserID       INT          NOT NULL,
    InterestName VARCHAR(100) NOT NULL,
    CreatedAt    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_interest_user FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB;


-- =================================================================
-- ១២. NOTIFICATIONS
-- =================================================================

CREATE TABLE notifications (
    NotificationID INT       AUTO_INCREMENT PRIMARY KEY,
    UserID         INT       NOT NULL,
    TriggerID      INT       NOT NULL,
    Type           ENUM('Like', 'Comment', 'FriendRequest', 'Message', 'NewEvent', 'GroupInvite', 'Mention', 'Share') NOT NULL,
    IsRead         TINYINT(1) DEFAULT 0,
    CreatedAt      TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_user    FOREIGN KEY (UserID)    REFERENCES users(UserID) ON DELETE CASCADE,
    CONSTRAINT fk_notif_trigger FOREIGN KEY (TriggerID) REFERENCES users(UserID) ON DELETE CASCADE,
    INDEX idx_notifications_isread (IsRead),
    INDEX idx_notif_user_read      (UserID, IsRead),
    INDEX idx_notif_user_created   (UserID, CreatedAt)
) ENGINE=InnoDB;


-- =================================================================
-- ១៣. EVENTS + PARTICIPANTS
-- =================================================================

CREATE TABLE events (
    EventID       INT           AUTO_INCREMENT PRIMARY KEY,
    UserID        INT           NOT NULL,
    Title         VARCHAR(255)  NOT NULL,
    Description   TEXT,
    Location      VARCHAR(255)  NOT NULL,
    EventDateTime DATETIME      NOT NULL,
    ImagePath     VARCHAR(255)  NULL,
    CreatedAt     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_event_user FOREIGN KEY (UserID) REFERENCES users(UserID) ON DELETE CASCADE,
    INDEX idx_event_datetime  (EventDateTime),
    INDEX idx_user_event_time (UserID, EventDateTime)
) ENGINE=InnoDB;

CREATE TABLE event_participants (
    ParticipantID INT       AUTO_INCREMENT PRIMARY KEY,
    EventID       INT       NOT NULL,
    UserID        INT       NOT NULL,
    Status        ENUM('Going', 'Interested', 'Not Going') DEFAULT 'Interested',
    CreatedAt     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ep_event FOREIGN KEY (EventID) REFERENCES events(EventID) ON DELETE CASCADE,
    CONSTRAINT fk_ep_user  FOREIGN KEY (UserID)  REFERENCES users(UserID)   ON DELETE CASCADE,
    UNIQUE KEY unique_event_participant (EventID, UserID),
    INDEX idx_ep_user_status            (UserID, Status),
    INDEX idx_ep_event_status           (EventID, Status)
) ENGINE=InnoDB;


-- =================================================================
-- ១៤. SHARES + MENTIONS + SAVED POSTS
-- =================================================================

CREATE TABLE shares (
    ShareID        INT       AUTO_INCREMENT PRIMARY KEY,
    OriginalPostID INT       NOT NULL,
    SharedPostID   INT       NOT NULL,
    UserID         INT       NOT NULL,
    SharedAt       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_share_original FOREIGN KEY (OriginalPostID) REFERENCES posts(PostID)  ON DELETE CASCADE,
    CONSTRAINT fk_share_shared   FOREIGN KEY (SharedPostID)   REFERENCES posts(PostID)  ON DELETE CASCADE,
    CONSTRAINT fk_share_user     FOREIGN KEY (UserID)         REFERENCES users(UserID)  ON DELETE CASCADE,
    INDEX idx_share_original (OriginalPostID, SharedAt),
    INDEX idx_share_user     (UserID, SharedAt)
) ENGINE=InnoDB;

CREATE TABLE mentions (
    MentionID   INT       AUTO_INCREMENT PRIMARY KEY,
    PostID      INT       NOT NULL,
    UserID      INT       NOT NULL,
    MentionedBy INT       NOT NULL,
    CreatedAt   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mention_post         FOREIGN KEY (PostID)      REFERENCES posts(PostID)  ON DELETE CASCADE,
    CONSTRAINT fk_mention_user         FOREIGN KEY (UserID)      REFERENCES users(UserID)  ON DELETE CASCADE,
    CONSTRAINT fk_mention_mentioned_by FOREIGN KEY (MentionedBy) REFERENCES users(UserID)  ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE saved_posts (
    SaveID  INT       AUTO_INCREMENT PRIMARY KEY,
    PostID  INT       NOT NULL,
    UserID  INT       NOT NULL,
    SavedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_save_post FOREIGN KEY (PostID) REFERENCES posts(PostID)  ON DELETE CASCADE,
    CONSTRAINT fk_save_user FOREIGN KEY (UserID) REFERENCES users(UserID)  ON DELETE CASCADE,
    UNIQUE KEY unique_save      (PostID, UserID),
    INDEX idx_saved_user_time   (UserID, SavedAt)
) ENGINE=InnoDB;


-- =================================================================
-- ១៥. ADMIN REPORTS
-- [FIX #2] Schema ថ្មី ត្រូវជាមួយ AdminModel.php
--          (EntityType + EntityID + Details + ReviewedBy)
--          UNIQUE KEY (ReporterID, EntityType, EntityID) ← ON DUPLICATE KEY UPDATE
-- =================================================================

CREATE TABLE admin_reports (
    ReportID   INT           AUTO_INCREMENT PRIMARY KEY,
    ReporterID INT           NOT NULL,
    EntityType VARCHAR(50)   NOT NULL DEFAULT 'post',  -- 'post', 'user', 'comment', 'group'
    EntityID   INT           NOT NULL,
    Reason     VARCHAR(255)  NOT NULL,
    Details    TEXT          NULL,
    Status     ENUM('Pending', 'Reviewed', 'Resolved', 'Dismissed') DEFAULT 'Pending',
    ReviewedBy INT           NULL,
    ReviewedAt DATETIME      NULL,
    CreatedAt  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_report_reporter FOREIGN KEY (ReporterID) REFERENCES users(UserID) ON DELETE CASCADE,
    CONSTRAINT fk_report_reviewer FOREIGN KEY (ReviewedBy) REFERENCES users(UserID) ON DELETE SET NULL,

    -- UNIQUE ត្រូវការសម្រាប់ ON DUPLICATE KEY UPDATE ក្នុង AdminModel.php
    UNIQUE KEY unique_report    (ReporterID, EntityType, EntityID),
    INDEX idx_report_status     (Status),
    INDEX idx_report_created_at (CreatedAt),
    INDEX idx_entity            (EntityType, EntityID),
    INDEX idx_status_created    (Status, CreatedAt)
) ENGINE=InnoDB;


-- =================================================================
-- ១៦. HASHTAGS
-- =================================================================

CREATE TABLE hashtags (
    HashtagID INT           AUTO_INCREMENT PRIMARY KEY,
    TagName   VARCHAR(100)  NOT NULL UNIQUE,
    CreatedAt TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE post_hashtags (
    PostHashtagID INT AUTO_INCREMENT PRIMARY KEY,
    PostID        INT NOT NULL,
    HashtagID     INT NOT NULL,
    CONSTRAINT fk_ph_post    FOREIGN KEY (PostID)    REFERENCES posts(PostID)       ON DELETE CASCADE,
    CONSTRAINT fk_ph_hashtag FOREIGN KEY (HashtagID) REFERENCES hashtags(HashtagID) ON DELETE CASCADE,
    UNIQUE KEY unique_post_hashtag (PostID, HashtagID),
    INDEX idx_hashtag_post         (HashtagID, PostID)
) ENGINE=InnoDB;


-- =================================================================
-- ១៧. VIEWS
-- =================================================================

-- Profile សម្រាប់ User Active ប៉ុណ្ណោះ (Security)
CREATE OR REPLACE VIEW vw_user_profiles AS
SELECT UserID, Username, ProfilePic, CoverPic, Bio, IsVerified, Role, LastActive, CreatedAt
FROM users
WHERE AccountStatus = 'Active';

-- Unread message count per user
CREATE OR REPLACE VIEW vw_unread_messages AS
SELECT ReceiverID, COUNT(*) AS unread_count
FROM messages
WHERE IsRead = 0
GROUP BY ReceiverID;

-- Post Analytics Dashboard
CREATE OR REPLACE VIEW post_analytics AS
SELECT
    p.PostID,
    p.UserID,
    p.Content,
    p.CreatedAt,
    COUNT(DISTINCT CASE WHEN i.Type IN ('like','love','haha') THEN i.UserID END) AS total_reactions,
    COUNT(DISTINCT CASE WHEN i.Type = 'comment'              THEN i.UserID END) AS total_comments,
    COUNT(DISTINCT s.ShareID)                                                    AS total_shares,
    COUNT(DISTINCT sp.SaveID)                                                    AS total_saves,
    (COUNT(DISTINCT CASE WHEN i.Type IN ('like','love','haha') THEN i.UserID END) +
     COUNT(DISTINCT CASE WHEN i.Type = 'comment'              THEN i.UserID END) * 2 +
     COUNT(DISTINCT s.ShareID) * 3)                                              AS engagement_score
FROM posts p
LEFT JOIN interactions i  ON p.PostID = i.PostID
LEFT JOIN shares      s   ON p.PostID = s.OriginalPostID
LEFT JOIN saved_posts sp  ON p.PostID = sp.PostID
GROUP BY p.PostID, p.UserID, p.Content, p.CreatedAt;


-- =================================================================
-- ១៨. FUNCTIONS
-- =================================================================

DELIMITER $$

-- [FIX #10] GetMutualFriendsCount - Fixed Query Logic
DROP FUNCTION IF EXISTS GetMutualFriendsCount $$
CREATE FUNCTION GetMutualFriendsCount(user1 INT, user2 INT)
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE mutual_count INT DEFAULT 0;

    SELECT COUNT(DISTINCT friend_of_1) INTO mutual_count
    FROM (
        SELECT CASE
            WHEN f.RequesterID = user1 THEN f.ReceiverID
            ELSE f.RequesterID
        END AS friend_of_1
        FROM friends f
        WHERE (f.RequesterID = user1 OR f.ReceiverID = user1)
          AND f.Status = 'Accepted'
    ) AS friends_user1
    WHERE friend_of_1 IN (
        SELECT CASE
            WHEN f.RequesterID = user2 THEN f.ReceiverID
            ELSE f.RequesterID
        END
        FROM friends f
        WHERE (f.RequesterID = user2 OR f.ReceiverID = user2)
          AND f.Status = 'Accepted'
    )
    AND friend_of_1 NOT IN (user1, user2);

    RETURN mutual_count;
END$$

DELIMITER ;


-- =================================================================
-- ១៩. TRIGGERS
-- =================================================================

DELIMITER $$

-- [FIX #4] after_user_register ← Insert user_settings AND user_presence
DROP TRIGGER IF EXISTS after_user_register $$
CREATE TRIGGER after_user_register
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO user_settings (UserID) VALUES (NEW.UserID);
    INSERT INTO user_presence (UserID) VALUES (NEW.UserID);
END$$


-- ShareCount Auto-increment
DROP TRIGGER IF EXISTS after_share_insert $$
CREATE TRIGGER after_share_insert
AFTER INSERT ON shares
FOR EACH ROW
BEGIN
    UPDATE posts SET ShareCount = ShareCount + 1
    WHERE PostID = NEW.OriginalPostID;
END$$

-- ShareCount Auto-decrement
DROP TRIGGER IF EXISTS after_share_delete $$
CREATE TRIGGER after_share_delete
AFTER DELETE ON shares
FOR EACH ROW
BEGIN
    UPDATE posts SET ShareCount = ShareCount - 1
    WHERE PostID = OLD.OriginalPostID;
END$$


-- [FIX #8] after_interaction_insert ← DECLARE position ត្រូវ
DROP TRIGGER IF EXISTS after_interaction_insert $$
CREATE TRIGGER after_interaction_insert
AFTER INSERT ON interactions
FOR EACH ROW
BEGIN
    DECLARE post_owner INT DEFAULT 0;

    SELECT UserID INTO post_owner
    FROM posts
    WHERE PostID = NEW.PostID
    LIMIT 1;

    IF NEW.UserID <> post_owner THEN
        INSERT INTO notifications (UserID, TriggerID, Type, IsRead)
        VALUES (
            post_owner,
            NEW.UserID,
            CASE WHEN NEW.Type = 'comment' THEN 'Comment' ELSE 'Like' END,
            0
        );
    END IF;
END$$


-- [FIX #9] after_mention_insert ← Notification 'Mention' ដំណើរការហើយ
DROP TRIGGER IF EXISTS after_mention_insert $$
CREATE TRIGGER after_mention_insert
AFTER INSERT ON mentions
FOR EACH ROW
BEGIN
    IF NEW.UserID <> NEW.MentionedBy THEN
        INSERT INTO notifications (UserID, TriggerID, Type, IsRead)
        VALUES (NEW.UserID, NEW.MentionedBy, 'Mention', 0);
    END IF;
END$$


-- Update LastActive + user_presence ពេលផ្ញើសារ
DROP TRIGGER IF EXISTS after_message_sent $$
CREATE TRIGGER after_message_sent
AFTER INSERT ON messages
FOR EACH ROW
BEGIN
    UPDATE users
    SET LastActive = NOW()
    WHERE UserID = NEW.SenderID;

    INSERT INTO user_presence (UserID, IsOnline, LastSeen)
    VALUES (NEW.SenderID, 1, NOW())
    ON DUPLICATE KEY UPDATE IsOnline = 1, LastSeen = NOW();
END$$


DELIMITER ;


-- =================================================================
-- ២០. STORED PROCEDURES
-- =================================================================

DELIMITER $$

-- -------------------------------------------------------------------
-- GetUserFeed (2 params ← ត្រូវជាមួយ PHP: CALL GetUserFeed(:user_id, :limit))
-- -------------------------------------------------------------------
DROP PROCEDURE IF EXISTS GetUserFeed $$
CREATE PROCEDURE GetUserFeed(IN p_user_id INT, IN p_limit INT)
BEGIN
    SELECT
        p.*,
        u.Username,
        u.ProfilePic,
        u.IsVerified,
        COUNT(DISTINCT CASE WHEN i.Type IN ('like','love','haha') THEN i.UserID END) AS reaction_count,
        COUNT(DISTINCT CASE WHEN i.Type = 'comment'              THEN i.UserID END) AS comment_count,
        p.ShareCount                                                                  AS share_count,
        MAX(CASE WHEN i.UserID = p_user_id
                  AND i.Type IN ('like','love','haha') THEN 1 ELSE 0 END)            AS user_reacted,
        CASE WHEN EXISTS (
            SELECT 1 FROM saved_posts
            WHERE PostID = p.PostID AND UserID = p_user_id
        ) THEN 1 ELSE 0 END                                                           AS user_saved,
        opu.Username   AS original_poster_name,
        opu.ProfilePic AS original_poster_pic,
        op.Content     AS original_content
    FROM posts p
    JOIN  users u    ON p.UserID = u.UserID
    LEFT JOIN posts op   ON p.OriginalPostID = op.PostID
    LEFT JOIN users opu  ON op.UserID = opu.UserID
    LEFT JOIN interactions i ON p.PostID = i.PostID
    WHERE
        p.Privacy = 'Public'
        OR (p.Privacy = 'Friends' AND EXISTS (
            SELECT 1 FROM friends
            WHERE ((RequesterID = p_user_id AND ReceiverID = p.UserID)
                OR (ReceiverID  = p_user_id AND RequesterID = p.UserID))
              AND Status = 'Accepted'
        ))
        OR p.UserID = p_user_id
    GROUP BY p.PostID
    ORDER BY p.CreatedAt DESC
    LIMIT p_limit;
END$$


-- -------------------------------------------------------------------
-- AcceptFriendRequest (ត្រូវជាមួយ FriendModel.php)
-- -------------------------------------------------------------------
DROP PROCEDURE IF EXISTS AcceptFriendRequest $$
CREATE PROCEDURE AcceptFriendRequest(IN req_id INT, IN rec_id INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;

    START TRANSACTION;
        UPDATE friends
        SET Status = 'Accepted'
        WHERE RequesterID = req_id AND ReceiverID = rec_id;

        INSERT INTO notifications (UserID, TriggerID, Type)
        VALUES (req_id, rec_id, 'FriendRequest');
    COMMIT;
END$$


-- -------------------------------------------------------------------
-- [FIX #3] [FIX #6] [FIX #7]
-- CreatePostWithHashtags
--   - 9 params (+ p_group_id) ← ត្រូវជាមួយ PostModel.php
--   - 1 Version តែប៉ុណ្ណោះ
--   - Hashtag Loop ដំណើរការពិតប្រាកដ
--   - DROP + CREATE (មិនប្រើ CREATE OR REPLACE ដែល Invalid)
-- -------------------------------------------------------------------
DROP PROCEDURE IF EXISTS CreatePostWithHashtags $$
CREATE PROCEDURE CreatePostWithHashtags(
    IN p_user_id    INT,
    IN p_content    TEXT,
    IN p_image_path TEXT,
    IN p_privacy    VARCHAR(50),
    IN p_location   VARCHAR(255),
    IN p_feeling    VARCHAR(100),
    IN p_category   VARCHAR(50),
    IN p_hashtags   TEXT,
    IN p_group_id   INT           -- [FIX #3] Parameter ថ្មី ត្រូវជាមួយ PHP
)
BEGIN
    DECLARE last_post_id INT;
    DECLARE tag_name     VARCHAR(100);
    DECLARE tag_id       INT;
    DECLARE remaining    TEXT;

    START TRANSACTION;

        -- ១. Insert Post (NULLIF ប្រែ 0 ជា NULL សម្រាប់ GroupID)
        INSERT INTO posts
            (UserID, Content, ImagePath, Privacy, Location, Feeling, Category, GroupID, CreatedAt)
        VALUES
            (p_user_id, p_content, p_image_path, p_privacy,
             p_location, p_feeling, p_category, NULLIF(p_group_id, 0), NOW());

        SET last_post_id = LAST_INSERT_ID();

        -- ២. Process Hashtags (comma-separated: 'khmer,study,it')
        IF p_hashtags IS NOT NULL AND TRIM(p_hashtags) != '' THEN
            SET remaining = CONCAT(TRIM(p_hashtags), ',');

            WHILE LOCATE(',', remaining) > 0 DO
                SET tag_name  = TRIM(SUBSTRING(remaining, 1, LOCATE(',', remaining) - 1));
                SET remaining = SUBSTRING(remaining, LOCATE(',', remaining) + 1);

                IF tag_name != '' THEN
                    -- Insert hashtag ប្រសិនបើមិនទាន់មាន
                    INSERT IGNORE INTO hashtags (TagName) VALUES (tag_name);

                    -- Get HashtagID
                    SELECT HashtagID INTO tag_id
                    FROM hashtags
                    WHERE TagName = tag_name
                    LIMIT 1;

                    -- Link post ទៅ hashtag
                    INSERT IGNORE INTO post_hashtags (PostID, HashtagID)
                    VALUES (last_post_id, tag_id);
                END IF;
            END WHILE;
        END IF;

    COMMIT;
END$$


DELIMITER ;


-- =================================================================
-- ២១. SCHEDULED EVENTS (MySQL Event Scheduler)
-- ត្រូវបើក: SET GLOBAL event_scheduler = ON;
-- =================================================================

CREATE EVENT IF NOT EXISTS auto_delete_expired_stories
ON SCHEDULE EVERY 1 HOUR
DO
    DELETE FROM stories WHERE ExpiresAt < NOW();

CREATE EVENT IF NOT EXISTS clean_old_notifications
ON SCHEDULE EVERY 1 DAY
DO
    DELETE FROM notifications WHERE CreatedAt < DATE_SUB(NOW(), INTERVAL 30 DAY);


-- =================================================================
-- ២២. INITIAL DATA  —  SuperAdmin Setup
--
-- របៀបប្រើ:
--   ១. ចុះឈ្មោះ Account ដោយ Email: shy784162@gmail.com តាម UI
--   ២. Run Statement ខាងក្រោម ដើម្បី Upgrade ជា SuperAdmin
--
-- NOTE: Password UPDATE ជ្រើសរើស Optional
--   ដើម្បី Reset Password → Run PHP command ដើម្បីបង្កើត bcrypt hash:
--   php -r "echo password_hash('YourNewPassword', PASSWORD_DEFAULT);"
--   ហើយ copy hash ទៅដាក់ Statement ខាងក្រោម
-- =================================================================

UPDATE users
SET
    Role          = 'SuperAdmin',
    AccountStatus = 'Active'
WHERE Email = 'shy784162@gmail.com';

-- Optional Password Reset (Uncomment + Replace hash ពី PHP):
-- UPDATE users
-- SET Password = '$2y$10$REPLACE_WITH_HASH_FROM_PHP_password_hash_FUNCTION'
-- WHERE Email = 'shy784162@gmail.com';

-- #!mysql

-- #{ table
    -- #{ bans
        CREATE TABLE IF NOT EXISTS bans (
            player VARCHAR(16) PRIMARY KEY NOT NULL,
            moderator VARCHAR(16) NOT NULL,
            reason VARCHAR(100),
            time TIMESTAMP NOT NULL,
            expire TIMESTAMP NULL DEFAULT NULL
        );
    -- #}
    -- #{ mutes
        CREATE TABLE IF NOT EXISTS mutes (
            player VARCHAR(16) PRIMARY KEY NOT NULL,
            moderator VARCHAR(16) NOT NULL,
            reason VARCHAR(100),
            time TIMESTAMP NOT NULL,
            expire TIMESTAMP NULL DEFAULT NULL
        );
    -- #}
    -- #{ players
        CREATE TABLE IF NOT EXISTS players (
            player VARCHAR(16) PRIMARY KEY NOT NULL,
            ban_points INT NOT NULL,
            mute_points INT NOT NULL,
            notifications TINYINT NOT NULL
        );
    -- #}
    -- #{ banlogs
        CREATE TABLE IF NOT EXISTS banlogs (
            player VARCHAR(16) PRIMARY KEY NOT NULL,
            moderator VARCHAR(16) NOT NULL,
            reason VARCHAR(100),
            time TIMESTAMP NOT NULL,
            expire TIMESTAMP NULL DEFAULT NULL
        );
    -- #}
    -- #{ mutelogs
        CREATE TABLE IF NOT EXISTS mutelogs (
            player VARCHAR(16) PRIMARY KEY NOT NULL,
            moderator VARCHAR(16) NOT NULL,
            reason VARCHAR(100),
            time TIMESTAMP NOT NULL,
            expire TIMESTAMP NULL DEFAULT NULL
        );
    -- #}
-- #}

-- #{ bans
    -- #{ add
        -- # :player string
        -- # :moderator string
        -- # :reason string
        -- # :time string
        -- # :expire string 0
        INSERT INTO bans(player, moderator, reason, time, expire)
        VALUES (:player, :moderator, :reason, :time, :expire);
    -- #}
    -- #{ addLog
        -- # :player string
        -- # :moderator string
        -- # :reason string
        -- # :time string
        -- # :expire string 0
        INSERT INTO banlogs(player, moderator, reason, time, expire)
        VALUES (:player, :moderator, :reason, :time, :expire);
    -- #}
    -- #{ getLog
        -- # :player string
        SELECT * from banlogs WHERE player = :player;
    -- #}
    -- #{ remove
        -- # :player string
        DELETE FROM bans WHERE player = :player;
    -- #}
    -- #{ edit
        -- # :player string
        -- # :newTime string 0
        UPDATE bans SET expire = :newTime WHERE player = :player;
    -- #}
    -- #{ get
        -- # :player string
        SELECT * from bans WHERE player = :player;
    -- #}
    -- #{ getAll
        SELECT * from bans;
    -- #}
    -- #{ check
        -- # :player string
        SELECT EXISTS(SELECT * FROM bans WHERE player = :player);
    -- #}
-- #}

-- #{ mutes
    -- #{ add
        -- # :player string
        -- # :moderator string
        -- # :reason string
        -- # :time string
        -- # :expire string 0
        INSERT INTO mutes(player, moderator, reason, time, expire)
        VALUES (:player, :moderator, :reason, :time, :expire);
    -- #}
    -- #{ addLog
        -- # :player string
        -- # :moderator string
        -- # :reason string
        -- # :time string
        -- # :expire string 0
        INSERT INTO mutelogs(player, moderator, reason, time, expire)
        VALUES (:player, :moderator, :reason, :time, :expire);
    -- #}
    -- #{ getLog
        -- # :player string
        SELECT * from mutelogs WHERE player = :player;
    -- #}
    -- #{ remove
        -- # :player string
        DELETE FROM mutes WHERE player = :player;
    -- #}
    -- #{ edit
        -- # :player string
        -- # :newTime string 0
        UPDATE mutes SET expire = :newTime WHERE player = :player;
    -- #}
    -- #{ get
        -- # :player string
        SELECT * from mutes WHERE player = :player;
    -- #}
    -- #{ getAll
        SELECT * from mutes;
    -- #}
    -- #{ check
        -- # :player string
        SELECT EXISTS(SELECT * FROM mutes WHERE player = :player);
    -- #}
-- #}

-- #{ player
    -- #{ create
        -- # :player string
        INSERT INTO players(player, ban_points, mute_points, notifications)
        VALUES(:player, 0, 0, 0);
    -- #}
    -- #{ updateNotifications
        -- # :player string
        -- # :notifications int
        UPDATE players SET notifications = :notifications WHERE player = :player;
    -- #}
    -- #{ getNotifications
        -- # :player string
        SELECT players.notifications FROM players WHERE player = :player;
    -- #}
    -- #{ addBanPoint
        -- # :player string
        UPDATE players SET ban_points = ban_points + 1 WHERE player = :player;
    -- #}
    -- #{ addMutePoint
        -- # :player string
        UPDATE players SET mute_points = mute_points + 1 WHERE player = :player;
    -- #}
    -- #{ subBanPoint
        -- # :player string
        UPDATE players SET ban_points = ban_points - 1 WHERE player = :player;
    -- #}
    -- #{ subMutePoint
        -- # :player string
        UPDATE players SET mute_points = mute_points - 1 WHERE player = :player;
    -- #}
    -- #{ getBanPoints
        -- # :player string
        SELECT players.ban_points FROM players WHERE player = :player;
    -- #}
    -- #{ getMutePoints
        -- # :player string
        SELECT players.mute_points FROM players WHERE player = :player;
    -- #}
    -- #{ check
        -- # :player string
        SELECT EXISTS(SELECT * FROM players WHERE player = :player);
    -- #}
-- #}
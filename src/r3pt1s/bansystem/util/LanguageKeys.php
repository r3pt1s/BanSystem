<?php

namespace r3pt1s\bansystem\util;

/**
 * credits to my goat ChatGPT (love you <3)
 */
final readonly class LanguageKeys {

    public const WATERDOGPE_INFO = "waterdogpe.info";
    public const RAW_PERMANENTLY = "raw.permanently";
    public const RAW_YES = "raw.yes";
    public const RAW_NO = "raw.no";
    public const NO_PERMS = "no.perms";

    public const COMMAND_DESCRIPTION_BAN = "command.description.ban";
    public const COMMAND_DESCRIPTION_BAN_IDS = "command.description.ban_ids";
    public const COMMAND_DESCRIPTION_BAN_INFO = "command.description.ban_info";
    public const COMMAND_DESCRIPTION_BAN_LIST = "command.description.ban_list";
    public const COMMAND_DESCRIPTION_BAN_LOG = "command.description.ban_log";
    public const COMMAND_DESCRIPTION_EDIT_BAN = "command.description.edit_ban";
    public const COMMAND_DESCRIPTION_TEMP_BAN = "command.description.temp_ban";
    public const COMMAND_DESCRIPTION_UNBAN = "command.description.unban";
    public const COMMAND_DESCRIPTION_EDIT_MUTE = "command.description.edit_mute";
    public const COMMAND_DESCRIPTION_MUTE = "command.description.mute";
    public const COMMAND_DESCRIPTION_MUTE_IDS = "command.description.mute_ids";
    public const COMMAND_DESCRIPTION_MUTE_INFO = "command.description.mute_info";
    public const COMMAND_DESCRIPTION_MUTE_LIST = "command.description.mute_list";
    public const COMMAND_DESCRIPTION_MUTE_LOG = "command.description.mute_log";
    public const COMMAND_DESCRIPTION_TEMP_MUTE = "command.description.temp_mute";
    public const COMMAND_DESCRIPTION_UNMUTE = "command.description.unmute";
    public const COMMAND_DESCRIPTION_NOTIFY = "command.description.notify";
    public const COMMAND_DESCRIPTION_CLEAR_WARNS = "command.description.clear_warns";
    public const COMMAND_DESCRIPTION_WARN = "command.description.warn";
    public const COMMAND_DESCRIPTION_WARNS = "command.description.warns";
    public const COMMAND_DESCRIPTION_KICK = "command.description.kick";
    public const COMMAND_DESCRIPTION_VANISH = "command.description.vanish";
    public const COMMAND_DESCRIPTION_FREEZE = "command.description.freeze";
    public const COMMAND_DESCRIPTION_MUTE_CHAT = "command.description.mute_chat";
    public const COMMAND_DESCRIPTION_SPECTATE = "command.description.spectate";

    public const PUNISH_FAILED_YOURSELF = "punish.failed.yourself";
    public const PLAYER_NOT_BANNED = "player.not.banned";
    public const PLAYER_NOT_MUTED = "player.not.muted";
    public const PLAYER_NOT_FOUND = "player.not.found";
    public const PLAYER_NOT_ONLINE = "player.not.online";
    public const CHECK_EXISTS_FAILED = "check.exists.failed";
    public const CHECK_BAN_POINTS_FAILED = "check.ban_points.failed";
    public const CHECK_MUTE_POINTS_FAILED = "check.mute_points.failed";
    public const CHECK_BAN_LOGS_FAILED = "check.ban_logs.failed";
    public const CHECK_MUTE_LOGS_FAILED = "check.mute_logs.failed";
    public const VALID_TIME_FORMAT = "valid.time_format";

    public const NOTIFICATIONS_ENABLED = "notifications.enabled";
    public const NOTIFICATIONS_DISABLED = "notifications.disabled";

    public const BAN_SUCCESS = "ban.success";
    public const BAN_ALREADY_BANNED = "ban.already_banned";
    public const BAN_EVENT_CANCELLED = "ban.event.cancelled";
    public const BAN_VALID_BANID = "ban.valid_banid";

    public const MUTE_SUCCESS = "mute.success";
    public const MUTE_ALREADY_MUTED = "mute.already_muted";
    public const MUTE_EVENT_CANCELLED = "mute.event.cancelled";
    public const MUTE_VALID_MUTEID = "mute.valid_muteid";

    public const BAN_INFO_MESSAGE_NOT_BANNED = "ban_info.message.not_banned";
    public const BAN_INFO_MESSAGE_BANNED = "ban_info.message.banned";

    public const MUTE_INFO_MESSAGE_NOT_MUTED = "mute_info.message.not_muted";
    public const MUTE_INFO_MESSAGE_MUTED = "mute_info.message.muted";

    public const BAN_LOG_NONE = "ban_log.none";
    public const MUTE_LOG_NONE = "mute_log.none";

    public const EDIT_BAN_EDIT_FAILED = "edit_ban.edit.failed";
    public const EDIT_BAN_SUCCESS = "edit_ban.success";
    public const EDIT_BAN_EVENT_CANCELLED = "edit_ban.event.cancelled";

    public const EDIT_MUTE_EDIT_FAILED = "edit_mute.edit.failed";
    public const EDIT_MUTE_SUCCESS = "edit_mute.success";
    public const EDIT_MUTE_EVENT_CANCELLED = "edit_mute.event.cancelled";

    public const UNBAN_SUCCESS = "unban.success";
    public const UNBAN_EVENT_CANCELLED = "unban.event.cancelled";

    public const UNMUTE_SUCCESS = "unmute.success";
    public const UNMUTE_EVENT_CANCELLED = "unmute.event.cancelled";

    public const KICK_SUCCESS = "kick.success";
    public const KICK_EVENT_CANCELLED = "kick.event.cancelled";
    public const KICK_FAILED = "kick.failed";

    public const WARNS_CLEARED = "warns.cleared";
    public const WARNS_NONE = "warns.none";

    public const WARN_SUCCESS = "warn.success";
    public const WARN_EVENT_CANCELLED = "warn.event.cancelled";

    public const CHAT_MUTE_MUTED_SUCCESS = "chat_mute.muted.success";
    public const CHAT_MUTE_UNMUTED_SUCCESS = "chat_mute.unmuted.success";

    public const CHAT_MUTE_MUTED_GLOBAL = "chat_mute.muted.global";
    public const CHAT_MUTE_UNMUTED_GLOBAL = "chat_mute.unmuted.global";

    public const FREEZE_FROZEN = "freeze.frozen";
    public const FREEZE_RELEASED = "freeze.released";
    public const FREEZE_FREEZE_SUCCESS = "freeze.freeze.success";
    public const FREEZE_RELEASE_SUCCESS = "freeze.release.success";

    public const SPECTATE_START = "spectate.start";
    public const SPECTATE_STOP = "spectate.stop";

    public const VANISH_VANISHED = "vanish.vanished";
    public const VANISH_SHOWN = "vanish.shown";

    public const UI_BAN_LOGS_TITLE = "ui.ban.logs.title";
    public const UI_BAN_LOGS_TEXT = "ui.ban.logs.text";
    public const UI_BAN_LOGS_VIEW_TITLE = "ui.ban.logs.view.title";
    public const UI_BAN_LOGS_VIEW_TEXT = "ui.ban.logs.view.text";

    public const UI_MUTE_LOGS_TITLE = "ui.mute.logs.title";
    public const UI_MUTE_LOGS_TEXT = "ui.mute.logs.text";
    public const UI_MUTE_LOGS_VIEW_TITLE = "ui.mute.logs.view.title";
    public const UI_MUTE_LOGS_VIEW_TEXT = "ui.mute.logs.view.text";

    public const UI_WARN_LOGS_TITLE = "ui.warn.logs.title";
    public const UI_WARN_LOGS_TEXT = "ui.warn.logs.text";
    public const UI_WARN_LOGS_VIEW_TITLE = "ui.warn.logs.view.title";
    public const UI_WARN_LOGS_VIEW_TEXT = "ui.warn.logs.view.text";

    public const SCREEN_BAN = "screen.ban";
    public const SCREEN_MUTE = "screen.mute";
    public const SCREEN_CHAT_MUTE = "screen.chat_mute";
    public const SCREEN_WARN = "screen.warn";
    public const SCREEN_KICK = "screen.kick";

    public const NOTIFY_BAN_AUTO = "notify.ban.auto";
    public const NOTIFY_BAN_MANUAL = "notify.ban.manual";
    public const NOTIFY_BAN_EDITED = "notify.ban.edited";

    public const NOTIFY_UNBAN_AUTO = "notify.unban.auto";
    public const NOTIFY_UNBAN_MANUAL = "notify.unban.manual";

    public const NOTIFY_MUTE_AUTO = "notify.mute.auto";
    public const NOTIFY_MUTE_MANUAL = "notify.mute.manual";
    public const NOTIFY_MUTE_EDITED = "notify.mute.edited";

    public const NOTIFY_UNMUTE_AUTO = "notify.unmute.auto";
    public const NOTIFY_UNMUTE_MANUAL = "notify.unmute.manual";

    public const NOTIFY_WARN_ADD = "notify.warn.add";
    public const NOTIFY_WARN_REMOVE = "notify.warn.remove";
    public const NOTIFY_WARN_CLEARED = "notify.warn.cleared";

    public const NOTIFY_KICK = "notify.kick";

    public const MUTE_COMMAND_BLOCKED = "mute.command.blocked";
}
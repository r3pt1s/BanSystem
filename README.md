# BanSystem
A system for pocketmine servers.

## Integrated systems 
- BanSystem to ban players
- MuteSystem to mute players
- WarnSystem to warn players
- KickSystem to kick players
- NotifySystem to receive notifications

## Configurable 
- Max warnings
- Action when max warnings reached
- Custom action reason when max warnings reached
- Blocked commands for muted players
- Activate or deactivate the creation of banlogs and mutelogs
- Custom banids and muteids
- Custom prefix
- Custom paths

## Commands - Permissions (Ban)
- /ban [player] [banId] - ban.command
- /tempban [player] [reason] [time] - tempban.command
- /editban [player] [add|sub] [time] - editban.command
- /unban [player] - unban.command
- /baninfo [player] - baninfo.command
- /banlog [player] - banlog.command
- /banids - banids.command
- /banlist - banlist.command

## Commands - Permissions (Mute)
- /mute [player] [muteId] - mute.command
- /tempmute [player] [reason] [time] - tempmute.command
- /editmute [player] [add|sub] [time] - editmute.command
- /unmute [player] - unmute.command
- /muteinfo [player] - muteinfo.command
- /mutelog [player] - mutelog.command
- /muteids - muteids.command
- /mutelist - mutelist.command

## Commands - Permissions (Warn)
- /warn [player] [reason] - warn.command
- /warns [player] - warns.command
- /resetwarns [player] - resetwarns.command

## Commands - Permissions (Kick)
- /kick [player] [reason] - kick.command

## Commands - Permissions (Notify)
- /notify - notify.command

## Extra Permissions
- notify.receive - To receive notifications 
- ban.bypass - To bypass a ban
- mute.bypass - To bypass a mute

## Issue / Bug report 
- [Create an Issue](https://github.com/PocketCloudSystem/CloudSystem/issues/new)

# Made with ❤️ by r3pt1s!

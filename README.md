# BanSystem
A system for pocketmine servers.

## Changes
- Added MySQL Support (Provider)
- Fixxed some stupid bugs

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
- /ban [player] [banId] - bansystem.ban.command
- /tempban [player] [reason] [time] - bansystem.tempban.command
- /editban [player] [add|sub] [time] - bansystem.editban.command
- /unban [player] - bansystem.unban.command
- /baninfo [player] - bansystem.baninfo.command
- /banlog [player] -bansystem.banlog.command
- /banids - bansystem.banids.command
- /banlist - bansystem.banlist.command

## Commands - Permissions (Mute)
- /mute [player] [banId] - bansystem.mute.command
- /tempmute [player] [reason] [time] - bansystem.tempmute.command
- /editmute [player] [add|sub] [time] - bansystem.editmute.command
- /unmute [player] - bansystem.unmute.command
- /muteinfo [player] - bansystem.muteinfo.command
- /mutelog [player] - bansystem.mutelog.command
- /muteids - bansystem.muteids.command
- /mutelist - bansystem.mutelist.command

## Commands - Permissions (Warn)
- /warn [player] [reason] - bansystem.warn.command
- /warns [player] - bansystem.warns.command
- /resetwarns [player] - bansystem.resetwarns.command

## Commands - Permissions (Kick)
- /kick [player] [reason] - bansystem.kick.command

## Commands - Permissions (Notify)
- /notify - bansystem.notify.command

## Extra Permissions
- bansystem.notify.receive - To receive notifications 
- bansystem.ban.bypass - To bypass a ban
- bansystem.mute.bypass - To bypass a mute

## Issue / Bug report 
- [Create an Issue](https://github.com/PocketCloudSystem/CloudSystem/issues/new)

# Made with ❤️ by r3pt1s!

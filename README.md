# BanSystem
A system for pocketmine servers.

## Integrated systems
| System               | Description           |
|----------------------|-----------------------|
| BanSystem            | Manage bans           |
| MuteSystem           | Manage mutes          |
| WarnSystem           | Manage warns          |
| NotificationSystem   | Receive notifications |

## Features
- Ban or mute players temporarily 
- Warn or kick players with a reason
- MySQL Support
- Notifications for team members

## Commands
| Command                                       | Description                                            | Permission                     |
|-----------------------------------------------|--------------------------------------------------------|--------------------------------|
| `/ban <player> <banId>`                       | Ban a player                                           | bansystem.command.ban          |
| `/tempban <player> <reason> <time>`           | Ban a player temporarily                               | bansystem.command.tempban      |
| `/baninfo <player>`                           | Get information about a player                         | bansystem.command.baninfo      | 
| `/banlog <player>`                            | See a list of every ban from a player                  | bansystem.command.banlog       |
| `/editban <player> <add OR sub> <time>`       | Edit a ban                                             | bansystem.command.editban      |
| `/unban <player> [mistake: false]`            | Unban a player and decide if the ban was a mistake     | bansystem.command.unban        |
| `/banids`                                     | See a list of all banids                               | bansystem.command.banids       |
| `/banlist`                                    | See a list of all bans                                 | bansystem.command.banlist      |
| `/mute <player> <muteId>`                     | Mute a player                                          | bansystem.command.mute         |
| `/tempmute <player> <reason> <time>`          | Mute a player temporarily                              | bansystem.command.tempmute     |
| `/muteinfo <player>`                          | Get information about a player                         | bansystem.command.muteinfo     | 
| `/mutelog <player>`                           | See a list of every mute from a player                 | bansystem.command.mutelog      |
| `/editmute <player> <add OR sub> <time>`      | Edit a mute                                            | bansystem.command.editmute     |
| `/unmute <player> [mistake: false]`           | Unmute a player and decide if the mute was a mistake   | bansystem.command.unmute       |
| `/muteids`                                    | See a list of all muteids                              | bansystem.command.muteids      |
| `/mutelist`                                   | See a list of all mutes                                | bansystem.command.mutelist     |
| `/warn <player> [reason]`                     | Warn a player                                          | bansystem.command.warn         |
| `/warns <player>`                             | See a list of all warns from a player                  | bansystem.command.warns        |
| `/clearwarns <player>`                        | Clear all warns from a player                          | bansystem.command.clearwarns   |
| `/kick <player> [reason]`                     | Kick a player                                          | bansystem.command.kick         |
| `/notify`                                     | Enable or disable notifications                        | bansystem.command.notify       |

## Permissions
| Permission                        | Description            |
|-----------------------------------|------------------------|
| bansystem.bypass.ban              | Bypass a ban           |
| bansystem.bypass.mute             | Bypass a mute          |
| bansystem.bypass.kick             | Bypass a kick          |
| bansystem.receive.notifications   | Receive notifications  |

## Configurable 
- Max warnings
- Action when max warnings reached
- Custom action reason and duration when max warnings reached
- Blocked commands for muted players
- Activate or deactivate the creation of banlogs and mutelogs
- Custom banids and muteids
- Custom prefix
- Custom paths

## Issue / Bug report 
- [Create an Issue](https://github.com/PocketCloudSystem/CloudSystem/issues/new)

# Made with ❤️ by r3pt1s!
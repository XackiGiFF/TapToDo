# TapToDo - v2.4.0
**New TapToDo for PMMP 4.0 API Minecraft.**

## Features:
- Simple and user-friendly
- very easy setup
- Add macros on block
- Work in game
- Tap to set command on block
- Work with all block, set on position block
- Multiworld

## TODOs
If you've new ideas and features for this plugin, please open an issue :)

## Bugreport
You've found a Bug?
- Go to [Issues](https://github.com/XackiGiFF/TapToDo/issues)
- Click on [New Issue](https://github.com/XackiGiFF/TapToDo/issues/new/choose)
- Write your bug with all information that you have down
- Send Issue
- Wait, I where answer you

## Commands
| **Command**                                    | **Description**                  | **Permission**               |
|------------------------------------------------|----------------------------------|------------------------------|
| `/t`                                           | Show help.                       | `taptodo.command`            |
| <code>/t add / a </code>                       | Add a macros.                    | `taptodo.command.add`        |
| <code>/t del / d</code>                        | Delete a last macros.            | `taptodo.command.del`        |
| <code>/t delall / da</code>                    | Delete all macros.               | `taptodo.command.delall`     |
| <code>/t list / ls / l </code>                 | Show macros on block.            | `taptodo.command.list`       |

## License:
This plugin is licensed under the [Apache License 2.0](/LICENSE)! Plugin by Falk, XackiGiFF modificate!

### How To Use
### Variables
**If you include one of these in your command it will be replaced by the appropriate value when the command is run, they are case sensitive.**
| **Variable**              | **Description**                       |
|---------------------------|---------------------------------------|
| %p                        | username of the player                |
| %x                        | x coord of the player                 |
| %y                        | y coord of the player                 |
| %z                        | z coord of the player                 |
| %l                        | name of the level the player is in    |
| %ip                       | IP of the player                      |
| %n                        | display name of the player            |

### Behaviour Switches
**These change the command execution behaviour, more will be coming soon. They can be added anywhere in the command as it will be removed when the command is run.**
| **Variable**              | **Description**                                                                                           |
|---------------------------|-----------------------------------------------------------------------------------------------------------|
| %pow                      | Makes the command run as console.                                                                         |
| %op                       | Gives the player running the command OP permissions, these will be revoked after executing the command.   |         


### Usage Example:
**You want to make a block that whenever it is tapped the player who tapped it is killed. You could accomplish this in two ways:**

### Console with Variable
**This way the command will be run as the console with the player's name added in when it is run.**
<code> /t add kill %p </code>

### Run as player
**If you want the command to be run as the player (with their permissions), you can add %safe anywhere in the command. If their is a non-OP on your server and they tap a block with the following command attached it won't execute.**
<code> /t add stop%safe </code>

### Run with OP permission
**You can add %op anywhere in the command to give the player tapping the block temporary OP and execute the command as them. This allows you to do things like. Warning! This will execute from the player allowing them to see command output.**
<code> /t add gamemode 1%op </code>

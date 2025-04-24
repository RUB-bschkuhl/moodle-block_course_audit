# Course audit Block for Moodle

This block helps analyze and convert course content.

## Features
- Section analysis
- Rule-based recommendations
- User tours for guided course exploration
- Dynamic "Audit" tour

## Installation
1. Copy the course_audit directory to your Moodle blocks directory
2. Install on reload as usual

## Usage
Add the block to your course page to begin analyzing your course structure by clicking on the MIau companion.

## TODO
Rest of the Documentation will follow when the plugin reaches completion

## License
GNU GPL v3 or later
http://www.gnu.org/copyleft/gpl.html

## Author
Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>

## Support
For support and bug reports, please use the GitHub issue tracker or contact the author directly.

## Quick copy paste for deletion of non core tour data
delete from mdl_tool_usertours_tours where id > 5;
delete from mdl_tool_usertours_steps where tourid > 5;
delete from mdl_block_course_audit_tours;
delete from mdl_block_course_audit_results;

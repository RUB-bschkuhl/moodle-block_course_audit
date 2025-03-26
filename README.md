# Course audit Block for Moodle

This block helps analyze and convert course content.

## Features
- Section analysis
- Activity flow visualization
- Rule-based recommendations

## Installation
1. Copy the course_audit directory to your Moodle blocks directory
2. Visit the notifications page to install

## Usage
Add the block to your course page to begin analyzing your course structure.

## Changelog

### 2025-03-26
- Removed legacy code
- Improved structure

### 2025-03-25
- Fixed external API class for section analysis
- Fixed block instantiation in external API
- Improved error handling and logging
- Modified files:
  - blocks/course_audit/classes/external/get_section_analysis.php
  - blocks/course_audit/block_course_audit.php

## Overview
The Course audit block is a Moodle plugin that helps analyze and visualize course sections and their content. It provides an interactive interface for reviewing course structure, availability conditions, and section-specific analysis.

## Features
- Interactive section navigation with visual indicators
- Detailed analysis of course sections
- Support for both block and inline display modes
- Course structure analysis

## Requirements
- Moodle 4.4 or higher (tested up to 4.5)

## Installation
1. Download the plugin
2. Extract the contents to /blocks/course_audit in your Moodle installation
3. Visit Site Administration > Notifications to complete the installation
4. Add the block to your course page

## Usage
### Adding the Block
1. Turn editing on in your course
2. Add the "Course audit" block from the "Add a block" menu

### Features
- **Section Analysis**: Review each section's structure and content
- **Display Modes**: 
  - Block Mode: Analysis shown in the block
  - Inline Mode: Analysis shown next to each section
- **Navigation**: Use the pagination controls to move between sections
- **Visual Indicators**: Highlights current section being analyzed
- **Floating Analysis**: When in inline mode, analysis panels float next to sections

### Permissions
- Requires 'block/course_audit:view' capability to view analysis
- Requires 'moodle/course:update' capability for full functionality

## License
GNU GPL v3 or later
http://www.gnu.org/copyleft/gpl.html

## Author
Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>

## Support
For support and bug reports, please use the GitHub issue tracker or contact the author directly.

---
Last updated: March 21, 2024

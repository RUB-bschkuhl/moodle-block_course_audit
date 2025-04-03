# Course audit Block for Moodle

This block helps analyze and convert course content.

## Features
- Section analysis
- Activity flow visualization
- Rule-based recommendations
- User tours for guided course exploration

## Installation
1. Copy the course_audit directory to your Moodle blocks directory
2. Visit the notifications page to install

## Usage
Add the block to your course page to begin analyzing your course structure.

### Using the Tour Manager
The block includes a tour manager that can create interactive guided tours for users:

```php
// Example: Create a course introduction tour
$tourmanager = new \block_course_audit\tour\manager();
$tour = $tourmanager->create_tour(
    'Course Introduction',
    'Introduce users to course features',
    '/course/view.php\?id=\d+',
    ['displaystepnumbers' => true]
);

// Add steps to the tour
$tourmanager->add_step('Welcome', 'Welcome to the course!', 'body', '.course-content');
$tourmanager->add_step('Navigation', 'Use this menu to navigate', 'selector', '.navbar');
```

## Changelog

### 2025-04-03
- Rework #2 
- Frontend replacement with concept of "dynamic user tours"
  - Based on moodle core feature user tours
- Added Tour Manager class for creating and managing user tours

### 2025-03-26
- Removed legacy code
- Improved structure
- Code review

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
- Guided user tours for better course exploration and understanding

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
- **User Tours**: Create guided tours to help users understand course features

### Tour Manager
The block includes a tour manager that allows you to create interactive guided tours:

- **Create Tours**: Build tours for specific pages with multiple steps
- **Target Elements**: Target specific page elements using CSS selectors
- **Customization**: Configure placement, backdrop, and behavior
- **Manage Tours**: Add, update, reset or remove tours

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
Last updated: April 3, 2024

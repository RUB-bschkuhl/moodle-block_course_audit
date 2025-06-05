# Moodle Plugin Rule System Information - TODO List

This document outlines the information needed to build a dynamic rule creation system for the Course Audit plugin.

## Task List

- [x] **1. Identify all available Moodle Modules (mods):**
    - [x] Find the directory/mechanism Moodle uses to list all installable modules.
    - [x] List the names/identifiers of each module.
    - [*] *Location in Moodle code (to be filled):* `moodle-500/mod/`
    - [*] *Extracted Data (to be filled):* workshop, wiki, url, subsection, scorm, resource, quiz, qbank, page, lti, lesson, label, imscp, h5pactivity, glossary, forum, folder, feedback, data, choice, book, bigbluebuttonbn, assign (List derived from directory names in `moodle-500/mod/`)

- [x] **2. Identify all Question Types for Quizzes:**
    - [x] Find where Moodle defines its question types.
    - [x] List the names/identifiers of each question type.
    - [*] *Location in Moodle code (to be filled):* `moodle-500/question/type/`
    - [*] *Extracted Data (to be filled):* truefalse, shortanswer, randomsamatch, random, ordering, numerical, multichoice, multianswer, missingtype, match, gapselect, essay, description, ddwtos, ddmarker, ddimageortext, calculatedsimple, calculatedmulti, calculated (List derived from directory names in `moodle-500/question/type/`)

- [ ] **3. Identify Common and Unique Settings for Modules (mods):**
    - [x] Find the base class/interface for Moodle modules.
    - [x] Identify how common settings are defined (e.g., visibility, group mode).
    - [ ] For a selection of representative modules (e.g., forum, assign, quiz, page, url):
        - [ ] Identify their unique settings.
    - [*] *Location in Moodle code for common settings (to be filled):* `moodle-500/course/moodleform_mod.php` (primarily in `standard_coursemodule_elements()` and `init_features()`). Individual modules extend this.
    - [*] *Location in Moodle code for unique settings (examples) (to be filled):* e.g., `moodle-500/mod/assign/mod_form.php`, `moodle-500/mod/quiz/mod_form.php` etc. (in their `definition()` method).
    - [*] *Extracted Data (Common Settings - to be filled based on `moodleform_mod.php` analysis):*
        - Module Name (usually `name`)
        - Introduction/Description (usually `intro` or `introeditor`)
        - Visibility (`visible`)
        - ID Number (`cmidnumber`)
        - Group Mode (`groupmode`)
        - Grouping ID (`groupingid`)
        - Outcomes (elements like `outcome_<id>`)
        - Ratings (elements added by `add_rating_settings()`)
        - Availability/Access Restrictions (`availabilityconditionsjson`)
        - Activity Completion (various elements like `completion`, `completionview`, `completionexpected`)
        - Tags (`tags`)
        - Force Language (`lang`)
        - Download Content (`downloadcontent`)
        - (Potentially others like `showdescription` from `init_features` if not covered by introeditor)
    - [*] *Extracted Data (Unique Settings - to be filled by inspecting individual module `mod_form.php` files):*
        - Example (Assignment): `assignsubmission_onlinetext_enabled`, `assignfeedback_comments_enabled`, `grade`, `duedate`, `cutoffdate`, etc.
        - Example (Quiz): `timeopen`, `timeclose`, `timelimit`, `shufflequestions`, `attempts`, etc.

- [x] **4. Identify Course Object Structure and Settings:**
    - [x] Find the main Moodle course class definition.
    - [x] List settable properties/fields of a course object.
    - [x] Identify how course settings are managed (e.g., course format options, completion settings).
    - [*] *Location in Moodle code (to be filled):* Course data is primarily stored in the `mdl_course` table. Settings are managed via `moodle-500/course/edit_form.php`. Core course functionalities are in `moodle-500/course/lib.php`.
    - [*] *Extracted Data (Course Settings - based on `course/edit_form.php` analysis - this is not exhaustive and some are conditional):*
        - **General:**
            - `fullname` (string): Course full name
            - `shortname` (string): Course short name
            - `category` (int): Course category ID
            - `visible` (int): Course visibility (0 = Hide, 1 = Show)
            - `startdate` (timestamp): Course start date
            - `enddate` (timestamp, optional): Course end date
            - `idnumber` (string, optional): Course ID number (for external systems)
            - `relativedatesmode` (int): Enable relative dates (0 = No, 1 = Yes)
        - **Description:**
            - `summary` (string, HTML): Course summary
            - `overviewfiles` (filearea): Course overview files (typically images)
        - **Course Format:**
            - `format` (string): Course format (e.g., `topics`, `weeks`, `social`)
            - `numsections` (int, format-dependent): Number of sections (for formats like Topics, Weeks)
            - Other format-specific settings (e.g., `hiddensections`, `coursedisplay` for some formats)
        - **Appearance:**
            - `theme` (string, optional): Force theme name
            - `lang` (string, optional): Force language
            - `showgrades` (int): Show gradebook to students
            - `showreports` (int): Show activity reports
        - **Files and Uploads:**
            - `maxbytes` (int): Maximum upload size for the course
        - **Completion Tracking:**
            - `enablecompletion` (int): Enable completion tracking (0 = Disabled, 1 = Enabled)
        - **Groups:**
            - `groupmode` (int): Group mode (0 = No groups, 1 = Separate groups, 2 = Visible groups)
            - `groupmodeforce` (int): Force group mode for all activities
            - `defaultgroupingid` (int): Default grouping ID
        - **Tags:**
            - `tags` (array of strings/ids): Course tags
        - **(Other potential settings not explicitly listed but present in the form)**

- [x] **5. Identify Sections Object Structure and Settings:**
    - [x] Find how Moodle defines course sections.
    - [x] List settable properties/fields of a section object.
    - [x] Identify common settings for sections (e.g., visibility, name, summary).
    - [*] *Location in Moodle code (to be filled):* Section data is primarily stored in the `mdl_course_sections` table. Settings are managed via `moodle-500/course/editsection_form.php`. Course format plugins can add their own section settings.
    - [*] *Extracted Data (Section Settings - based on `course/editsection_form.php` and general Moodle structure):*
        - `name` (string, optional): Custom section name. If not set, a default name is used (e.g., "Topic 1", "Week 1").
        - `summary` (string, HTML): Section summary or description.
        - `visible` (int): Section visibility (0 = Hidden, 1 = Shown). (This is a common field in `mdl_course_sections` but might be controlled differently by some formats).
        - `availabilityconditionsjson` (string, JSON): Access restrictions for the section.
        - `sequence` (string, comma-separated ints): Comma-separated list of course module IDs within the section. (Managed by course editing, not typically a direct form field for the user in this raw format).
        - *Format-specific settings:* Course formats (e.g., Tiles, Grid format) can add their own settings for sections (e.g., background image, icon, layout options). These are not universal. 

- [x] **6. Identify Common and Unique Settings for Question Types:**
    - [x] Find the base class/interface for Moodle question type editing forms.
    - [x] Identify how common question settings are defined (e.g., question name, question text, default mark).
    - [ ] For a selection of representative question types (e.g., multichoice, truefalse, essay, shortanswer):
        - [ ] Identify their unique settings.
    - [*] *Location in Moodle code for common settings (to be filled):* `moodle-500/question/type/edit_question_form.php` (class `question_edit_form`).
    - [*] *Location in Moodle code for unique settings (examples) (to be filled):* e.g., `moodle-500/question/type/multichoice/edit_multichoice_form.php` (in `definition_inner()`), etc.
    - [*] *Extracted Data (Common Settings - based on `edit_question_form.php`):*
        - `category` (int): Question category ID.
        - `name` (string): Question name.
        - `questiontext` (string, HTML): The main content of the question.
        - `status` (string): Question status (e.g., 'draft', 'ready').
        - `defaultmark` (float): Default points for the question.
        - `generalfeedback` (string, HTML, optional): General feedback shown to the student after attempting the question.
        - `tags` (array of strings/ids, optional): Question tags.
        - `idnumber` (string, optional): An optional ID number for the question.
        - (Potentially `penalty` for adaptive quizzes, `hidden` status, `version` - though these might be more specialized or part of the question object itself rather than explicit form fields in the base class).
    - [*] *Extracted Data (Unique Settings - to be filled by inspecting individual question type `edit_..._form.php` files):*
        - Example (Multiple Choice): `answer` (repeated for choices), `fraction` (grade for choice), `feedback` (for choice), `single` (single or multiple answers), `shuffleanswers`, `answernumbering`.
        - Example (True/False): `feedbacktrue`, `feedbackfalse`, `correctanswer`.
        - Example (Essay): `responseformat` (e.g., html, plain text), `responserequired` (e.g., text required or optional), `responsefieldlines` (input box size), `attachments` (allow attachments), `graderinfo`, `responsetemplate`.

- [ ] **7. Identify Container Modules and Their Sub-Elements:**
    - [ ] List modules that primarily function as containers for other configurable elements.
    - [ ] For each container module, identify the type(s) of sub-elements they hold.
    - [*] *Extracted Data (Container Modules and Sub-Elements):*
        - **Quiz (`quiz`):**
            - Contains: **Questions** (drawn from the question bank or created in-quiz).
        - **Lesson (`lesson`):**
            - Contains: **Pages** (content pages, question pages).
        - **Folder (`folder`):**
            - Contains: **Files**, **Sub-folders**.
        - **Book (`book`):**
            - Contains: **Chapters**, **Sub-chapters** (HTML content pages).
        - **Database (`data`):**
            - Contains: **Entries** (composed of defined fields).
        - **Glossary (`glossary`):**
            - Contains: **Entries** (terms and definitions).
        - **Workshop (`workshop`):**
            - Contains: **Submissions**, **Peer/Teacher Assessments**.
        - **SCORM Package (`scorm`):**
            - Contains: **SCOs (Shareable Content Objects)** and internal assets (defined by SCORM standard).
        - **H5P Activity (`h5pactivity`):**
            - Contains: **H5P Content Package** (with its own internal structure).
        - **IMSCP Content Package (`imscp`):**
            - Contains: Packaged resources and organization (defined by IMS Content Packaging standard).
        - *(Note: Modules like Forum, Wiki, Assignment, etc., while containing user-generated content or submissions, are generally not treated as containers of distinct, structurally configurable sub-elements in the same way as the above for rule-making purposes, though their own settings can be subject to rules.)*

- [ ] **8. Define Structure for the Rule Creation Form:**
    - [ ] Design the UI/UX flow for creating and managing rules.
    - [ ] Detail the components and fields for a single "Check" within a rule.
    - [ ] Detail how multiple "Checks" are combined (logical operators).
    - [ ] Detail the fields for the "Resolution" part of a rule.
    - [*] *Form Structure Details (to be refined during implementation):*

        **I. Rule Structure Overview:**
        A rule is composed of:
        1.  One or more "Check" blocks.
        2.  Logical operators (AND/OR) connecting multiple "Check" blocks.
        3.  One or more "Resolution" blocks that define actions/hints if all checks evaluate to true.
        A button "Add another Check" will allow users to extend the rule with more checks.
        A button "Add another Resuloution" will allow users to extend the rule with more resolutions.

        **II. "Check" Component (Base Fields for the First Check):**
        - `CHECK_SCOPE`: Select Dropdown.
            - *Purpose:* Defines the broad context or container type for this check.
            - *Options:* Container-type Modules (refer to Task 7, e.g., Quiz, Lesson, Folder).
        - `CHECK_NOT`: Checkbox.
            - *Purpose:* Inverts the logic of this individual check (e.g., "NOT (Source Has Setting X)").
        - `CHECK_SOURCE`: Select Dropdown.
            - *Purpose:* Specifies the Moodle element to be inspected.
            - *Options:* `course`, `section`, specific Moodle Modules/Activities (from Task 1, e.g., `assign`, `forum`, `quiz`), specific sub-elements (from Task 7, e.g., `quiz_question`, `folder_file`, `lesson_page`). The list should be filtered by `CHECK_SCOPE` if applicable.
        - `CHECK_CONDITION_TYPE`: Select Dropdown.
            - *Purpose:* Determines what aspect of the `CHECK_SOURCE` is being evaluated.
            - *Options:* "Has Setting", "Has Content".
        - `CHECK_TARGET`: Select Dropdown (Dynamically populated based on `CHECK_SOURCE` and `CHECK_CONDITION_TYPE`).
            - *Purpose:* Specifies the particular setting or type of content to look for.
            - *If `CHECK_CONDITION_TYPE` = "Has Setting":* Options list available settings for the selected `CHECK_SOURCE` (e.g., if `CHECK_SOURCE` is `quiz`, `CHECK_TARGET` could be `timeopen`, `grade`, `shuffleanswers`; if `CHECK_SOURCE` is `course`, `CHECK_TARGET` could be `fullname`, `visible`). (Refer to Tasks 3, 4, 5, 6 for settings lists).
            - *If `CHECK_CONDITION_TYPE` = "Has Content":* Options list elements that can be contained by the `CHECK_SOURCE` (e.g., if `CHECK_SOURCE` is `quiz`, `CHECK_TARGET` could be `question_type_multichoice`; if `CHECK_SOURCE` is `folder`, `CHECK_TARGET` could be `file_type_pdf`).
        - `CHECK_COMPARE_OPERATOR`: Select Dropdown.
            - *Purpose:* Defines how the `CHECK_TARGET` (or its value) is compared.
            - *Options:* "equals", "not equals", "contains", "does not contain", "regex matches", "is greater than", "is less than", "is empty", "is not empty", "is one of", "is not one of".
        - `CHECK_VALUE_TO_COMPARE`: Text Input Field (or other appropriate input based on `CHECK_TARGET` and `CHECK_COMPARE_OPERATOR`, e.g., a multi-select for "is one of").
            - *Purpose:* The value to use for the comparison.
        - `CHECK_VALUE_TYPE_INFO`: Disabled Text Field (Display Only).
            - *Purpose:* Informs the user about the expected data type of the selected `CHECK_TARGET` setting (e.g., "Integer", "String", "Boolean", "Timestamp", "Comma-separated list").

        **III. Subsequent "Checks" (For the second check onwards):**
        - `CHECK_LOGICAL_OPERATOR_WITH_PREVIOUS`: Select Dropdown (appears *before* each subsequent check).
            - *Purpose:* Connects this check to the previous one.
            - *Options:* "AND", "OR".
        - `CHECK_SCOPE`, `CHECK_SOURCE`, `CHECK_TARGET` fields will have modified options:
            - *Options will include references to elements from the **first check** (e.g., `same_SCOPE_as_Check1`, `same_SOURCE_as_Check1`, `same_TARGET_as_Check1`) alongside options to select a new/different element (`other_SCOPE`, `other_SOURCE`, `other_TARGET`).*
            - *Detailed logic for how "same_" and "other_" propagate or refer needs to be defined during UI design. The primary goal is to allow referencing elements from the initial check or introducing new ones.*
        - Other fields (`CHECK_NOT`, `CHECK_CONDITION_TYPE`, `CHECK_COMPARE_OPERATOR`, `CHECK_VALUE_TO_COMPARE`, `CHECK_VALUE_TYPE_INFO`) are the same as in the first check.

        **IV. "Resolution" Component (Defines action/hint if all combined checks pass):**
        - `RESOLUTION_CONTEXT`: Select Dropdown.
            - *Purpose:* Defines the Moodle element that the resolution will apply to.
            - *Options:* May include `same_SCOPE_as_Check1`, `same_SOURCE_as_Check1`, `same_TARGET_as_Check1`, or allow selection of a new specific context (e.g., `course`, a specific Module instance identified by the checks).
        - `RESOLUTION_TYPE`: Select Dropdown.
            - *Purpose:* Specifies the nature of the resolution.
            - *Options:* "HINT" (provide a message/suggestion to the user), "ACTION" (propose an automated modification).
        - **If `RESOLUTION_TYPE` = "HINT":**
            - `RESOLUTION_HINT_MESSAGE`: Text Area.
                - *Purpose:* The guidance or warning message to be displayed.
        - **If `RESOLUTION_TYPE` = "ACTION":**
            - `RESOLUTION_ACTION_TYPE`: Select Dropdown.
                - *Purpose:* Specifies the type of automated action.
                - *Options:* "Change Setting", "Add Content".
            - **If `RESOLUTION_ACTION_TYPE` = "Change Setting":**
                - `RESOLUTION_SETTING_TO_CHANGE`: Select Dropdown.
                    - *Purpose:* The specific setting to modify on the `RESOLUTION_CONTEXT`.
                    - *Options:* Dynamically populated list of available settings for the selected `RESOLUTION_CONTEXT`.
                - `RESOLUTION_NEW_SETTING_VALUE`: Text Input Field.
                    - *Purpose:* The new value for the selected setting.
            - **If `RESOLUTION_ACTION_TYPE` = "Add Content":**
                - `RESOLUTION_CONTENT_TO_ADD`: Select Dropdown.
                    - *Purpose:* The type of Moodle module/activity to add.
                    - *Options:* List of all available Moodle Modules/Activities (from Task 1).
                - `RESOLUTION_CONTENT_LOCATION` (Optional, context-dependent): Select Dropdown or other appropriate input.
                    - *Purpose:* Specifies where the new content should be placed (e.g., a specific course section if `RESOLUTION_CONTEXT` is a course or within a container module).
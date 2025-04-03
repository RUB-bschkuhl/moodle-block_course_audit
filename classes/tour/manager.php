<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Tour manager class.
 *
 * @package    block_course_audit
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_audit\tour;

defined('MOODLE_INTERNAL') || die();

use tool_usertours\tour;
use tool_usertours\step;

/**
 * Class manager for handling tour creation and management.
 *
 * This class provides functionality to create, save, and manage user tours
 * and their steps for the course_audit block.
 *
 * @package    block_course_audit
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager
{

    /** @var tour The tour object */
    protected $tour = null;

    /** @var array The steps for this tour */
    protected $steps = [];

    /**
     * Create a new tour with the specified settings.
     *
     * @param string $name The name of the tour
     * @param string $description The description of the tour
     * @param string $pathmatch The pattern to match for displaying the tour
     * @param array $config Optional additional configuration for the tour
     * @return tour The newly created tour object
     */
    public function create_tour($name, $description, $pathmatch, $config = [])
    {
        global $DB;

        // Create a new tour instance
        $this->tour = new tour();
        $this->tour->set_name($name);
        $this->tour->set_description($description);
        $this->tour->set_pathmatch($pathmatch);
        $this->tour->set_enabled(tour::ENABLED);
        $this->tour->set_filter_values('cssselector', ['#block-course-audit']);
        $this->tour->set_sortorder(0);

        // Set configuration options
        foreach ($config as $key => $value) {
            $this->tour->set_config($key, $value);
        }

        /*
        Display Options
        displaystepnumbers (boolean)
        Determines whether to display step numbers in the tour
        Example: 'displaystepnumbers' => true
        showtourwhen (integer)
        Controls when to show the tour:
        tour::SHOW_TOUR_UNTIL_COMPLETE (value: 1) - Show the tour until the user completes it
        tour::SHOW_TOUR_ON_EACH_PAGE_VISIT (value: 2) - Show the tour every time the page is visited
        Example: 'showtourwhen' => tour::SHOW_TOUR_UNTIL_COMPLETE
        filtervalues.cssselector (string)
        Only show the tour when the specified CSS selector is found on the page.
        Example: '#block_course_audit'
        endtourlabel (string)
        Custom label for the button that ends the tour
        Example: 'endtourlabel' => 'Finish tour'
        Visual and Behavior Options
        4. backdrop (boolean)
        Whether to show a backdrop behind the tour step (highlighting the target element)
        Example: 'backdrop' => true
        orphan (boolean)
        Whether the step should be displayed even if the target element is not found
        Example: 'orphan' => true
        reflex (boolean)
        Whether clicking on the element will automatically advance to the next step
        Example: 'reflex' => true
        placement (string)
        Default placement for all steps (can be overridden in individual steps)
        Options: 'top', 'bottom', 'left', 'right'
        Example: 'placement' => 'top'
        */

        // Persist the tour to the database
        $this->tour->persist();

        // Log the creation of the tour
        debugging('Created new tour with ID: ' . $this->tour->get_id(), DEBUG_DEVELOPER);

        return $this->tour;
    }

    /**
     * Add a step to the current tour.
     *
     * @param string $title The step title
     * @param string $content The step content
     * @param string $targettype The type of target
     * @param string $targetvalue The target selector value
     * @param array $config Optional additional configuration for the step
     * @return step The newly created step object
     */
    public function add_step($title, $content, $targettype, $targetvalue, $config = [])
    {
        if ($this->tour === null) {
            throw new \coding_exception('No tour has been created yet. Call create_tour() first.');
        }

        // Create a new step instance
        $step = new step();
        $step->set_tourid($this->tour->get_id());
        $step->set_title($title);
        $step->set_content($content, FORMAT_HTML);
        $step->set_targettype($targettype);
        $step->set_targetvalue($targetvalue);

        // Set any additional configuration options
        foreach ($config as $key => $value) {
            $step->set_config($key, $value);
        }

        /*
        Placement Options
        placement (string)
        Position of the step popup relative to the target element
        Options: 'top', 'bottom', 'left', 'right'
        Example: 'placement' => 'top'
        Behavior Options
        orphan (boolean)
        Whether the step should be displayed even if the target element is not found
        Example: 'orphan' => true
        backdrop (boolean)
        Whether to show a backdrop behind the tour step (highlighting the target element)
        Example: 'backdrop' => true
        reflex (boolean)
        Whether clicking on the element will automatically advance to the next step
        Example: 'reflex' => true
        */

        // Persist the step to the database
        $step->persist();

        // Store the step for later reference
        $this->steps[] = $step;

        // Log the creation of the step
        debugging('Added step with ID: ' . $step->get_id() . ' to tour: ' . $this->tour->get_id(), DEBUG_DEVELOPER);

        return $step;
    }

    /**
     * Create a complete tour with multiple steps.
     *
     * @param string $name The name of the tour
     * @param string $description The description of the tour
     * @param string $pathmatch The pattern to match for displaying the tour
     * @param array $steps Array of step configurations to add
     * @param array $config Optional additional configuration for the tour
     * @return tour The newly created tour object
     */
    public function create_tour_with_steps($name, $description, $pathmatch, $steps, $config = [])
    {
        // Create the tour first
        $this->create_tour($name, $description, $pathmatch, $config);

        // Then add all steps
        foreach ($steps as $stepconfig) {
            $this->add_step(
                $stepconfig['title'],
                $stepconfig['content'],
                $stepconfig['targettype'],
                $stepconfig['targetvalue'],
                $stepconfig['config'] ?? []
            );
        }

        return $this->tour;
    }

    /**
     * Get the current tour object.
     *
     * @return tour|null The current tour object or null if none exists
     */
    public function get_tour()
    {
        return $this->tour;
    }

    /**
     * Get all steps for the current tour.
     *
     * @return array The array of step objects
     */
    public function get_steps()
    {
        return $this->steps;
    }

    /**
     * Get a tour by ID.
     *
     * @param int $id The ID of the tour to fetch
     * @return tour The tour object
     */
    public function get_tour_by_id($id)
    {
        $this->tour = tour::instance($id);
        return $this->tour;
    }

    /**
     * Reset a tour for all users.
     *
     * @param int $tourid The ID of the tour to reset
     * @return bool Whether the reset was successful
     */
    public function reset_tour_for_all_users($tourid)
    {
        global $DB;

        // Get the tour instance
        $tour = tour::instance($tourid);

        // Reset the tour by setting a new major update time
        $tour->mark_major_change();

        return true;
    }

    /**
     * Delete a tour and all its steps.
     *
     * @param int $tourid The ID of the tour to delete
     * @return bool Whether the deletion was successful
     */
    public function delete_tour($tourid)
    {
        $tour = tour::instance($tourid);
        $tour->remove();

        return true;
    }

    /**
     * Example utility function to create a course introduction tour.
     *
     * @param string $courseid The course ID to create the tour for (used in the path match)
     * @return tour The created tour object
     */
    public static function create_course_introduction_tour($courseid)
    {
        $manager = new self();

        // Create a tour for this specific course
        $pathmatch = "/course/view.php\\?id=$courseid";
        $tourconfig = [
            'displaystepnumbers' => true,
            'showtourwhen' => tour::SHOW_TOUR_UNTIL_COMPLETE
        ];

        $tour = $manager->create_tour(
            'Course Navigation Tour',
            'This tour will help you navigate the course interface.',
            $pathmatch,
            $tourconfig
        );

        // Add steps to the tour
        $manager->add_step(
            'Welcome to the Course',
            'This tour will guide you through the main features of this course.',
            'body',
            '.course-content',
            ['placement' => 'top']
        );

        $manager->add_step(
            'Course Navigation',
            'Use this navigation menu to access different course sections.',
            'selector',
            '.navbar',
            ['placement' => 'bottom']
        );

        $manager->add_step(
            'Course Activities',
            'Here you can see all the activities available in this course.',
            'selector',
            '.activity',
            ['placement' => 'right']
        );

        $manager->add_step(
            'Need Help?',
            'If you need help, you can contact your instructor or use the help resources.',
            'selector',
            '.helplink',
            ['placement' => 'left']
        );

        return $tour;
    }
}

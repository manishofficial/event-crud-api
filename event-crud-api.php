<?php
/**
 * Event CRUD API
 *
 * @package           PluginPackage
 * @author            Your Name
 * @copyright         2019 Your Name or Company Name
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Event CRUD API
 * Plugin URI:        https://github.com/manishofficial/event-crud-api
 * Description:       Simple CRUD API for managing special events.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Manish Kumar
 * Author URI:        https://github.com/manishofficial
 * Text Domain:       event-crud-api
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://github.com/manishofficial/event-crud-api/
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Register the REST API endpoints
add_action('rest_api_init', function () {
    // Register the endpoints
    register_rest_route('event-crud/v1', '/events', array(
        'methods' => 'GET',
        'callback' => 'get_events',
        'permission_callback' => 'admin',
    ));
    register_rest_route('event-crud/v1', '/events', array(
        'methods' => 'POST',
        'callback' => 'create_event',
        'permission_callback' => 'admin',
    ));
    register_rest_route('event-crud/v1', '/events/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'update_event',
        'permission_callback' => 'admin',
    ));
    register_rest_route('event-crud/v1', '/events/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_event',
        'permission_callback' => 'admin',
    ));
});

// Permission callback to check if user is admin
function admin() {
    return current_user_can('administrator');
}

// Callback function for getting events
function get_events() {
    $events = get_posts(array(
        'post_type' => 'event',
        'posts_per_page' => -1,
    ));

    return rest_ensure_response($events);
}

// Callback function for creating an event
function create_event($request) {
    $params = $request->get_params();

    $event_id = wp_insert_post(array(
        'post_title' => $params['title'],
        'post_content' => $params['description'],
        'post_status' => 'publish',
        'post_type' => 'event',
        'meta_input' => array(
            '_event_start_date' => $params['start_date'],
            '_event_end_date' => $params['end_date'],
            '_event_category' => $params['category'],
        ),
    ));

    if (is_wp_error($event_id)) {
        return rest_ensure_response(array('error' => $event_id->get_error_message()), 500);
    }

    return rest_ensure_response(array('message' => 'Event created successfully', 'event_id' => $event_id));
}

// Callback function for updating an event
function update_event($request) {
    $params = $request->get_params();
    $event_id = $params['id'];

    $updated = wp_update_post(array(
        'ID' => $event_id,
        'post_title' => $params['title'],
        'post_content' => $params['description'],
        'meta_input' => array(
            '_event_start_date' => $params['start_date'],
            '_event_end_date' => $params['end_date'],
            '_event_category' => $params['category'],
        ),
    ));

    if (is_wp_error($updated)) {
        return rest_ensure_response(array('error' => $updated->get_error_message()), 500);
    }

    return rest_ensure_response(array('message' => 'Event updated successfully'));
}

// Callback function for deleting an event
function delete_event($request) {
    $params = $request->get_params();
    $event_id = $params['id'];

    $deleted = wp_delete_post($event_id, true);

    if (!$deleted) {
        return rest_ensure_response(array('error' => 'Failed to delete event'), 500);
    }

    return rest_ensure_response(array('message' => 'Event deleted successfully'));
}

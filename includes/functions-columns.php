<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Outputs data for the leave metabox within an employee.
 *
 */
function business_manager_upcoming_leave_html_column() {
    $employee = new Business_Manager_Employee();
    return $employee->upcoming_leave_html_column();
}

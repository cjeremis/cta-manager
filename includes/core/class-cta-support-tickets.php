<?php
/**
 * Support Tickets Handler
 *
 * Handles support ticket data storage and ticket retrieval operations.
 *
 * @package CTAManager
 * @since 1.0.0
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CTA_Support_Tickets {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor
     */
    private function __construct() {
        // Constructor
    }

    /**
     * Generate unique ticket number
     * Format: CTASUP-YYYYMMDD-####
     *
     * @return string Ticket number
     */
    public function generate_ticket_number() {
        $date_prefix = 'CTASUP-' . gmdate('Ymd') . '-';
        $table_name = CTA_Database::table( CTA_Database::TABLE_SUPPORT_TICKETS );

        // Find highest ticket number for today
        $pattern = $date_prefix . '%';
        $sql = "SELECT ticket_number FROM {$table_name} WHERE ticket_number LIKE %s ORDER BY ticket_number DESC LIMIT 1";
        $results = CTA_Database::query($sql, [$pattern]);

        $latest = !empty($results) ? $results[0]['ticket_number'] : null;

        if ($latest) {
            $parts = explode('-', $latest);
            $sequence = isset($parts[2]) ? intval($parts[2]) + 1 : 1;
        } else {
            $sequence = 1;
        }

        return $date_prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new support ticket
     *
     * @param array $ticket_data Ticket data
     * @return int|false Ticket ID on success, false on failure
     */
    public function create_ticket($ticket_data) {
        $table_name = CTA_Database::table( CTA_Database::TABLE_SUPPORT_TICKETS );

        // Generate ticket number
        $ticket_number = $this->generate_ticket_number();

        // Prepare data
        $data = array(
            'ticket_number' => $ticket_number,
            'customer_email' => sanitize_email($ticket_data['customer_email']),
            'customer_site_url' => esc_url_raw($ticket_data['customer_site_url']),
            'subject' => sanitize_text_field($ticket_data['subject']),
            'description' => wp_kses_post($ticket_data['description']),
            'priority' => in_array($ticket_data['priority'] ?? 'normal', ['low', 'normal', 'high', 'critical'])
                ? $ticket_data['priority']
                : 'normal',
            'status' => 'new',
            'customer_ip' => $ticket_data['customer_ip'] ?? null,
            'customer_user_agent' => $ticket_data['customer_user_agent'] ?? null,
        );

        // Add system metadata if provided
        $metadata_fields = [
            'wp_version',
            'php_version',
            'theme_name',
            'theme_version',
            'cta_manager_version',
            'cta_manager_pro_version',
        ];

        foreach ($metadata_fields as $field) {
            if (isset($ticket_data[$field])) {
                $data[$field] = sanitize_text_field($ticket_data[$field]);
            }
        }

        // Handle active_plugins JSON
        if (isset($ticket_data['active_plugins']) && is_array($ticket_data['active_plugins'])) {
            $data['active_plugins'] = wp_json_encode($ticket_data['active_plugins']);
        }

        $result = CTA_Database::insert($table_name, $data, array_fill(0, count($data), '%s'));

        return $result;
    }

    /**
     * Get ticket by ID
     *
     * @param int $ticket_id Ticket ID
     * @return object|null Ticket object or null
     */
    public function get_ticket($ticket_id) {
        $table_name = CTA_Database::table( CTA_Database::TABLE_SUPPORT_TICKETS );

        $ticket = CTA_Database::get_row($table_name, ['id' => $ticket_id]);

        if (!$ticket) {
            return null;
        }

        // Convert array to object for backward compatibility
        $ticket = (object) $ticket;

        if ($ticket->active_plugins) {
            $ticket->active_plugins = json_decode($ticket->active_plugins, true);
        }

        return $ticket;
    }

    /**
     * Get ticket by ticket number
     *
     * @param string $ticket_number Ticket number
     * @return object|null Ticket object or null
     */
    public function get_ticket_by_number($ticket_number) {
        $table_name = CTA_Database::table( CTA_Database::TABLE_SUPPORT_TICKETS );

        $ticket = CTA_Database::get_row($table_name, ['ticket_number' => $ticket_number]);

        if (!$ticket) {
            return null;
        }

        // Convert array to object for backward compatibility
        $ticket = (object) $ticket;

        if ($ticket->active_plugins) {
            $ticket->active_plugins = json_decode($ticket->active_plugins, true);
        }

        return $ticket;
    }

    /**
     * Get tickets by customer email
     *
     * @param string $email Customer email
     * @param int $limit Number of tickets to retrieve
     * @param int $offset Offset for pagination
     * @return array Array of ticket objects
     */
    public function get_customer_tickets($email, $limit = 20, $offset = 0) {
        $table_name = CTA_Database::table( CTA_Database::TABLE_SUPPORT_TICKETS );

        $tickets = CTA_Database::get_results(
            $table_name,
            ['customer_email' => $email],
            'created_at DESC',
            $limit,
            $offset
        );

        // Convert arrays to objects for backward compatibility
        $tickets_objects = [];
        foreach ($tickets as $ticket) {
            $ticket_obj = (object) $ticket;
            if ($ticket_obj->active_plugins) {
                $ticket_obj->active_plugins = json_decode($ticket_obj->active_plugins, true);
            }
            $tickets_objects[] = $ticket_obj;
        }

        return $tickets_objects;
    }

    /**
     * Add a reply to a ticket
     *
     * @param int $ticket_id Ticket ID
     * @param array $reply_data Reply data
     * @return int|false Reply ID on success, false on failure
     */
    public function add_reply($ticket_id, $reply_data) {
        $table_name = CTA_Database::table( CTA_Database::TABLE_SUPPORT_TICKET_REPLIES );

        // Prepare data
        $data = array(
            'ticket_id' => $ticket_id,
            'author_type' => in_array($reply_data['author_type'], ['customer', 'admin', 'system'])
                ? $reply_data['author_type']
                : 'customer',
            'author_name' => sanitize_text_field($reply_data['author_name']),
            'author_email' => isset($reply_data['author_email']) ? sanitize_email($reply_data['author_email']) : null,
            'author_user_id' => isset($reply_data['author_user_id']) ? absint($reply_data['author_user_id']) : null,
            'message' => wp_kses_post($reply_data['message']),
            'ip_address' => $reply_data['ip_address'] ?? null,
            'user_agent' => $reply_data['user_agent'] ?? null,
        );

        $result = CTA_Database::insert($table_name, $data, array(
            '%d', // ticket_id
            '%s', // author_type
            '%s', // author_name
            '%s', // author_email
            '%d', // author_user_id
            '%s', // message
            '%s', // ip_address
            '%s', // user_agent
        ));

        if ($result === false) {
            return false;
        }

        // Update ticket's last_reply_at and last_reply_by
        $tickets_table = CTA_Database::table( CTA_Database::TABLE_SUPPORT_TICKETS );
        CTA_Database::update(
            $tickets_table,
            array(
                'last_reply_at' => current_time('mysql'),
                'last_reply_by' => $data['author_type'],
            ),
            array('id' => $ticket_id),
            array('%s', '%s'),
            array('%d')
        );

        return $result;
    }

    /**
     * Get all replies for a ticket
     *
     * @param int $ticket_id Ticket ID
     * @return array Array of reply objects
     */
    public function get_ticket_replies($ticket_id) {
        $table_name = CTA_Database::table( CTA_Database::TABLE_SUPPORT_TICKET_REPLIES );

        $replies = CTA_Database::get_results(
            $table_name,
            ['ticket_id' => $ticket_id],
            'created_at ASC'
        );

        // Convert arrays to objects for backward compatibility
        $replies_objects = [];
        foreach ($replies as $reply) {
            $replies_objects[] = (object) $reply;
        }

        return $replies_objects;
    }

    /**
     * Update ticket status
     *
     * @param int $ticket_id Ticket ID
     * @param string $status New status
     * @return bool Success
     */
    public function update_status($ticket_id, $status) {
        $allowed_statuses = ['new', 'in_progress', 'on_hold', 'waiting_for_reply', 'closed', 'archived'];

        if (!in_array($status, $allowed_statuses)) {
            return false;
        }

        $table_name = CTA_Database::table( CTA_Database::TABLE_SUPPORT_TICKETS );

        $data = array('status' => $status);

        // Set closed_at if status is closed
        if ($status === 'closed') {
            $data['closed_at'] = current_time('mysql');
        }

        $result = CTA_Database::update(
            $table_name,
            $data,
            array('id' => $ticket_id),
            array('%s'),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Update ticket priority
     *
     * @param int $ticket_id Ticket ID
     * @param string $priority New priority
     * @return bool Success
     */
    public function update_priority($ticket_id, $priority) {
        $allowed_priorities = ['low', 'normal', 'high', 'critical'];

        if (!in_array($priority, $allowed_priorities)) {
            return false;
        }

        $table_name = CTA_Database::table( CTA_Database::TABLE_SUPPORT_TICKETS );

        $result = CTA_Database::update(
            $table_name,
            array('priority' => $priority),
            array('id' => $ticket_id),
            array('%s'),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Mark ticket as read
     *
     * @param int $ticket_id Ticket ID
     * @return bool Success
     */
    public function mark_as_read($ticket_id) {
        $table_name = CTA_Database::table( CTA_Database::TABLE_SUPPORT_TICKETS );

        $result = CTA_Database::update(
            $table_name,
            array('is_read' => 1),
            array('id' => $ticket_id),
            array('%d'),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Get unread ticket count
     *
     * @return int Count of unread tickets
     */
    public function get_unread_count() {
        $table_name = CTA_Database::table( CTA_Database::TABLE_SUPPORT_TICKETS );

        return CTA_Database::count($table_name, ['is_read' => 0]);
    }

    /**
     * Search tickets with filters
     *
     * @param array $filters Search filters
     * @param int $limit Number of tickets to retrieve
     * @param int $offset Offset for pagination
     * @return array Array of ticket objects
     */
    public function search_tickets($filters = array(), $limit = 20, $offset = 0) {
        $table_name = CTA_Database::table( CTA_Database::TABLE_SUPPORT_TICKETS );

        $where_clauses = array();
        $where_values = array();

        // Filter by status
        if (isset($filters['status']) && !empty($filters['status'])) {
            $where_clauses[] = 'status = %s';
            $where_values[] = $filters['status'];
        }

        // Filter by priority
        if (isset($filters['priority']) && !empty($filters['priority'])) {
            $where_clauses[] = 'priority = %s';
            $where_values[] = $filters['priority'];
        }

        // Filter by is_read
        if (isset($filters['is_read'])) {
            $where_clauses[] = 'is_read = %d';
            $where_values[] = $filters['is_read'] ? 1 : 0;
        }

        // Search in subject and description
        if (isset($filters['search']) && !empty($filters['search'])) {
            $where_clauses[] = '(subject LIKE %s OR description LIKE %s OR customer_email LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }

        // Build WHERE clause
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }

        // Order by
        $order_by = isset($filters['order_by']) ? $filters['order_by'] : 'created_at';
        $order = isset($filters['order']) && strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';

        // Build query
        $sql = "SELECT * FROM {$table_name} {$where_sql} ORDER BY {$order_by} {$order} LIMIT %d OFFSET %d";
        $where_values[] = $limit;
        $where_values[] = $offset;

        $tickets = CTA_Database::query($sql, $where_values);

        // Convert arrays to objects for backward compatibility
        $tickets_objects = [];
        foreach ($tickets as $ticket) {
            $ticket_obj = (object) $ticket;
            if ($ticket_obj->active_plugins) {
                $ticket_obj->active_plugins = json_decode($ticket_obj->active_plugins, true);
            }
            $tickets_objects[] = $ticket_obj;
        }

        return $tickets_objects;
    }

    /**
     * Get total ticket count with filters
     *
     * @param array $filters Search filters
     * @return int Total count
     */
    public function get_total_count($filters = array()) {
        $table_name = CTA_Database::table( CTA_Database::TABLE_SUPPORT_TICKETS );

        $where_clauses = array();
        $where_values = array();

        // Same filter logic as search_tickets
        if (isset($filters['status']) && !empty($filters['status'])) {
            $where_clauses[] = 'status = %s';
            $where_values[] = $filters['status'];
        }

        if (isset($filters['priority']) && !empty($filters['priority'])) {
            $where_clauses[] = 'priority = %s';
            $where_values[] = $filters['priority'];
        }

        if (isset($filters['is_read'])) {
            $where_clauses[] = 'is_read = %d';
            $where_values[] = $filters['is_read'] ? 1 : 0;
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $where_clauses[] = '(subject LIKE %s OR description LIKE %s OR customer_email LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }

        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }

        $sql = "SELECT COUNT(*) as total_count FROM {$table_name} {$where_sql}";

        $result = CTA_Database::query($sql, $where_values);

        return (int) ($result[0]['total_count'] ?? 0);
    }
}

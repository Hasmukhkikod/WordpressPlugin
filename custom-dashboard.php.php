<?php
/*
Plugin Name: Custom User and Admin Dashboard
Description: A custom dashboard plugin to manage posts for users and admins.
Version: 1.0
Author: Your Name
*/


add_action('admin_menu', 'custom_dashboard_menu');

function custom_dashboard_menu() {
    add_menu_page(
        'Custom Dashboard',        
        'Custom Dashboard',       
        'edit_posts',              
        'custom-dashboard',        
        'custom_dashboard_page',   
        'dashicons-admin-post',   
        6                          
    );
}

// Display the custom dashboard page
function custom_dashboard_page() {
    $current_user = wp_get_current_user();
    $user_can_edit_all = current_user_can('administrator'); 
    ?>
    <div class="wrap">
        <h1><?php echo $user_can_edit_all ? 'All Posts' : 'My Posts'; ?></h1>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <th scope="col">Post Title</th>
                    <th scope="col">Author</th>
                    <th scope="col">Role</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
           
                $args = array(
                    'post_type'   => 'post',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                );

            
                if (!$user_can_edit_all) {
                    $args['author'] = $current_user->ID;
                }

                // Fetch posts
                $posts = get_posts($args);

                if ($posts) {
                    foreach ($posts as $post) {
                        $author_id = $post->post_author;
                        $author = get_userdata($author_id); 
                        $roles = $author ? implode(', ', $author->roles) : 'N/A'; 

                        echo '<tr>';
                        echo '<td>' . esc_html($post->post_title) . '</td>';
                        echo '<td>' . esc_html($author->display_name) . '</td>';
                        echo '<td>' . esc_html($roles) . '</td>';
                        echo '<td>';
                        // Edit link
                        echo '<a href="' . get_edit_post_link($post->ID) . '" class="edit-post">Edit</a> | ';
                        // Delete link
                        echo '<a href="' . wp_nonce_url(admin_url('admin-post.php?action=delete_post&id=' . $post->ID), 'delete_post_' . $post->ID) . '" class="delete-post" onclick="return confirm(\'Are you sure you want to delete this post?\')">Delete</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="4">No posts found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Handle post deletion
add_action('admin_post_delete_post', 'handle_delete_post');
function handle_delete_post() {
    if (isset($_GET['id']) && is_user_logged_in()) {
        $post_id = absint($_GET['id']);
        $post = get_post($post_id);

        // Check permissions
        if ($post && (current_user_can('administrator') || $post->post_author == get_current_user_id())) {
            wp_delete_post($post_id, true); 
            wp_redirect(admin_url('admin.php?page=custom-dashboard'));
            exit;
        } else {
            wp_die('You do not have permission to delete this post.');
        }
    }
}





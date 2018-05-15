<?php
require_once( './wp-config.php' );

function delete_revision_autosave_post()
{
    global $wpdb;
    $sql = "delete a,b,c from " . $wpdb->prefix ."posts a "
           . "left join " . $wpdb->prefix ."term_relationships b on (a.id = b.object_id) "
           . "left join " . $wpdb->prefix ."postmeta c on (a.id = c.post_id) "
           . "where a.post_type = 'revision' or post_status='auto-draft'";
    $wpdb->query($sql);
}

function update_post_id()
{
    global $wpdb;

    $buffer_count = 10000;
    $add_post_id_query = 'update ' . $wpdb->prefix .'posts set id = id + ' . $buffer_count;
    $wpdb->query($add_post_id_query);
    $add_post_id_query = 'update ' . $wpdb->prefix .'term_relationships set object_id = object_id + ' . $buffer_count;
    $wpdb->query($add_post_id_query);
    $add_post_id_query = 'update ' . $wpdb->prefix .'postmeta set post_id = post_id + ' . $buffer_count;
    $wpdb->query($add_post_id_query);
    $add_post_id_query = 'update ' . $wpdb->prefix .'comments set comment_post_id = comment_post_id + ' . $buffer_count;
    $wpdb->query($add_post_id_query);

    $sql_query = 'select id from ' . $wpdb->prefix .'posts order by post_type, id';
    $all_post_ids = $wpdb->get_results( $sql_query );

    $new_post_id = 1;
    if (is_array($all_post_ids)) {
        foreach($all_post_ids as $post_id_obj) {
            $post_id = $post_id_obj->id;
            $wpdb->query( 'update ' . $wpdb->prefix .'posts set id = ' . $new_post_id . ' where id = ' . $post_id );
            $wpdb->query( 'update ' . $wpdb->prefix .'term_relationships set object_id = ' . $new_post_id . ' where object_id = ' . $post_id );
            $wpdb->query( 'update ' . $wpdb->prefix .'postmeta set post_id = ' . $new_post_id . ' where post_id = ' . $post_id );
            $wpdb->query( 'update ' . $wpdb->prefix .'comments set comment_post_id = ' . $new_post_id . ' where comment_post_id = ' . $post_id );
            ++$new_post_id;
        }
        $wpdb->query('alter table ' . $wpdb->prefix .'posts AUTO_INCREMENT = ' . $new_post_id);
    }
}

function update_comment_id()
{
    global $wpdb;
    $sql_query = 'select comment_id from ' . $wpdb->prefix .'comments order by comment_id';
    $all_comment_ids = $wpdb->get_results( $sql_query );

    $new_comment_id = 1;
    if (is_array($all_comment_ids)) {
        foreach($all_comment_ids as $comment_id_obj) {
            $comment_id = $comment_id_obj->comment_id;
            $wpdb->query( 'update ' . $wpdb->prefix .'comments set comment_id = ' . $new_comment_id . ' where comment_id = ' . $comment_id );
            $wpdb->query( 'update ' . $wpdb->prefix .'comments set comment_parent = ' . $new_comment_id . ' where comment_parent = ' . $comment_id );
            $wpdb->query( 'update ' . $wpdb->prefix .'commentmeta set comment_id = ' . $new_comment_id . ' where comment_id = ' . $comment_id);
            ++$new_comment_id;
        }
        $wpdb->query('alter table ' . $wpdb->prefix .'comments AUTO_INCREMENT = ' . $new_comment_id);
    }
}


function update_option_id()
{
    global $wpdb;
    $sql_query = 'select option_id from ' . $wpdb->prefix .'options order by option_id';
    $all_option_ids = $wpdb->get_results( $sql_query );

    $new_option_id = 1;
    if (is_array($all_option_ids)) {
        foreach($all_option_ids as $option_id_obj) {
            $option_id = $option_id_obj->option_id;
            $wpdb->query( 'update ' . $wpdb->prefix .'options set option_id = ' . $new_option_id . ' where option_id = ' . $option_id );
            ++$new_option_id;
        }
        $wpdb->query('alter table ' . $wpdb->prefix .'options AUTO_INCREMENT = ' . $new_option_id);
    }
}

function update_term_id()
{
    global $wpdb;

    $buffer_count = 10000;

    $add_term_id_query = 'update ' . $wpdb->prefix .'terms set term_id = term_id + ' . $buffer_count;
    $wpdb->query($add_term_id_query);
    $add_term_id_query = 'update ' . $wpdb->prefix .'termmeta set term_id = term_id + ' . $buffer_count;
    $wpdb->query($add_term_id_query);
    $add_term_id_query = 'update ' . $wpdb->prefix .'term_taxonomy set term_id = term_id + ' . $buffer_count;
    $wpdb->query($add_term_id_query);
    $add_term_id_query = 'update ' . $wpdb->prefix .'term_taxonomy set parent = parent + ' . $buffer_count . 'where parent > 0';
    $wpdb->query($add_term_id_query);

    $sql_query = 'select t.term_id from ' . $wpdb->prefix .'terms t inner join ' . $wpdb->prefix .'term_taxonomy m '
                 . 'on t.term_id = m.term_id order by m.taxonomy, m.parent, t.name, m.term_id';
    $all_term_ids = $wpdb->get_results( $sql_query );

    $new_term_id = 1;
    if (is_array($all_term_ids)) {
        foreach($all_term_ids as $term_id_obj) {
            $term_id = $term_id_obj->term_id;
            $wpdb->query( 'update ' . $wpdb->prefix .'terms set term_id = ' . $new_term_id . ' where term_id = ' . $term_id );
            $wpdb->query( 'update ' . $wpdb->prefix .'termmeta set term_id = ' . $new_term_id . ' where term_id = ' . $term_id );
            $wpdb->query( 'update ' . $wpdb->prefix .'term_taxonomy set term_id = ' . $new_term_id . ' where term_id = ' . $term_id );
            $wpdb->query( 'update ' . $wpdb->prefix .'term_taxonomy set parent = ' . $new_term_id . ' where parent = ' . $term_id );
            ++$new_term_id;
        }
        $wpdb->query('alter table ' . $wpdb->prefix .'terms AUTO_INCREMENT = ' . $new_term_id);
    }
}


delete_revision_autosave_post();
update_post_id();
update_comment_id();
update_option_id();
update_term_id();
?>
<?php

PL_Posts::init();

class PL_Posts {
  
  public static function init () {
    self::register_dummy_data_post_status();
  }

  public static function create ( $manifest_posts, $post_type, $settings ) {
    // global $_wp_additional_image_sizes;

    // get existing posts... get_existing_posts() function
    $posts = get_posts( array(
        'showposts' => 50,
        'post_type' => $post_type,
        )
    );
    
    $existing_post_count = count($posts);

    $use_manifest = '';

    // if # of existing posts > min-posts use dummy data from manifest
    if ($existing_post_count <= $settings['min_posts']) {
      $posts = $manifest_posts;
      
      $use_manifest = true;
      // append special mark of posts to show their custom
        // function => register_dummy_data_post_status();
    }

    // Add new posts if they don't already exist
    foreach ($posts as $post) {

      $post_array = (array) $post;
      $found_post = @get_page_by_title($post_array['post_title'], ARRAY_A, $post_type);
      
      // If post doesn't already exist, create it.
      if (empty($found_post)) {

        // create post
        wp_insert_post($post_array);

        
        if (!empty($post_array['featured_image'])) {
          // add featured image to post
          // THIS WORKS IF WE PASS IN AN EXPLICIT ATTACHMENT ID
          // $post_id = 1720;
          // $post_thumbnail_id = 1719;
          // add_post_meta($post_id, '_thumbnail_id', $post_thumbnail_id);
          // // load the image
          // // $result = media_sideload_image($post_thumbnail_id, $post_id);
          // $attachments = get_posts(array('numberposts' => '1', 'post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC'));
          // if(sizeof($attachments) > 0){
          //     // set image as the post thumbnail
          //     error_log($attachments[0]->ID);
          //     set_post_thumbnail($post_id, $attachments[0]->ID);
          // }
        }

        // find post again in case it was just created and it didn't have an ID
        $post_with_id = @get_page_by_title($post_array['post_title'], ARRAY_A, $post_type);

        // add taxonomies to posts
        if ( !empty($post_array['taxonomies']) ) {
            // add meta data to post
            
            foreach ($post_array['taxonomies'] as $tax_terms) {

                if ( isset($tax_terms['taxonomy_terms']) ) {
                  // add taxonomy ids to an array

                  // get taxonomy IDs
                  $taxonomy_ids = array();
                  foreach ($tax_terms['taxonomy_terms'] as $tax_term) {
                    $term = get_term_by('name', $tax_term, $tax_terms['taxonomy_name'], ARRAY_A);
                    $taxonomy_ids[] = $term['term_id'];
                  }

                  // make sure taxonomy IDs are in correct form
                  $tax_ids = array_map('intval', $taxonomy_ids);
                  $tax_ids = array_unique( $tax_ids );

                  // Add taxonomies to post
                  wp_set_object_terms( $post_with_id['ID'], $tax_ids, $tax_terms['taxonomy_name'], true);
                }

            }
        }

        // add meta data to posts - only good for new posts because we wouldnt want to override existing posts
        if ( !empty($post_array['meta']) ) {
            // add meta data to post
            foreach ($post['meta'] as $key => $value) {
              $values = add_post_meta( $post_with_id["ID"], $key, $value );
              
            }
        }
        
      }
    }
    
    // NEED TO ADD POST ATTRIBUTES (IE. CATEGORIES, TAGS, ETC)
  }




  private static function register_dummy_data_post_status() {
    // http://codex.wordpress.org/Function_Reference/register_post_status
    // register_post_status("Dummy Data", array(
    //   'exclude_from_search' => true,
    //   'show_in_admin_all_list' => true,
    //   'show_in_admin_all' => true,
    //   'single_view_cap' => true,
    //   'label' => "Dummy Data",
    //   'public' => true
    //   )
    // );
  }
}
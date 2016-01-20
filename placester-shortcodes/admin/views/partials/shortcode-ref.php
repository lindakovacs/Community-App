<div id="<?php echo $shortcode ?>_ref" class="shortcode_ref">    
  <h3><u>Usage</u></h3>
    <?php if ($shortcode == 'search_form'): ?>
      <p>
        You can insert your "activated" Search Form snippet by using the [search_form] shortcode in a page or a post. 
        This control is intended to be used alongside the [search_listings] shortcode (defined below) to display the search 
        form's results.
      </p>
      <p>
        You can use the following shortcodes inside of your Search Form snippet definition to define form elements 
        that can filter on the field they are named after:
      </p>
      <?php foreach (PL_Shortcodes::$subcodes[$shortcode] as $subcode): ?>
        [<?php echo $subcode ?>], &nbsp;
      <?php endforeach ?>
    <?php elseif ($shortcode == 'search_listings'): ?>
      <p>
        You can insert your "activated" Listings snippet by using the [search_form] shortcode in a page or a post.
        The listings view is intended to be used alongside the [search_form] shortcode defined above as a container
        for the results of the search, with the snippet representing how an <i>individual</i> listing that matches
        the search criteria will be displayed.
      </p>
      <p>
        <b>NOTE:</b> The snippet that will be used by [search_listings] is the one that you last clicked "Activate" while 
        viewing or editing.
      </p>
      <p>
        You can use the following shortcodes inside of your Listings Form snippet definition to define what and where 
        certain information is displayed in the listings search :
      </p>
      <?php foreach (PL_Shortcodes::$subcodes['listing'] as $subcode): ?>
        [<?php echo $subcode ?>], &nbsp;
      <?php endforeach ?>
    <?php elseif ($shortcode == 'prop_details'): ?>
      <p>
        Unlike the other examples here, this snippet is not actually used via a shortcode--instead, what you define
        in your snippet overwrites the format for any property details page, including those accesssed from search and 
        listing elements you have <i>not</i> defined.
      </p>
      <p>
        You can use the following shortcodes inside of your Property Details snippet definition include form elements that can 
        filter on field they are named after:
      </p>
      <?php foreach (PL_Shortcodes::$subcodes['listing'] as $subcode): ?>
          [<?php echo $subcode ?>], &nbsp;
      <?php endforeach ?>
    <?php elseif ($shortcode == 'featured_listings'): ?>
      <p>
        You can insert your Featured Listings snippet by using the [featured_listings id="<em>listingid</em>"] shortcode in a page or a post.
        The shortcode require an ID parameter of the featured listing ID number published in your
        Featured Listings post type control on the left side of the admin panel.
      </p>
    <?php elseif ($shortcode == 'static_listings'): ?>
      <p>
        You can insert your Static Listings snippet by using the [static_listings id="<em>listingid</em>"] shortcode in a page or a post.
        The shortcode require an ID parameter of the static listing ID number published in your
        Featured Listings post type control on the left side of the admin panel.
      </p>
    <?php elseif ($shortcode == 'listing_slideshow'): ?>
      <p>
        You can create a slideshow for your Featured Listings by using the 
        [listing_slideshow post_id="<em>slideshowid</em>"] shortcode. 
        You can use the following shortcodes inside of your Slideshow template snippet 
        definition and include slideshow caption component, such as:
        <?php foreach (PL_Shortcodes::$subcodes['listing_slideshow'] as $subcode): ?>
          [<?php echo $subcode ?>], &nbsp;
        <?php endforeach ?>
      </p>
    <?php elseif ($shortcode == 'pl_neighborhood'): ?>
      <p>
        You can add a neighborhood area via the [pl_neighborhood] shortcode. 
        The neighborhood could list an area with polygons for a given region, such as:
      </p> 
        <ul>
            <li>Neighborhood</li>
            <li>City</li>
            <li>Zip code</li>
            <li>State</li>
        </ul>
      <p>
      You can use the following subshortcodes to create the template snippet with the
      Neighborhood details:
      <?php foreach (PL_Shortcodes::$subcodes['neighborhood'] as $subcode): ?>
          [<?php echo $subcode ?>], &nbsp;
      <?php endforeach ?>
      </p>
    <?php else: ?>
        Doc not found...
    <?php endif ?>

</div>
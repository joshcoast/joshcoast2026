<?php

/**
 * Custom Search Route
 *
 * The Big Idea Here is that we create an API that has perimeters for returning a json set of custom data.
 * This end point will be used by javascript on the front end to pass it the term, get the json, and
 * then display it.
 *
 * @perimeter term The search term we are searching for. Example: term=mysearchword
 */

add_action('rest_api_init', 'joshcoastRegisterSearch');

function joshcoastRegisterSearch() {
    register_rest_route('joshcoast/v1', 'search', array(
        // Think of the CRUD. Here we just want to 'GET', but wp has a species constant that will use "GET" in most cases. This is just a little bit safer than using 'GET'.
        'methods' => WP_REST_SERVER::READABLE,
        // Function that returns the API json data that will be displayed.
        'callback' => 'joshcoastSearchResults'
    ));
}

// "$data" is our Parameter from the url in the API, you can name it whatever you want.
function joshcoastSearchResults($data) {
    $mainQuery = new WP_Query(array(
        'post_type' => array('post', 'page', 'client'),
        // 's' stands for search, $data is an array from the parameter of the api url.
        // the key 'term' can be anything, it just needs to match the key we use in the url.
        // example http://joshcoast.local/wp-json/joshcoast/v1/search?term=audinate&pee=yellow then our
        // search will find "audinate" but if you put in $data['pee'], this would return 'yellow'
        // sanitize_text_field() is just a security precaution.
        's' => sanitize_text_field($data['term'])
    ));

    // Make an array that can sort our results into post types.
    $searchResults = array(
        'generalInfos' => array(),
        'clients'      => array(),
        'blogs'        => array()
    );

    while($mainQuery->have_posts()) {
        $mainQuery->the_post();
        // Sort the post into post types.
        if ( get_post_type() == 'page' ) {
            array_push($searchResults['generalInfos'], array(
                'post_type'  => get_post_type(),
                'title'      => get_the_title(),
                'permalink'  => get_the_permalink(),
                'authorName' => get_the_author()
            ));
        } elseif ( get_post_type() == 'post' ) {
            array_push($searchResults['blogs'], array(
                'post_type'  => get_post_type(),
                'title'      => get_the_title(),
                'permalink'  => get_the_permalink(),
                'authorName' => get_the_author(),
                'id'         => get_the_ID()
            ));
        } elseif ( get_post_type() == 'client' ) {
            array_push($searchResults['clients'], array(
                'post_type'  => get_post_type(),
                'title'      => get_the_title(),
                'permalink'  => get_the_permalink(),
                'authorName' => get_the_author(),
                'image'      => get_the_post_thumbnail_url( 0, 'thumbnail' ),
                'excerpt'    => get_the_excerpt( 0 )
            ));
        }
    }

    // If we have clients in the results, we want to include the related blog posts.
    if ( $searchResults['clients']) {

        // Build an array for each blog that was found that has arguments for the meta_query.
        $programsMetaQuery = array('relation' => 'OR');

        foreach($searchResults['blogs'] as $item ) {
            array_push( $programsMetaQuery, array(
                'key'     => 'related_blog_posts',
                'compare' => 'LIKE',
                'value'   => '"' . $item['id'] . '"'
            ));
        }

        // Run the query that will find the related items.
        $clientRelationshipQuery = new WP_Query(array(
            'post_type'  => 'client',
            'meta_query' => $programsMetaQuery
        ));

        while($clientRelationshipQuery->have_posts()) {
            $clientRelationshipQuery->the_post();

            if ( get_post_type() == 'client' ) {
                array_push($searchResults['clients'], array(
                    'post_type'  => get_post_type(),
                    'title'      => get_the_title(),
                    'permalink'  => get_the_permalink(),
                    'authorName' => get_the_author(),
                    'image'      => get_the_post_thumbnail_url( 0, 'thumbnail' ),
                    'excerpt'    => get_the_excerpt( 0 )
                ));
            }
        }

        // Get rid of duplicates in clients.
        $searchResults['clients'] = array_values( array_unique( $searchResults['clients'], SORT_REGULAR ) );
    }




    return $searchResults;
}

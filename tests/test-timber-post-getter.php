<?php

/**
 * @group posts-api
 */
class TestTimberPostGetter extends Timber_UnitTestCase {


	function setUp() {
		delete_option('sticky_posts');
		parent::setUp();
	}

	/**
	 * @group wp_query_hacks
	 */
	function testGettingWithCatList() {
		$this->markTestSkipped('moved to test-timber');
		$cat = array();
		$cat[] = $this->factory->term->create(array('name' => 'News', 'taxonomy' => 'category'));
		$cat[] = $this->factory->term->create(array('name' => 'Local', 'taxonomy' => 'category'));
		$pids = $this->factory->post->create_many(6);
		$cat_post = $this->factory->post->create(array('post_category' => array($cat[0])) );
		$cat_post = $this->factory->post->create(array('post_category' => array($cat[1])) );
		$cat_post = $this->factory->post->create(array('post_category' => $cat) );

		$posts = new Timber\PostQuery( array(
			'query' => array(
				'cat' => implode( ',', $cat ),
			),
		) );
		$this->assertEquals(3, count($posts));
	}

	/**
	 * @group wp_query_hacks
	 */
	function testGettingWithCategory() {
		$this->markTestSkipped('moved to test-timber');
		$cat = $this->factory->term->create(array('name' => 'News', 'taxonomy' => 'category'));
		$pids = $this->factory->post->create_many(6);
		$cats = $this->factory->post->create_many(3, array('post_category' => array($cat)) );
		$cat_post = $this->factory->post->create(array('post_category' => array($cat)) );

		$cat_post = Timber::get_post($cat_post);
		$this->assertEquals('News', $cat_post->category()->title());

		$posts = new Timber\PostQuery( array(
			'query' => array(
				'category' => $cat,
			),
		) );
		$this->assertEquals(4, count($posts));
	}

	/**
	 * @group wp_query_hacks
	 */
	function testGettingWithCategoryList() {
		// We don't really need to test this AND 'category' => [...]
		// since they're not really alternate code paths as far as Timber is concerned.
		// That is, only WP itself is concerned with the distinction.
		$this->markTestSkipped('removed');
		$cat = array();
		$cat[] = $this->factory->term->create(array('name' => 'News', 'taxonomy' => 'category'));
		$cat[] = $this->factory->term->create(array('name' => 'Local', 'taxonomy' => 'category'));
		$pids = $this->factory->post->create_many(6);
		$cat_post = $this->factory->post->create(array('post_category' => array($cat[0])) );
		$cat_post = $this->factory->post->create(array('post_category' => array($cat[1])) );
		$cat_post = $this->factory->post->create(array('post_category' => $cat) );

		$posts = new Timber\PostQuery( array(
			'query' => array(
				'category' => implode( ',', $cat ),
			),
		) );
		$this->assertEquals(3, count($posts));
	}

	function testGettingArrayWithSticky(){
		$this->markTestSkipped('moved to TestTimberMainClass::testFromArrayWithSticky');
		$pids = $this->factory->post->create_many(6);
		$sticky_id = $this->factory->post->create();
		$sticky = array($sticky_id, $pids[0]);
		update_option('sticky_posts', $sticky);
		$posts = new Timber\PostQuery( array(
			'query' => $pids,
		) );
		$post_ids_gotten = array();
		foreach($posts as $post) {
			$post_ids_gotten[] = $post->ID;
		}
		$this->assertNotContains($sticky_id, $post_ids_gotten);
		$this->assertContains($pids[0], $post_ids_gotten);
	}

	function testStickyAgainstGetPosts() {
		$this->markTestSkipped('@todo this behavior is being removed');
		$first = $this->factory->post->create(array('post_date' => '2015-04-23 15:13:52'));
		$sticky_id = $this->factory->post->create(array('post_date' => '2015-04-21 15:13:52'));
		$last = $this->factory->post->create(array('post_date' => '2015-04-24 15:13:52'));
		update_option('sticky_posts', array($sticky_id));
		add_filter( 'timber/get_posts/mirror_wp_get_posts', '__return_true' );
		$posts = Timber::get_posts('post_type=post');
		$this->assertEquals($last, $posts[0]->ID);
		$posts = get_posts('post_type=post');
		$this->assertEquals($last, $posts[0]->ID);
	}

	function testStickyAgainstTwoSuccessiveLookups() {
		$this->markTestSkipped('@todo this behavior is being removed');
		$first = $this->factory->post->create(array('post_date' => '2015-04-23 15:13:52'));
		$sticky_id = $this->factory->post->create(array('post_date' => '2015-04-21 15:13:52'));
		$last = $this->factory->post->create(array('post_date' => '2015-04-24 15:13:52'));
		update_option('sticky_posts', array($sticky_id));
		add_filter( 'timber/get_posts/mirror_wp_get_posts', '__return_true' );

		$posts = Timber::get_posts('post_type=post');
		$this->assertEquals($last, $posts[0]->ID);

		$posts = new Timber\PostQuery(array('query' => 'post_type=post'));
		$this->assertEquals($sticky_id, $posts[0]->ID);
	}

	function testStickyAgainstQuery() {
		$this->markTestSkipped('moved to test-timber');
		$this->factory->post->create(array('post_date' => '2015-04-23 15:13:52'));
		$sticky_id = $this->factory->post->create(array('post_date' => '2015-04-21 15:13:52'));
		$this->factory->post->create(array('post_date' => '2015-04-24 15:13:52'));

		update_option('sticky_posts', array($sticky_id));

		$posts = Timber::get_posts([
			'post_type' => 'post',
		]);
		$this->assertEquals($sticky_id, $posts[0]->ID);

		$posts = new WP_Query('post_type=post');
		$this->assertEquals($sticky_id, $posts->posts[0]->ID);
	}

	function testGetPostsWithClassMap() {
		$this->markTestSkipped('moved to test-timber');
		register_post_type('portfolio', array('public' => true));
		register_post_type('alert', array('public' => true));
		$this->factory->post->create(array('post_type' => 'portfolio', 'post_title' => 'A portfolio item', 'post_date' => '2015-04-23 15:13:52'));
		$this->factory->post->create(array('post_type' => 'alert', 'post_title' => 'An alert', 'post_date' => '2015-06-23 15:13:52'));
		$posts = new Timber\PostQuery( array(
			'query'      => 'post_type=any',
			'post_class' => array(
				'portfolio' => 'TimberPortfolio',
				'alert'     => 'TimberAlert',
			),
		) );
		$this->assertEquals( 'TimberAlert', get_class($posts[0]) );
		$this->assertEquals( 'TimberPortfolio', get_class($posts[1]) );
	}

	function testGetPostWithClassMap() {
		$this->markTestSkipped('moved to test-timber');
		register_post_type('portfolio', array('public' => true));
		$post_id_portfolio = $this->factory->post->create(array('post_type' => 'portfolio', 'post_title' => 'A portfolio item', 'post_date' => '2015-04-23 15:13:52'));
		$post_id_alert = $this->factory->post->create(array('post_type' => 'alert', 'post_title' => 'An alert', 'post_date' => '2015-06-23 15:13:52'));
		$post_portfolio = new Timber\PostQuery( array(
			'query' => $post_id_portfolio,
			'post_class' => array(
				'portfolio' => 'TimberPortfolio',
				'alert'     => 'TimberAlert',
			),
		) );
		$post_alert = new Timber\PostQuery( array(
			'query' => $post_id_alert,
			'post_class' => array(
				'portfolio' => 'TimberPortfolio',
				'alert' => 'TimberAlert'
			)
		) );
		$this->assertEquals( 'TimberPortfolio', get_class($post_portfolio[0]) );
		$this->assertEquals( $post_id_portfolio, $post_portfolio[0]->ID );
		$this->assertEquals( 'TimberAlert', get_class($post_alert[0]) );
		$this->assertEquals( $post_id_alert, $post_alert[0]->ID );
	}

	function testGettingEmptyArray(){
		$this->markTestSkipped('moved to test-timber');
		$pids = $this->factory->post->create_many( 15 );
		$posts = new Timber\PostQuery( array(
			'query' => array()
		) );
		$this->assertEmpty($posts);
	}

	function testGettingWithFalse(){
		$this->markTestSkipped('moved to test-timber');
		$pids = $this->factory->post->create_many( 15 );
		$posts = new Timber\PostQuery( array(
			'query' => false
		) );
		$this->assertEmpty($posts);
	}

	function testGetAttachment() {
		$this->markTestSkipped('moved to test-timber');
		$upload_dir = wp_upload_dir();
		$post_id = $this->factory->post->create();
		$filename = TestTimberImage::copyTestAttachment( 'flag.png' );
		$destination_url = str_replace( ABSPATH, 'http://'.$_SERVER['HTTP_HOST'].'/', $filename );
		$wp_filetype = wp_check_filetype( basename( $filename ), null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
		add_post_meta( $post_id, '_thumbnail_id', $attach_id, true );
		$data = array();
		$data['post'] = Timber::get_post( $post_id );
		$data['size'] = array( 'width' => 100, 'height' => 50 );
		$data['crop'] = 'default';
		Timber::compile( 'assets/thumb-test.twig', $data );
		$exists = file_exists( $filename );
		$this->assertTrue( $exists );
		$resized_path = $upload_dir['path'].'/flag-'.$data['size']['width'].'x'.$data['size']['height'].'-c-'.$data['crop'].'.png';
		$exists = file_exists( $resized_path );
		$this->assertTrue( $exists );
		$attachments = new Timber\PostQuery( array(
			'query' => 'post_type=attachment&post_status=inherit',
		) );
		$this->assertGreaterThan(0, count($attachments));
	}

	function testNumberPosts() {
		$this->markTestSkipped('moved to test-timber');
		$pids = $this->factory->post->create_many( 15 );
		$query = 'post_type=post&numberposts=7';
		$posts = new Timber\PostQuery( array(
			'query' => $query
		) );
		$this->assertEquals(7, count($posts));

	}

	function testNumberPostsBig() {
		$this->markTestSkipped('moved to test-timber');
		$pids = $this->factory->post->create_many( 15 );
		$query = 'post_type=post&numberposts=15';
		$posts = new Timber\PostQuery( array(
			'query' => $query
		) );
		$this->assertEquals(15, count($posts));

	}

	/**
	 * @group wp_query_hacks
	 */
	function testNumberPostsAll() {
		$this->markTestSkipped('moved to test-timber');
		$pids = $this->factory->post->create_many( 17 );
		$query = 'post_type=post&numberposts=-1';
		$posts = new Timber\PostQuery( array(
			'query' => $query
		) );
		$this->assertEquals(17, count($posts));

	}

	function testPostsPerPage() {
		$this->markTestSkipped('moved to test-timber');
		$pids = $this->factory->post->create_many( 15 );
		$query = 'post_type=post&posts_per_page=7';
		$posts = new Timber\PostQuery( array(
			'query' => $query
		) );
		$this->assertEquals(7, count($posts));
	}

	function testPostsPerPageAll() {
		$this->markTestSkipped('moved to test-timber');
		$pids = $this->factory->post->create_many( 23 );
		$query = 'post_type=post&posts_per_page=-1';
		$posts = new Timber\PostQuery( array(
			'query' => $query
		) );
		$this->assertEquals(23, count($posts));
	}

	function testPostsPerPageBig() {
		$this->markTestSkipped('moved to test-timber');
		$pids = $this->factory->post->create_many( 15 );
		$query = 'post_type=post&posts_per_page=15';
		$posts = new Timber\PostQuery( array(
			'query' => $query
		) );
		$this->assertEquals(15, count($posts));
	}

	/**
	 * @expectedDeprecated Timber\Timber::query_post()
	 */
	function testQueryPost() {
		$this->markTestSkipped('moved to test-timber and deprecated');
		$posts = $this->factory->post->create_many( 6 );
		$post = Timber::get_post( $posts[3] );
		$this->go_to( home_url( '/?p='.$posts[2] ) );
		$this->assertNotEquals( get_the_ID(), $post->ID );
		$post = Timber::query_post( $posts[3] );
		$this->assertEquals( get_the_ID(), $post->ID );
	}

	/**
	 * @expectedDeprecated Timber\Timber::query_post()
	 */
	function testBlankQueryPost() {
		$this->markTestSkipped('moved to test-timber and deprecated');
		$pid = $this->factory->post->create( );
		$this->go_to( home_url( '/?p='.$pid ) );
		$post = Timber::query_post();
		$this->assertEquals( $pid, $post->ID );
	}

	function testGetPostsInLoop() {
		$this->markTestSkipped('@todo what are we actually testing here? The Loop? Performance?');
		$posts = $this->factory->post->create_many( 55 );
		$this->go_to( '/' );
		$start = microtime( true );
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				$posts = new Timber\PostQuery($GLOBALS['wp_query']);
			}
		}
		$end = microtime( true );
		$diff = $end - $start;
		//if this takes more than 10 seconds, we're in trouble
		$this->assertLessThan( 10, $diff );
	}

	function testGetPostsFromLoop() {
		$this->markTestSkipped('moved to TestTimberMainClass::testGetPostsDefault()');
		$posts = $this->factory->post->create_many( 15 );
		$this->go_to( '/' );
		$posts = new Timber\PostQuery($GLOBALS['wp_query']);
		$this->assertEquals( 10, count( $posts ) );
		$pc = new Timber\PostQuery($GLOBALS['wp_query']);
		$this->assertEquals( 10, count( $pc ) );
	}

	function testGetPostsFromArray() {
		$this->markTestSkipped('moved to test-timber');
		$pids = $this->factory->post->create_many( 15 );
		$posts = new Timber\PostQuery(  array(
			'query' => $pids
		)  );
		$this->assertEquals( 15, count( $posts ) );
		$this->assertEquals( $pids[3], $posts[3]->ID );
	}

	function testGetPostWithSlug() {
		$this->markTestSkipped('removed');
		$post = $this->factory->post->create( array( 'post_name' => 'silly-post' ) );
		$posts = new Timber\PostQuery( array(
			'query' => 'silly-post'
		) );
		$this->assertEquals( 1, count( $posts ) );
		$this->assertEquals( 'silly-post', $posts[0]->slug );
	}

	function testCustomPostTypeAndClass() {
		$this->markTestSkipped('redundant');
		register_post_type('job');
		$jobs = $this->factory->post->create_many( 10, array('post_type' => 'job'));
		$jobPosts = new Timber\PostQuery( array(
			'query' => array(
				'post_type' => 'job',
			),
		) );
		$this->assertEquals(10, count($jobPosts));
	}

	function testCustomPostTypeAndClassOnSinglePage() {
		$this->markTestSkipped('moved to test-timber');
		register_post_type('job');
		$post_id = $this->factory->post->create( array( 'post_type' => 'job' ) );
		$post = Timber::get_post($post_id);
		$this->go_to('?p='.$post->ID);
		$jobs = $this->factory->post->create_many( 10, array('post_type' => 'job'));
		$jobPosts = new Timber\PostQuery( array(
			'query' => array(
				'post_type' => 'job',
			),
		) );
		$this->assertEquals(10, count($jobPosts));
	}

	function testStringWithPostClass() {
		$this->markTestSkipped('removed');
		$yes = \Timber\PostGetter::is_post_class_or_class_map('job');
		$this->assertTrue($yes);
	}

	function testStringWithPostClassBogus() {
		$this->markTestSkipped('removed');
		$no = \Timber\PostGetter::is_post_class_or_class_map('pants');
		$this->assertFalse($no);
	}

	function testNotATimberPost() {
		$this->markTestSkipped('removed');
		self::enable_error_log(false);
		$post_id = $this->factory->post->create( array( 'post_type' => 'state' ) );
		$use = \Timber\PostGetter::get_post_class('state', 'MyState');
		$this->assertEquals('\Timber\Post', $use);
		$post = new $use($post_id);
		$this->assertEquals('Timber\Post', get_class($post));
		self::enable_error_log(true);
	}

	function testPostTypeReturnAgainstArgType() {
		$this->markTestSkipped('removed in favor of Class Maps');
		register_post_type('person');
		$jobs = $this->factory->post->create_many( 4, array('post_type' => 'person'));
		$personPostsArray = new Timber\PostQuery( array(
			'query' => array(
				'post_type' => 'person',
			),
			'post_class' => 'Person',
		) );
		$personPostsString = new Timber\PostQuery( array(
			'query' => 'post_type=person',
			'post_class' => 'Person',
		) );
		$this->assertEquals(4, count($personPostsArray));
		$this->assertEquals(4, count($personPostsString));
	}

	/**
	 * Make sure that the_post action is called when we loop over a collection of posts.
	 */
	function testThePostHook() {
		$this->markTestSkipped('moved to test-timber');
		add_action( 'the_post', function( $post ) {
			add_filter( 'touched_the_post_action', '__return_true' );
		} );

		$posts = new Timber\PostQuery( array(
			'query' => $this->factory->post->create_many( 3 ),
		) );

		foreach ( $posts as $post ) {
			$this->assertTrue( apply_filters( 'touched_the_post_action', false ) );
		}
	}

	/**
	 * @expectedDeprecated Timber\PostQuery::get_posts()
	 */
	function testChangeArgumentInDefaultQuery() {
		$this->markTestSkipped('moved to test-timber');
		update_option( 'show_on_front', 'posts' );
		$post_ids = $this->factory->post->create_many( 3, array( 'post_type' => 'post' ) );
		$this->go_to( '/' );

		$posts = new Timber\PostQuery( array(
			'query' => array(
				'post__in' => array( $post_ids[1] ),
			),
			'merge_default' => true,
		) );

		$posts = $posts->to_array();

		$this->assertEquals( $posts[0]->ID, $post_ids[1] );
	}

	/**
	 * @expectedDeprecated Passing query arguments directly to PostQuery
	 * @expectedDeprecated Timber\PostQuery::get_posts()
	 */
	function testDeprecatedPostQueryArguments() {
		$this->markTestSkipped('removed');
		update_option( 'show_on_front', 'posts' );
		$post_ids = $this->factory->post->create_many( 3, array( 'post_type' => 'post' ) );
		$this->go_to( '/' );

		$posts = new Timber\PostQuery( array(
			'post_type'      => 'post',
			'posts_per_page' => - 1,
		) );

		$this->assertCount( 3, $posts->get_posts() );
	}

	function testGettingPostsWithStickiesReturnsCorrectAmountOfPosts(){
		$this->markTestSkipped('@todo this behavior is being removed');

		$post_ids = $this->factory->post->create_many(20);

		//Set some posts as sticky, outside of the first ten posts
        $sticky_ids = array_slice($post_ids, 11, 3);
        foreach($sticky_ids as $sticky_id){
            stick_post($sticky_id);
        }

        //Query the first ten posts
        $numberPosts = 10;
        $queryArgs = array(
            'post_type' => 'post',
            'numberposts' => $numberPosts,
            'orderby' => 'ID',
            'order' => 'ASC'
        );
		add_filter( 'timber/get_posts/mirror_wp_get_posts', '__return_true' );
        $posts = Timber::get_posts($queryArgs);
        $this->assertEquals($numberPosts, count($posts));

	}


	function testOrderOfPostsIn() {
		$this->markTestSkipped('@todo I don\'t think this test is really improving coverage?');
		$pids = $this->factory->post->create_many(30);
		shuffle($pids);
		$first_pids = array_slice($pids, 0, 5);
		$query = array('post__in' => $first_pids, 'orderby' => 'post__in');

		$timber_posts = Timber::get_posts($query);
		$timber_ids = array_map(function($post) {
			return $post->ID;
		}, $timber_posts);

		$this->assertEquals($first_pids, $timber_ids);

		$wp_posts = get_posts($query);
		$wp_ids = array_map(function($post) {
			return $post->ID;
		}, $wp_posts);

		$this->assertEquals($first_pids, $wp_ids);

	}


}

class MyState {

}

class job extends \Timber\Post {

}

class Person extends \Timber\Post {

}

class TimberAlert extends \Timber\Post {

}

class TimberPortfolio extends \Timber\Post {

}

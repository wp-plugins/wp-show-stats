<?php

function wp_show_stats_home_page() { 

    
        global $wpdb;
        
        // get post data
        $totalPosts = wp_count_posts();
        $totalPostsArray = (array)$totalPosts;
        unset($totalPostsArray['auto-draft']);
        unset($totalPostsArray['inherit']);
        $countPosts = array_sum($totalPostsArray);
        
        
        // get users data
        $users = count_users();
        
        // get page data 
        $totalPages = wp_count_posts('page');
        $totalPagesArray = (array)$totalPages;
        unset($totalPagesArray['auto-draft']);
        unset($totalPagesArray['inherit']);
        $countPages = array_sum($totalPagesArray);
        
        // get comments data
        $totalComments = wp_count_comments(); 

        // get category data
        $totalCategory  = wp_count_terms('category');
        $totalParentCategory = count(get_categories('parent=0&hide_empty=0'));
        $totalChildCategory = $totalCategory - $totalParentCategory;
        
        // get tags data
        $totalTags = wp_count_terms('post_tag');
        $unusedTagsResult = $wpdb->get_results( "SELECT name, slug FROM " . $wpdb->prefix . "terms WHERE term_id IN 
            (SELECT term_id FROM " . $wpdb->prefix . "term_taxonomy WHERE taxonomy = 'post_tag' AND count = 0 ) ");
        
        $unusedTags = count($unusedTagsResult);
        $usedTags = $totalTags - $unusedTags;
        
        // get custom post type data
        $allCustomPostTypes = get_post_types();
        $privateargs = array('public'   => true, '_builtin' => false);
        $privateCustomPostTypes = get_post_types($privateargs);
        $publicCustomPostTypes = array();
        foreach($allCustomPostTypes as $k => $type){
            if(!in_array($type, $privateCustomPostTypes)){
                $publicCustomPostTypes[] = $type;
            }
        }
        
        ?>


        <div class="wrap">
            <h2>WP Show Stats - To keep an eye on your usage of WordPress elements</h2>
            <div class="stat-charts-main">
                <div class="chartBox">
                    <div id="postsChart"></div>
                </div>
                <div class="chartBox">
                    <div id="pagesChart"></div>
                </div>
                <div class="chartBox">
                    <div id="usersChart" ></div>
                </div>
                <div class="chartBox">
                    <div id="commentsChart" ></div>
                </div>
                <div class="chartBox">
                    <div id="categoryChart" ></div>
                </div>
                <div class="chartBox">
                    <div id="tagChart" ></div>
                </div>
                <div class="chartBox">
                    <div id="cptChart" ></div>
                </div>
            </div>
        </div>
        
        <?php include_once('wp-show-stats-sidebar.php'); ?>
        
        
        <script>
            google.load("visualization", "1", {packages: ["corechart"]});
            google.setOnLoadCallback(drawCharts);

            function drawCharts() {
                
                // chart for post
                var postdata = new google.visualization.DataTable();
                postdata.addColumn('string', 'Status');
                postdata.addColumn('number', 'Number of posts');
                postdata.addRows([
                    ['Private(<?php echo $totalPosts->private; ?>)', <?php echo $totalPosts->private; ?>],
                    ['Pending(<?php echo $totalPosts->pending; ?>)', <?php echo $totalPosts->pending; ?>],
                    ['Draft(<?php echo $totalPosts->draft; ?>)', <?php echo $totalPosts->draft; ?>],
                    ['Published(<?php echo $totalPosts->publish; ?>)', <?php echo $totalPosts->publish; ?>],
                    ['Trash(<?php echo $totalPosts->trash; ?>)', <?php echo $totalPosts->trash; ?>],
                    ['Future(<?php echo $totalPosts->future; ?>)', <?php echo $totalPosts->future; ?>]
                ]);
                var postoptions = {title: 'Post Statistics (Total: <?php echo $countPosts; ?>)'};
                var postchart = new google.visualization.PieChart(document.getElementById('postsChart'));
                postchart.draw(postdata, postoptions);
                
                // chart for users
                var usersdata = new google.visualization.DataTable();
                usersdata.addColumn('string', 'Role');
                usersdata.addColumn('number', 'Number of users');
                usersdata.addRows([
                    <?php foreach($users['avail_roles'] as $role => $count) { ?>
                        ['<?php echo ucfirst($role) ?>(<?php echo $count; ?>)', <?php echo $count; ?>],
                    <?php } ?>
                ]);
                var usersoptions = {title: 'User Statistics (Total: <?php echo $users['total_users']; ?>)'};
                var userschart = new google.visualization.PieChart(document.getElementById('usersChart'));
                userschart.draw(usersdata, usersoptions);
                
                // chart for page
                var pagesdata = new google.visualization.DataTable();
                pagesdata.addColumn('string', 'Role');
                pagesdata.addColumn('number', 'Number of Pages');
                pagesdata.addRows([
                    ['Private(<?php echo $totalPages->private; ?>)', <?php echo $totalPages->private; ?>],
                    ['Pending(<?php echo $totalPages->pending; ?>)', <?php echo $totalPages->pending; ?>],
                    ['Draft(<?php echo $totalPages->draft; ?>)', <?php echo $totalPages->draft; ?>],
                    ['Published(<?php echo $totalPages->publish; ?>)', <?php echo $totalPages->publish; ?>],
                    ['Trash(<?php echo $totalPages->trash; ?>)', <?php echo $totalPages->trash; ?>],
                    ['Future(<?php echo $totalPages->future; ?>)', <?php echo $totalPages->future; ?>]
                ]);
                var pagesoptions = {title: 'Page Statistics (Total: <?php echo $countPages; ?>)'};
                var pageschart = new google.visualization.PieChart(document.getElementById('pagesChart'));
                pageschart.draw(pagesdata, pagesoptions);
                
                // chart for comments
                var commentsdata = new google.visualization.DataTable();
                commentsdata.addColumn('string', 'Role');
                commentsdata.addColumn('number', 'Number of comments');
                commentsdata.addRows([
                    ['Moderated(<?php echo $totalComments->moderated; ?>)', <?php echo $totalComments->moderated; ?>],
                    ['Approved(<?php echo $totalComments->approved; ?>)', <?php echo $totalComments->approved; ?>],
                    ['Trash(<?php echo $totalComments->trash; ?>)', <?php echo $totalComments->trash; ?>],
                    ['Spam(<?php echo $totalComments->spam; ?>)', <?php echo $totalComments->spam; ?>]
                ]);
                var commentsoptions = {title: 'Comments Statistics (Total: <?php echo $totalComments->total_comments; ?>)'};
                var commentschart = new google.visualization.PieChart(document.getElementById('commentsChart'));
                commentschart.draw(commentsdata, commentsoptions);
                
                // chart for category
                var categorydata = new google.visualization.DataTable();
                categorydata.addColumn('string', 'Role');
                categorydata.addColumn('number', 'Number of categories');
                categorydata.addRows([
                    ['Parent(<?php echo $totalParentCategory; ?>)', <?php echo $totalParentCategory; ?>],
                    ['Child(<?php echo $totalChildCategory; ?>)', <?php echo $totalChildCategory; ?>]
                ]);
                var categoryoptions = {title: 'Category Statistics (Total: <?php echo $totalCategory; ?>)'};
                var categorychart = new google.visualization.PieChart(document.getElementById('categoryChart'));
                categorychart.draw(categorydata, categoryoptions);
                
                 // chart for tags
                var tagdata = new google.visualization.DataTable();
                tagdata.addColumn('string', 'Tags');
                tagdata.addColumn('number', 'Number of tags');
                tagdata.addRows([
                    ['Used tags (<?php echo $usedTags; ?>) ', <?php echo $usedTags; ?>],
                    ['Unused Tags (<?php echo $unusedTags; ?>) ', <?php echo $unusedTags; ?>],
                ]);
                var tagoptions = {title: 'Tags Statistics (Total: <?php echo $totalTags; ?>)'};
                var tagchart = new google.visualization.PieChart(document.getElementById('tagChart'));
                tagchart.draw(tagdata, tagoptions);
                
                
                 // chart for tags
                var cptdata = new google.visualization.DataTable();
                cptdata.addColumn('string', 'Post types');
                cptdata.addColumn('number', 'Number of post types');
                cptdata.addRows([
                    ['Custom Post (<?php echo count($privateCustomPostTypes); ?>) ', <?php echo count($privateCustomPostTypes); ?>],
                    ['Inbuilt/Other Post (<?php echo count($publicCustomPostTypes); ?>) ', <?php echo count($publicCustomPostTypes); ?>],
                ]);
                var cptoptions = {title: 'Post Types Statistics (Total: <?php echo count($allCustomPostTypes); ?>)'};
                var cptchart = new google.visualization.PieChart(document.getElementById('cptChart'));
                cptchart.draw(cptdata, cptoptions);
                
                
            }

        </script>
    
<?php } ?>
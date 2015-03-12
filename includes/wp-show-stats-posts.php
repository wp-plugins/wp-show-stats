<?php
/*
 * sitestats Pages
 */

function wp_show_stats_posts() {

    global $wpdb;

    // get total post
    $totalPosts = wp_count_posts();
    $totalPostsArray = (array)$totalPosts;
    unset($totalPostsArray['auto-draft']);
    unset($totalPostsArray['inherit']);
    $countPosts = array_sum($totalPostsArray);
    
    // Get years that have posts and get comments count per year
    $years = $wpdb->get_results("SELECT YEAR(post_date) AS year FROM " . $wpdb->prefix . "posts 
            WHERE post_type = 'post' AND post_status = 'publish' 
            GROUP BY year DESC");
    
    // find year wise and month wise posts
    foreach($years as $k => $year){
        
        // year wise
        $yearWisePosts = $wpdb->get_results("
            SELECT YEAR(post_date) as post_year, COUNT(ID) as post_count 
                FROM " . $wpdb->prefix . "posts
                WHERE YEAR(post_date) =  '" . $year->year . "' AND post_type = 'post' 
                GROUP BY post_year
                ORDER BY post_date ASC"
        );
        if(!empty($yearWisePosts[0]->post_year)){
            $yearWiseArray[$yearWisePosts[0]->post_year] = $yearWisePosts[0]->post_count;
        }
        
        // month wise
        $monthWisePosts = $wpdb->get_results("
            SELECT MONTH(post_date) as post_month, COUNT(ID) as post_count 
                FROM " . $wpdb->prefix . "posts
                WHERE YEAR(post_date) =  '" . $year->year . "' AND post_type = 'post'
                GROUP BY post_month
                ORDER BY post_date ASC"
            );
        
        foreach($monthWisePosts as $mk => $post){
            $monthWiseArray[$year->year][$post->post_month] = $post->post_count;
        }
    }
    // make the string of month wise comments according to chart's requirements
   foreach($monthWiseArray as $y => $arr){
       $test_arr = array();
       for($i = 1; $i<=12; $i++){
           $test_arr[$i] = isset($arr[$i]) ? $arr[$i] : 0;
       }
       $monthsArray[$y] = implode(",", $test_arr);
   }
    
    // most commented posts
    $mostCommentedPosts = $wpdb->get_results("SELECT comment_count, ID, post_title, post_author, post_date
        FROM $wpdb->posts wposts, $wpdb->comments wcomments
        WHERE wposts.ID = wcomments.comment_post_ID
        AND wposts.post_status='publish'
        AND wcomments.comment_approved='1'
        GROUP BY wposts.ID
        ORDER BY comment_count DESC
        LIMIT 0 ,  5
    ");
    
    
    $longestPosts = $wpdb->get_results("SELECT ID,post_title,post_date,LENGTH(post_content) - LENGTH(REPLACE(post_content,' ',''))+1 AS post_length 
            FROM $wpdb->posts 
            WHERE post_status = 'publish' AND post_type = 'post'
            GROUP BY ID
            ORDER BY post_length DESC 
            LIMIT 5");

    $shortestPosts = $wpdb->get_results("SELECT ID,post_title,post_date,LENGTH(post_content) - LENGTH(REPLACE(post_content,' ',''))+1 AS post_length 
            FROM $wpdb->posts 
            WHERE post_status = 'publish' AND post_type = 'post'
            GROUP BY ID
            ORDER BY post_length 
            LIMIT 5");
    
    ?>

    <div class="wrap">
        <h2>WP Show Stats - Post Statistics</h2>
        <div class="stat-charts-main">
            <div class="chartBox">
                <div id="totalPostsChart"></div>
            </div>
            <div class="chartBox">
                <div id="mostCommentsChart"></div>
            </div>
            <div class="chartBox">
                <div id="byYearChart"></div>
            </div>
            <div class="chartBox">
                <div id="longestPostsChart"></div>
            </div>
            <div class="chartBox">
                <div id="shortestPostsChart"></div>
            </div>
            <div class="chartBoxLarge">
                <div id="monthWiseChart"></div>
            </div>
        </div>
    </div>

    <?php include_once('wp-show-stats-sidebar.php'); ?>

    <script type="text/javascript">
            google.load("visualization", "1", {packages: ["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                
                // total posts chart
                <?php if($countPosts > 0): ?>
                    var postdata = google.visualization.arrayToDataTable([
                        ["Status", "Number of posts", {role: "style"}],
                        ["Private", <?php echo $totalPosts->private; ?>, "#000000"],
                        ["Pending", <?php echo $totalPosts->pending; ?>, "#0f0f0f"],
                        ["Draft", <?php echo $totalPosts->draft; ?>, "#0000ff"],
                        ["Published", <?php echo $totalPosts->publish; ?>, "#00ff00"],
                        ["Trash", <?php echo $totalPosts->trash; ?>, "#ff0000"],
                        ["Future", <?php echo $totalPosts->future; ?>, "#e0440e"],
                    ]);
                    var postview = new google.visualization.DataView(postdata);
                    postview.setColumns([0, 1,2]);
                    var postoptions = {
                        title: "Posts by status (Total: <?php echo $countPosts; ?>)",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var postchart = new google.visualization.ColumnChart(document.getElementById("totalPostsChart"));
                    postchart.draw(postview, postoptions);
                <?php else: ?>
                    document.getElementById('totalPostsChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Total/Status Wise Post Stats' because there are no posts found.</span>";
                <?php endif; ?> 
                
                // most commented posts
                <?php if(count($mostCommentedPosts) > 0): ?>
                    var mostcommentsdata = google.visualization.arrayToDataTable([
                        ["Comments", "Number of comments", {role: "style"}],
                        <?php $i=0; foreach($mostCommentedPosts as $k => $post): $i++; ?>
                            ["<?php echo substr($post->post_title,0,10); ?>", <?php echo $post->comment_count; ?>, "<?php echo $i%2==0 ? "#00ff00" : "0000ff"; ?>"],
                        <?php endforeach; ?>
                    ]);
                    var mostcommentsview = new google.visualization.DataView(mostcommentsdata);
                    mostcommentsview.setColumns([0, 1,2]);
                    var mostcommentsoptions = {
                        title: "5 most commented posts",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var mostcommentsChart = new google.visualization.ColumnChart(document.getElementById("mostCommentsChart"));
                    mostcommentsChart.draw(mostcommentsview, mostcommentsoptions);
                <?php else: ?>
                    document.getElementById('mostCommentsChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Most Commented Posts Stats' because there are no posts found.</span>";
                <?php endif; ?>
                
                
                // year wise post chart
                <?php if(count($yearWiseArray) > 0): ?>
                    var yearwisedata = google.visualization.arrayToDataTable([
                        ["Year", "Number of posts", {role: "style"}],
                        <?php $i=0; foreach($yearWiseArray as $k => $val): $i++; ?>
                            ["<?php echo $k; ?>", <?php echo $val; ?>, "<?php echo $i%2==0 ? "#00ff00" : "0000ff"; ?>"],
                        <?php endforeach; ?>
                    ]);
                    var yearwiseview = new google.visualization.DataView(yearwisedata);
                    yearwiseview.setColumns([0, 1,2]);
                    var yearwiseoptions = {
                        title: "Posts by year (Total: <?php echo $countPosts; ?>)",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var yearwiseChart = new google.visualization.ColumnChart(document.getElementById("byYearChart"));
                    yearwiseChart.draw(yearwiseview, yearwiseoptions);
                <?php else: ?>
                    document.getElementById('byYearChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Posts By Year Stats' because there are no posts found.</span>";
                <?php endif; ?>
                
                // monthwise posts chart
                <?php if(count($monthsArray) > 0): ?>
                    var monthwisedata = google.visualization.arrayToDataTable([
                        ['Month', 'Jan','Feb','Mar','Apr','May','Jun','July','Aug','Sept','Oct','Nov','Dec', { role: 'annotation' } ],
                        <?php foreach($monthsArray as $k => $data): ?>
                            ['<?php echo $k; ?>',<?php echo $data; ?>,''],
                        <?php endforeach; ?>
                    ]);

                      var monthwiseoptions = {
                        width: 1015,
                        height: 500,
                        title: "Month wise posts",
                        legend: { position: 'top', maxLines: 3 },
                        bar: { groupWidth: '55%' },
                        isStacked: true,
                      };
                    var monthwiseview = new google.visualization.DataView(monthwisedata);
                    monthwiseview.setColumns([0,1,2,3,4,5,6,7,8,9,10,11,12]);
                    var monthwiseChart = new google.visualization.ColumnChart(document.getElementById("monthWiseChart"));
                    monthwiseChart.draw(monthwiseview, monthwiseoptions);
                <?php else: ?>
                    document.getElementById('monthWiseChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Posts By Months Stats' because there are no posts found.</span>";
                <?php endif; ?>
                
                // Author wise posts
               
               
               
                // longest posts
                <?php if(count($longestPosts) > 0): ?>
                    var longestdata = google.visualization.arrayToDataTable([
                        ["Post", "Length (No. of words)", {role: "style"}],
                        <?php $i=0; foreach($longestPosts as $k => $post): $i++; ?>
                            ["<?php echo $post->post_title;; ?>", <?php echo $post->post_length; ?>, "<?php echo $i%2==0 ? "#00ff00" : "0000ff"; ?>"],
                        <?php endforeach; ?>
                    ]);
                    var longestview = new google.visualization.DataView(longestdata);
                    longestview.setColumns([0, 1,2]);
                    var longestoptions = {
                        title: "5 longest posts",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var longestChart = new google.visualization.ColumnChart(document.getElementById("longestPostsChart"));
                    longestChart.draw(longestview, longestoptions);
                <?php else: ?>
                    document.getElementById('longestPostsChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Longest Posts Stats' because there are no posts found.</span>";
                <?php endif; ?>
                
                // shortest posts
                <?php if(count($shortestPosts) > 0): ?>
                    var shortestdata = google.visualization.arrayToDataTable([
                        ["Post", "Length (No. of words)", {role: "style"}],
                        <?php $i=0; foreach($shortestPosts as $k => $post): $i++; ?>
                            ["<?php echo $post->post_title;; ?>", <?php echo $post->post_length; ?>, "<?php echo $i%2==0 ? "#00ff00" : "0000ff"; ?>"],
                        <?php endforeach; ?>
                    ]);
                    var shortestview = new google.visualization.DataView(shortestdata);
                    shortestview.setColumns([0, 1,2]);
                    var shortestoptions = {
                        title: "5 shortest posts",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var shortestChart = new google.visualization.ColumnChart(document.getElementById("shortestPostsChart"));
                    shortestChart.draw(shortestview, shortestoptions);
                <?php else: ?>
                    document.getElementById('shortestPostsChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Shortest Posts Stats' because there are no posts found.</span>";
                <?php endif; ?> 
                
            }
        </script>
<?php } ?>
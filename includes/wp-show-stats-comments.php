<?php

function wp_show_stats_comments() {

    global $wpdb;
    
    // get total comments
    $totalComments = wp_count_comments();
    
    // Get years that have posts and get comments count per year
    $years = $wpdb->get_results("SELECT YEAR(post_date) AS year FROM " . $wpdb->prefix . "posts 
            WHERE post_type = 'post' AND post_status = 'publish' 
            GROUP BY year DESC");
    
    // find year wise and month wise comments
    foreach($years as $k => $year){
        
        // year wise
        $yearWiseComments = $wpdb->get_results("
            SELECT YEAR(comment_date) as comment_year, COUNT(comment_ID) as comment_count 
                FROM " . $wpdb->prefix . "comments
                WHERE YEAR(comment_date) =  '" . $year->year . "' AND comment_type = '' 
                GROUP BY comment_year
                ORDER BY comment_date ASC"
        );
        if(!empty($yearWiseComments[0]->comment_year)){
            $yearWiseArray[$yearWiseComments[0]->comment_year] = $yearWiseComments[0]->comment_count;
        }
        
        // month wise
        $monthWiseComments = $wpdb->get_results("
            SELECT MONTH(comment_date) as comment_month, COUNT(comment_ID) as comment_count 
                FROM " . $wpdb->prefix . "comments
                WHERE YEAR(comment_date) =  '" . $year->year . "' AND comment_type = ''
                GROUP BY comment_month
                ORDER BY comment_date ASC"
            );
        
        foreach($monthWiseComments as $mk => $comment){
            $monthWiseArray[$year->year][$comment->comment_month] = $comment->comment_count;
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
    
    // get userwise comments
    $userWisecomments = $wpdb->get_results("SELECT COUNT(comment_author_email) 
        AS comments_count, comment_author_email, comment_author, comment_author_url
        FROM " . $wpdb->prefix . "comments 
        WHERE comment_author_email != '' AND comment_type = ''
        GROUP BY comment_author_email
        ORDER BY comments_count DESC, comment_author ASC
        LIMIT 5");
    
    ?>

    <div class="wrap">
        <h2>WP Show Stats - Comments Statistics</h2>
        <div class="stat-charts-main">
            <div class="chartBox">
                <div id="totalCommentsChart"></div>
            </div>
            <div class="chartBox">
                <div id="mostCommentsChart"></div>
            </div>
            <div class="chartBox">
                <div id="byYearChart"></div>
            </div>
            <div class="chartBox">
                <div id="userWiseChart"></div>
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
                
                // total comments chart
                <?php if($totalComments->total_comments > 0): ?>
                    var totalCommentsdata = google.visualization.arrayToDataTable([
                        ["Comments", "Number of comments", {role: "style"}],
                        ["Approved", <?php echo $totalComments->approved; ?>, "#00ff00"],
                        ["Moderation", <?php echo $totalComments->moderated; ?>, "#0000ff"],
                        ["Spam", <?php echo $totalComments->spam; ?>, "#ff0000"],
                        ["Trash", <?php echo $totalComments->trash; ?>, "#000000"],
                    ]);
                    var totalCommentsview = new google.visualization.DataView(totalCommentsdata);
                    totalCommentsview.setColumns([0, 1,2]);
                    var totalCommentsoptions = {
                        title: "Comments by status (Total: <?php echo $totalComments->total_comments; ?>)",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var totalCommentsChart = new google.visualization.ColumnChart(document.getElementById("totalCommentsChart"));
                    totalCommentsChart.draw(totalCommentsview, totalCommentsoptions);
                <?php else: ?>
                    document.getElementById('totalCommentsChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Total Comments Stats' because there are no comments found.</span>";
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
                    document.getElementById('mostCommentsChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Most Commented Posts Stats' because there are no comments found.</span>";
                <?php endif; ?>
                
                
                // year comments chart
                <?php if(count($yearWiseArray) > 0): ?>
                    var yearwisedata = google.visualization.arrayToDataTable([
                        ["Year", "Number of comments", {role: "style"}],
                        <?php $i=0; foreach($yearWiseArray as $k => $val): $i++; ?>
                            ["<?php echo $k; ?>", <?php echo $val; ?>, "<?php echo $i%2==0 ? "#00ff00" : "0000ff"; ?>"],
                        <?php endforeach; ?>
                    ]);
                    var yearwiseview = new google.visualization.DataView(yearwisedata);
                    yearwiseview.setColumns([0, 1,2]);
                    var yearwiseoptions = {
                        title: "Comments by year (Total: <?php echo $totalComments->total_comments; ?>)",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var yearwiseChart = new google.visualization.ColumnChart(document.getElementById("byYearChart"));
                    yearwiseChart.draw(yearwiseview, yearwiseoptions);
                <?php else: ?>
                    document.getElementById('byYearChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Year Wise Comments Stats' because there are no comments found.</span>";
                <?php endif; ?>    
                
                <?php if(count($userWisecomments) > 0): ?>
                    // user wise comments chart
                    var userwisedata = google.visualization.arrayToDataTable([
                        ["User", "Number of comments", {role: "style"}],
                        <?php $i=0; foreach($userWisecomments as $k => $val): $i++; ?>
                            ["<?php echo ucfirst($val->comment_author); ?>", <?php echo $val->comments_count; ?>, "<?php echo $i%2==0 ? "#00ff00" : "0000ff"; ?>"],
                        <?php endforeach; ?>
                    ]);
                    var userwiseview = new google.visualization.DataView(userwisedata);
                    userwiseview.setColumns([0,1,2]);
                    var userwiseoptions = {
                        title: "Most comments by Authors",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var userwiseChart = new google.visualization.ColumnChart(document.getElementById("userWiseChart"));
                    userwiseChart.draw(userwiseview, userwiseoptions);
                <?php else: ?>
                    document.getElementById('userWiseChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'User Wise Comments Stats' because there are no comments found.</span>";
                <?php endif; ?>
                
                    
                // monthwise comments chart
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
                        title: "Month wise comments",
                        legend: { position: 'top', maxLines: 3 },
                        bar: { groupWidth: '55%' },
                        isStacked: true,
                      };
                    var monthwiseview = new google.visualization.DataView(monthwisedata);
                    monthwiseview.setColumns([0,1,2,3,4,5,6,7,8,9,10,11,12]);
                    var monthwiseChart = new google.visualization.ColumnChart(document.getElementById("monthWiseChart"));
                    monthwiseChart.draw(monthwiseview, monthwiseoptions);
               <?php else: ?>
                    document.getElementById('monthWiseChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Month Wise Comments Stats' because there are no comments found.</span>";
                <?php endif; ?>
                
            }
        </script>

<?php } ?>
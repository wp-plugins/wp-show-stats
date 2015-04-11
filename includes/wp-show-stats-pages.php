<?php

function wp_show_stats_pages() {

    global $wpdb;
    
    // get page data 
    $totalPages = wp_count_posts('page');
    $totalPagesArray = (array)$totalPages;
    unset($totalPagesArray['auto-draft']);
    unset($totalPagesArray['inherit']);
    $countPages = array_sum($totalPagesArray);
    
    
     // Get years that have page and get comments count per year
    $years = $wpdb->get_results("SELECT YEAR(post_date) AS year FROM " . $wpdb->prefix . "posts 
            WHERE post_type = 'page' AND post_status = 'publish' 
            GROUP BY year DESC");
    
    // find year wise and month wise page
    foreach($years as $k => $year){
        
        // year wise
        $yearWisePages = $wpdb->get_results("
            SELECT YEAR(post_date) as post_year, COUNT(ID) as post_count 
                FROM " . $wpdb->prefix . "posts
                WHERE YEAR(post_date) =  '" . $year->year . "' AND post_type = 'page' 
                GROUP BY post_year
                ORDER BY post_date ASC"
        );
        if(!empty($yearWisePages[0]->post_year)){
            $yearWiseArray[$yearWisePages[0]->post_year] = $yearWisePages[0]->post_count;
        }
        
        // month wise
        $monthWisePages = $wpdb->get_results("
            SELECT MONTH(post_date) as post_month, COUNT(ID) as post_count 
                FROM " . $wpdb->prefix . "posts
                WHERE YEAR(post_date) =  '" . $year->year . "' AND post_type = 'page'
                GROUP BY post_month
                ORDER BY post_date ASC"
            );
        
        foreach($monthWisePages as $mk => $page){
            $monthWiseArray[$year->year][$page->post_month] = $page->post_count;
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
    
   // 5 longest page
   $longestPages = $wpdb->get_results("SELECT ID,post_title,post_date,LENGTH(post_content) - LENGTH(REPLACE(post_content,' ',''))+1 AS post_length 
            FROM $wpdb->posts 
            WHERE post_status = 'publish' AND post_type = 'page'
            GROUP BY ID
            ORDER BY post_length DESC 
            LIMIT 5");

   // 5 shortest page
    $shortestPages = $wpdb->get_results("SELECT ID,post_title,post_date,LENGTH(post_content) - LENGTH(REPLACE(post_content,' ',''))+1 AS post_length 
            FROM $wpdb->posts 
            WHERE post_status = 'publish' AND post_type = 'page'
            GROUP BY ID
            ORDER BY post_length 
            LIMIT 5");
    
    ?>

    <div class="wrap">
        <h2>WP Show Stats - Page Statistics</h2>
        <div class="stat-charts-main">
            <div class="chartBox">
                <div id="totalPagesChart"></div>
            </div>
            <div class="chartBox">
                <div id="byYearChart"></div>
            </div>
            <div class="chartBoxLarge">
                <div id="monthWiseChart"></div>
            </div>
            <div class="chartBox">
                <div id="longestPagesChart"></div>
            </div>
            <div class="chartBox">
                <div id="shortestPagesChart"></div>
            </div>
        </div>
    </div>


    <?php include_once('wp-show-stats-sidebar.php'); ?>

    <script type="text/javascript">
            google.load("visualization", "1", {packages: ["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                
                // total pages chart
                <?php if($countPages > 0): ?>
                    var pagedata = google.visualization.arrayToDataTable([
                        ["Status", "Number of page", {role: "style"}],
                        ["Published", <?php echo $totalPages->publish; ?>, "#0000ff"],
                        ["Draft", <?php echo $totalPages->draft; ?>, "#000000"],
                        ["Future", <?php echo $totalPages->future; ?>, "#0f0f0f"],
                        ["Pending", <?php echo $totalPages->pending; ?>, "#00ff00"],
                        ["Private", <?php echo $totalPages->private; ?>, "#e0440e"],
                        ["Trash", <?php echo $totalPages->trash; ?>, "#ff0000"],
                        
                    ]);
                    var pageview = new google.visualization.DataView(pagedata);
                    pageview.setColumns([0, 1,2]);
                    var pageoptions = {
                        title: "Page by status (Total: <?php echo $countPages; ?>)",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var pagechart = new google.visualization.ColumnChart(document.getElementById("totalPagesChart"));
                    pagechart.draw(pageview, pageoptions);
                <?php else: ?>
                    document.getElementById('totalPagesChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Total/Status Wise Page Stats' because there are no page found.</span>";
                <?php endif; ?> 
                
                // year wise page chart
                <?php if(count($yearWiseArray) > 0): ?>
                    var yearwisedata = google.visualization.arrayToDataTable([
                        ["Year", "Number of page", {role: "style"}],
                        <?php $i=0; foreach($yearWiseArray as $k => $val): $i++; ?>
                            ["<?php echo $k; ?>", <?php echo $val; ?>, "<?php echo $i%2==0 ? "#00ff00" : "0000ff"; ?>"],
                        <?php endforeach; ?>
                    ]);
                    var yearwiseview = new google.visualization.DataView(yearwisedata);
                    yearwiseview.setColumns([0, 1,2]);
                    var yearwiseoptions = {
                        title: "Page by year (Total: <?php echo $countPages; ?>)",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var yearwiseChart = new google.visualization.ColumnChart(document.getElementById("byYearChart"));
                    yearwiseChart.draw(yearwiseview, yearwiseoptions);
                <?php else: ?>
                    document.getElementById('byYearChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Pages By Year Stats' because there are no page found.</span>";
                <?php endif; ?>
                    
                
                // monthwise page chart
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
                        title: "Month wise Page",
                        legend: { position: 'top', maxLines: 3 },
                        bar: { groupWidth: '55%' },
                        isStacked: true,
                      };
                    var monthwiseview = new google.visualization.DataView(monthwisedata);
                    monthwiseview.setColumns([0,1,2,3,4,5,6,7,8,9,10,11,12]);
                    var monthwiseChart = new google.visualization.ColumnChart(document.getElementById("monthWiseChart"));
                    monthwiseChart.draw(monthwiseview, monthwiseoptions);
                <?php else: ?>
                    document.getElementById('monthWiseChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Page By Months Stats' because there are no page found.</span>";
                <?php endif; ?>
                
                // longest pages
                <?php if(count($longestPages) > 0): ?>
                    var longestdata = google.visualization.arrayToDataTable([
                        ["Page", "Length (No. of words)", {role: "style"}],
                        <?php $i=0; foreach($longestPages as $k => $page): $i++; ?>
                            ["<?php echo $page->post_title;; ?>", <?php echo $page->post_length; ?>, "<?php echo $i%2==0 ? "#00ff00" : "0000ff"; ?>"],
                        <?php endforeach; ?>
                    ]);
                    var longestview = new google.visualization.DataView(longestdata);
                    longestview.setColumns([0, 1,2]);
                    var longestoptions = {
                        title: "5 longest pages",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var longestChart = new google.visualization.ColumnChart(document.getElementById("longestPagesChart"));
                    longestChart.draw(longestview, longestoptions);
                <?php else: ?>
                    document.getElementById('longestPagesChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Longest Page Stats' because there are no page found.</span>";
                <?php endif; ?>
                
                // shortest pages
                <?php if(count($shortestPages) > 0): ?>
                    var shortestdata = google.visualization.arrayToDataTable([
                        ["Page", "Length (No. of words)", {role: "style"}],
                        <?php $i=0; foreach($shortestPages as $k => $page): $i++; ?>
                            ["<?php echo $page->post_title;; ?>", <?php echo $page->post_length; ?>, "<?php echo $i%2==0 ? "#00ff00" : "0000ff"; ?>"],
                        <?php endforeach; ?>
                    ]);
                    var shortestview = new google.visualization.DataView(shortestdata);
                    shortestview.setColumns([0, 1,2]);
                    var shortestoptions = {
                        title: "5 shortest page",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var shortestChart = new google.visualization.ColumnChart(document.getElementById("shortestPagesChart"));
                    shortestChart.draw(shortestview, shortestoptions);
                <?php else: ?>
                    document.getElementById('shortestPagesChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Shortest Page Stats' because there are no page found.</span>";
                <?php endif; ?> 
            }
        </script>

<?php } ?>
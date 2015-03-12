<?php

function wp_show_stats_users() {

    global $wpdb;
    global $wp_roles;

    // get role wise users
    $usersCount = count_users();
    
    // get total users count
    $totalUsers = count(get_users());
    
    // Get years of registered users
    $years = $wpdb->get_results("SELECT YEAR(user_registered) AS year FROM " . $wpdb->prefix . "users GROUP BY year DESC");
    
    // find year wise and month wise comments
    foreach($years as $k => $year){
        
        // year wise
        $yearWiseUsers = $wpdb->get_results("
            SELECT YEAR(user_registered) as users_year, COUNT(ID) as users_count 
                FROM " . $wpdb->prefix . "users 
                WHERE YEAR(user_registered) =  '" . $year->year . "' 
                GROUP BY users_year
                ORDER BY user_registered ASC"
        );
        if(!empty($yearWiseUsers[0]->users_year)){
            $yearWiseArray[$yearWiseUsers[0]->users_year] = $yearWiseUsers[0]->users_count;
        }
        
        // month wise
        $monthWiseUsers = $wpdb->get_results("
            SELECT MONTH(user_registered) as users_month, COUNT(ID) as users_count 
                FROM " . $wpdb->prefix . "users
                WHERE YEAR(user_registered) =  '" . $year->year . "' 
                GROUP BY users_month
                ORDER BY user_registered ASC"
            );
        
        foreach($monthWiseUsers as $mk => $usr){
            $monthWiseArray[$year->year][$usr->users_month] = $usr->users_count;
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

  ?>

    <div class="wrap">
        <h2>WP Show Stats - Users Statistics</h2>
        <div class="stat-charts-main">
            <div class="chartBox">
                <div id="rolewiseChart"></div>
            </div>
            <div class="chartBox">
                <div id="byYearChart"></div>
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
                
                // rolewise users count
                <?php if($totalUsers > 0): ?>
                    var rolewisedata = google.visualization.arrayToDataTable([
                        ["Role", "Number of users", {role: "style"}],
                        <?php $i=0; foreach ($usersCount['avail_roles'] as $role => $count): $i++; ?>
                            ["<?php echo ucfirst($role) ?>", <?php echo $count; ?>, "<?php echo $i%2==0 ? "#00ff00" : "0000ff"; ?>"],
                        <?php endforeach; ?>
                    ]);
                    var rolewiseview = new google.visualization.DataView(rolewisedata);
                    rolewiseview.setColumns([0, 1, 2]);
                    var rolewiseoptions = {
                        title: "Role wise users (Total users: <?php echo $totalUsers; ?>)",
                        bar: {groupWidth: "95%"},
                        legend: {position: "none"},
                    };
                    var rolewisechart = new google.visualization.ColumnChart(document.getElementById("rolewiseChart"));
                    rolewisechart.draw(rolewiseview, rolewiseoptions);
                <?php else: ?>
                    document.getElementById('rolewiseChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Rolewise Users Stats' because there are no users found.</span>";
                <?php endif; ?>
                
                
                // year wise user registration
                <?php if(count($yearWiseArray) > 0): ?>
                    var yearwisedata = google.visualization.arrayToDataTable([
                        ["Year", "Number of users registered", {role: "style"}],
                        <?php $i=0; foreach($yearWiseArray as $k => $val): $i++; ?>
                            ["<?php echo $k; ?>", <?php echo $val; ?>, "<?php echo $i%2==0 ? "#00ff00" : "0000ff"; ?>"],
                        <?php endforeach; ?>
                    ]);
                    var yearwiseview = new google.visualization.DataView(yearwisedata);
                    yearwiseview.setColumns([0, 1,2]);
                    var yearwiseoptions = {
                        title: "Users registration by year (Total: <?php echo $totalUsers; ?>)",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var yearwiseChart = new google.visualization.ColumnChart(document.getElementById("byYearChart"));
                    yearwiseChart.draw(yearwiseview, yearwiseoptions);
                <?php else: ?>
                    document.getElementById('byYearChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'User Registration By Year Stats' because there are no users found.</span>";
                <?php endif; ?>
                
                // monthwise user registration chart
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
                        title: "Month wise user registration",
                        legend: { position: 'top', maxLines: 3 },
                        bar: { groupWidth: '55%' },
                        isStacked: true,
                      };
                    var monthwiseview = new google.visualization.DataView(monthwisedata);
                    monthwiseview.setColumns([0,1,2,3,4,5,6,7,8,9,10,11,12]);
                    var monthwiseChart = new google.visualization.ColumnChart(document.getElementById("monthWiseChart"));
                    monthwiseChart.draw(monthwiseview, monthwiseoptions);
                <?php else: ?>
                    document.getElementById('monthWiseChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'User Registration By Months Stats' because there are no users found.</span>";
                <?php endif; ?>
            }
        </script>

<?php } ?>
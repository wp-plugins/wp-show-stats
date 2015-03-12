<?php

function wp_show_stats_tags() {

    global $wpdb;

    // get tags data
    $totalTags = wp_count_terms('post_tag');

    // used/unused tags
    $unusedTagsResult = $wpdb->get_results( "SELECT name, slug FROM " . $wpdb->prefix . "terms WHERE term_id IN 
        (SELECT term_id FROM " . $wpdb->prefix . "term_taxonomy WHERE taxonomy = 'post_tag' AND count = 0 ) ");
    $unusedTags = count($unusedTagsResult);
    $usedTags = $totalTags - $unusedTags;
    
    // most used tags
    $mostusedargs=array('orderby' => 'count','order' => 'DESC','hide_empty' => 0,'number' => 5);
    $mostusedtags = get_tags($mostusedargs);
    
    // less used tags
    $lessusedargs=array('orderby' => 'count','order' => 'ASC','hide_empty' => 0,'number' => 5);
    $lessusedtags = get_tags($lessusedargs);
    
    
    ?>

    <div class="wrap">
        <h2>WP Show Stats - Tags Statistics</h2>
        <div class="stat-charts-main">
            <div class="chartBox">
                <div id="usedTagsChart"></div>
            </div>
            <div class="chartBox">
                <div id="mostUsedChart"></div>
            </div>
            <div class="chartBox">
                <div id="lessUsedChart"></div>
            </div>
        </div>
    </div>


    <?php include_once('wp-show-stats-sidebar.php'); ?>

    <script type="text/javascript">
            google.load("visualization", "1", {packages: ["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                
                // used/unused tags chart
                <?php if($totalTags > 0): ?>
                    var usedunuseddata = google.visualization.arrayToDataTable([
                        ["Tags", "Count", {role: "style"}],
                        ["Used", <?php echo $usedTags; ?>, "#00ff00"],
                        ["Unused", <?php echo $unusedTags; ?>, "#0000ff"],
                    ]);
                    var usedunusedview = new google.visualization.DataView(usedunuseddata);
                    usedunusedview.setColumns([0, 1,2]);
                    var usedunusedoptions = {
                        title: "Used/Unused Tags (Total: <?php echo $totalTags; ?>)",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var usedunusedChart = new google.visualization.ColumnChart(document.getElementById("usedTagsChart"));
                    usedunusedChart.draw(usedunusedview, usedunusedoptions);
                <?php else: ?>
                    document.getElementById('usedTagsChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Used/Unused Tags Stats' because there are no tags found.</span>";
                <?php endif; ?>
                
                <?php if(count($mostusedtags) > 0): ?>
                    // most used tags
                    var mostuseddata = google.visualization.arrayToDataTable([
                        ["Tags", "Count", {role: "style"}],
                        <?php $i=0; foreach($mostusedtags as $k => $tag): $i++; ?>
                            ["<?php echo $tag->name;; ?>", <?php echo $tag->count; ?>, "<?php echo $i%2==0 ? "#00ff00" : "0000ff"; ?>"],
                        <?php endforeach; ?>
                    ]);
                    var mostusedview = new google.visualization.DataView(mostuseddata);
                    mostusedview.setColumns([0, 1,2]);
                    var mostusedoptions = {
                        title: "5 most used tags",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var mostusedChart = new google.visualization.ColumnChart(document.getElementById("mostUsedChart"));
                    mostusedChart.draw(mostusedview, mostusedoptions);
                <?php else: ?>
                    document.getElementById('mostUsedChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Most Used Tags Stats' because there are no tags found.</span>";
                <?php endif; ?>
                
                
                <?php if(count($lessusedtags) > 0): ?>
                    // less used tags
                    var lessuseddata = google.visualization.arrayToDataTable([
                        ["Tags", "Count", {role: "style"}],
                        <?php $i=0; foreach($lessusedtags as $k => $tag): $i++; ?>
                            ["<?php echo $tag->name;; ?>", <?php echo $tag->count; ?>, "<?php echo $i%2==0 ? "#00ff00" : "0000ff"; ?>"],
                        <?php endforeach; ?>
                    ]);
                    var lessusedview = new google.visualization.DataView(lessuseddata);
                    lessusedview.setColumns([0, 1,2]);
                    var lessusedoptions = {
                        title: "5 less used tags",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var lessusedChart = new google.visualization.ColumnChart(document.getElementById("lessUsedChart"));
                    lessusedChart.draw(lessusedview, lessusedoptions);
                <?php else: ?>
                    document.getElementById('lessUsedChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Less Used Tags Stats' because there are no tags found.</span>";
                <?php endif; ?>
                
                 
                
                
            }
        </script>

<?php } ?>
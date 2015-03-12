<?php

function wp_show_stats_custom_post_types() {

    global $wpdb;

    // get all post types
    $allCustomPostTypes = get_post_types();
    
    // get custom post types
    $cptargs = array('public'   => true, '_builtin' => false);
    $customPostTypes = get_post_types($cptargs);
    
    // get public post types
    $publicPostTypes = array();
    foreach($allCustomPostTypes as $k => $type){
        if(!in_array($type, $customPostTypes)){
            $publicPostTypes[] = $type;
        }
    }

    // count records of each custom post types
    $cptStats = array();
    foreach($customPostTypes as $key => $cpt){
        $posts = get_posts(array(
            'post_type'   => $cpt,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        $cptStats[$cpt] = count($posts);
        unset($posts);
    }
    
?>

    <div class="wrap">
        <h2>WP Show Stats - Custom Post Types Statistics</h2>
        <div class="stat-charts-main">
            <div class="chartBox">
                <div id="totalPostsChart"></div>
            </div>
            <div class="chartBox">
                <div id="cptcountChart"></div>
            </div>
            <div class="chartBoxLarge">
                <div id="postListChart"></div>
            </div>
        </div>
    </div>

    <?php include_once('wp-show-stats-sidebar.php'); ?>

    <script type="text/javascript">
        
            // post type table custom/other
            google.load("visualization", "1", {packages:["table"]});
            google.setOnLoadCallback(drawTable);

            function drawTable() {
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Name');
                data.addColumn('string', 'Type');
                data.addRows([
                <?php foreach($customPostTypes as $k => $custom): ?>  
                    ['<?php echo ucfirst($custom); ?>',  "Custom Post Type"],
                <?php endforeach; ?>
                <?php foreach($publicPostTypes as $k => $public): ?>  
                    ['<?php echo ucfirst(str_replace("_", " ",$public)); ?>',  "Inbuilt/Other Post Type"],
                <?php endforeach; ?>    
                ]);
                

              var table = new google.visualization.Table(document.getElementById('postListChart'));

              table.draw(data, {showRowNumber: true, width: '500px'});
            }

        
            google.load("visualization", "1", {packages: ["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                
                // total posts chart
                var postdata = google.visualization.arrayToDataTable([
                    ["Type", "Number of posts", {role: "style"}],
                    ["Inbuilt/Other", <?php echo count($publicPostTypes); ?>, "#00ff00"],
                    ["Custom Post Types", <?php echo count($customPostTypes); ?>, "#0000ff"],
                ]);
                var postview = new google.visualization.DataView(postdata);
                postview.setColumns([0, 1,2]);
                var postoptions = {
                    title: "Post types (Total: <?php echo count($allCustomPostTypes); ?>)",
                    bar: {groupWidth: "70%"},
                    legend: {position: "none"},
                };
                var postchart = new google.visualization.ColumnChart(document.getElementById("totalPostsChart"));
                postchart.draw(postview, postoptions);
                
                
                // custom post types record count chart
                <?php if(count($cptStats) > 0): ?>
                    var cptcountdata = google.visualization.arrayToDataTable([
                        ["Type", "Number of records", {role: "style"}],
                        <?php $i=0; foreach($cptStats as $name => $count): $i++; ?>
                            ["<?php echo ucfirst($name); ?>", <?php echo $count; ?>, "<?php echo $i%2==0 ? "#00ff00" : "0000ff"; ?>"],
                        <?php endforeach; ?>
                    ]);
                    var cptcountview = new google.visualization.DataView(cptcountdata);
                    cptcountview.setColumns([0, 1,2]);
                    var cptcountoptions = {
                        title: "CPT Records Count",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var postchart = new google.visualization.ColumnChart(document.getElementById("cptcountChart"));
                    postchart.draw(cptcountview, cptcountoptions);
                <?php else: ?>
                    document.getElementById('cptcountChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here 'Records Count By Custom Post Type'. It looks like CPT is not used or there are no records.</span>";
                <?php endif; ?>
                
            }
        </script>

<?php } ?>
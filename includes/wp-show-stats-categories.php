<?php

function wp_show_stats_categories() {

    global $wpdb;
    
    // get total category
    $totalCategory = wp_count_terms('category');
    
    // get used and unused category
    $unusedCategory = $wpdb->get_results("SELECT name, slug FROM  ". $wpdb->prefix . "terms 
        WHERE term_id IN (SELECT term_id  FROM  ". $wpdb->prefix . "term_taxonomy  WHERE taxonomy = 'category'  AND count = 0 ) ");
    $usedCategory = $totalCategory - count($unusedCategory);
    
    // get parent and child category
    $totalParentCategory = count(get_categories('parent=0&hide_empty=0'));
    $totalChildCategory = $totalCategory - $totalParentCategory;
    
    // find most and less used category
    $mostArgs=array('orderby' => 'count','order' => 'DESC','hide_empty' => 0,'number' => 5);
    $mostUsedcategories=get_categories($mostArgs);
    $lessArgs=array('orderby' => 'count','order' => 'ASC','hide_empty' => 0,'number' => 5);
    $lessUsedcategories=get_categories($lessArgs);
    
?>

    <div class="wrap">
        <h2>WP Show Stats - Category Statistics</h2>
        <div class="stat-charts-main">
            <div class="chartBox">
                <div id="parentChildChart"></div>
            </div>
            <div class="chartBox">
                <div id="usedUnusedChart"></div>
            </div>
            <div class="chartBoxLarge">
                <div id="mostUsedChart"></div>
            </div>
            <div class="chartBoxLarge">
                <div id="lessUsedChart"></div>
            </div>
        </div>
    </div>

    <?php include_once('wp-show-stats-sidebar.php'); ?>

        <script type="text/javascript">
            google.load("visualization", "1", {packages: ["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                
                
                // chart for child/parent category
                <?php if($totalCategory > 0): ?>
                    var data = google.visualization.arrayToDataTable([
                        ["Category", "Count", {role: "style"}],
                        ["Parent", <?php echo $totalParentCategory; ?>, "#0000ff"],
                        ["Child", <?php echo $totalChildCategory; ?>, "#00ff00"],
                    ]);
                    var view = new google.visualization.DataView(data);
                    view.setColumns([0, 1, 2]);
                    /*view.setColumns([0, 1,
                        {calc: "stringify",
                            sourceColumn: 0,
                            type: "string",
                            role: "annotation"},
                        2]);*/
                    var options = {
                        title: "Parent and child categories (Total: <?php echo $totalCategory; ?>)",
                        bar: {groupWidth: "95%"},
                        legend: {position: "none"},
                    };
                    var chart = new google.visualization.ColumnChart(document.getElementById("parentChildChart"));
                    chart.draw(view, options);
                <?php else: ?>
                    document.getElementById('parentChildChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Parent Child Category Stats' because there are no categories found.</span>";
                <?php endif; ?>
                
                // chart for used/unused categories
                <?php if($totalCategory > 0): ?>
                    var usedcategorydata = google.visualization.arrayToDataTable([
                        ["Category", "Count", {role: "style"}],
                        ["Used",<?php echo $usedCategory; ?>, "#0000ff"],
                        ["Unused",<?php echo count($unusedCategory); ?>, "#00ff00"],
                    ]);
                    var usedcategoryview = new google.visualization.DataView(usedcategorydata);
                    usedcategoryview.setColumns([0, 1, 2]);
                    var usedcategoryoptions = {
                        title: "Used/Unused categories (Total: <?php echo $totalCategory; ?>)",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var usedcategorychart = new google.visualization.ColumnChart(document.getElementById("usedUnusedChart"));
                    usedcategorychart.draw(usedcategoryview, usedcategoryoptions);
                <?php else: ?>
                    document.getElementById('usedUnusedChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Used/Unused Category Stats' because there are no categories found.</span>";
                <?php endif; ?>   
                
                
                // chart for most used categories
                <?php if($totalCategory > 0): ?>
                    var mostuseddata = google.visualization.arrayToDataTable([
                        ["Category", "Used in number of posts", {role: "style"}],
                        <?php $i=0; foreach($mostUsedcategories as $k => $val): $i++; ?>
                            ["<?php echo $val->name; ?>", <?php echo $val->category_count; ?>, "<?php echo $i%2==0 ? "#00ff00" : "0000ff"; ?>"],
                        <?php endforeach; ?>
                    ]);
                    var mostusedview = new google.visualization.DataView(mostuseddata);
                    mostusedview.setColumns([0, 1, 2]);
                    var mostusedoptions = {
                        width: 1015,
                        height: 500,
                        title: "5 most used categories (Total: <?php echo $totalCategory; ?>)",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var mostusedchart = new google.visualization.ColumnChart(document.getElementById("mostUsedChart"));
                    mostusedchart.draw(mostusedview, mostusedoptions);
                <?php else: ?>
                    document.getElementById('mostUsedChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Most Used Category Stats' because there are no categories found.</span>";
                <?php endif; ?> 
                
                
                // chart for less used categories
                <?php if($totalCategory > 0): ?>
                    var lessuseddata = google.visualization.arrayToDataTable([
                        ["Category", "Used in number of posts", {role: "style"}],
                        <?php $i=0; foreach($lessUsedcategories as $k => $val): $i++; ?>
                            ["<?php echo $val->name; ?>", <?php echo $val->category_count; ?>, "<?php echo $i%2==0 ? "#00ff00" : "0000ff"; ?>"],
                        <?php endforeach; ?>
                    ]);
                    var lessusedview = new google.visualization.DataView(lessuseddata);
                    lessusedview.setColumns([0, 1, 2]);
                    var lessusedoptions = {
                        width: 1015,
                        height: 500,
                        title: "5 less used categories (Total: <?php echo $totalCategory; ?>)",
                        bar: {groupWidth: "70%"},
                        legend: {position: "none"},
                    };
                    var lessusedchart = new google.visualization.ColumnChart(document.getElementById("lessUsedChart"));
                    lessusedchart.draw(lessusedview, lessusedoptions);
                <?php else: ?>
                    document.getElementById('lessUsedChart').innerHTML = "<span class='nothingtodo'>There is nothing to show here for 'Less Used Category Stats' because there are no categories found.</span>";
                <?php endif; ?> 
                
                
            }
        </script>
<?php } ?>
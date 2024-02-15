<?php

// Your hierarchical data
$url = "https://new-e-service.spcc.co.th/Dashbord/Api/Area/Get_sunburst";

$json = file_get_contents($url);

$json_data = json_decode($json, true);


$root = array("name" => "งานคดี", "children" => array());

foreach ($json_data["data"] as $entry) {
    if (!isset($entry["value"])) {
        
        addToHierarchy($root, $entry);
    }
}

function addToHierarchy(&$node, $entry)
{
    if ($entry["parent"] !== "") {
        if ($entry["parent"] === "0.0") {
            $node["children"][] = [
                "name" => $entry["name"],
                "color" => $entry["color"] ? $entry["color"] : "" ,
                "children" => [] // Children array can be empty initially
            ];
            addToHierarchyChild($node, $entry);
        };
    }
}


function addToHierarchyChild(&$root, $entry)
{
    global $json_data;
    foreach ($json_data["data"] as $childEntry) {
        if ($childEntry["parent"] === $entry["id"]) {
            foreach ($root["children"] as &$parentNode) {
                if ($parentNode["name"] === $entry["name"]) {
                    if(!isset($childEntry["color"])){
                        $parentNode["children"][] = [
                            "name" => $childEntry["name"],
                            "value" => $childEntry["value"] ,
                            "color" =>  "lightgreen"
                        ];
                    }else {
                        $parentNode["children"][] = [
                            "name" => $childEntry["name"],
                            "value" => $childEntry["value"] ,
                            "color" =>  $childEntry["color"]
                        ];
                    }
                  
                    // Recursively add children for this child node
                    addToHierarchyChild($parentNode, $childEntry);
                }
            }
        }
    }
}


// print_r($json_data['title'])

$json2 =  json_encode($root, JSON_PRETTY_PRINT);
// echo '<pre>' . $json2 . 's</pre>'; 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sunburst Chart</title>
  
    <script src="https://cdn.amcharts.com/lib/4/core.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>

    <style>
        body {
            padding: 20px;
        }
        .header {
            text-align: center;
            font-size: 25px;

        }
        #chartdiv {
            width: 100%; height: 500px;
            
        }
    </style>

</head>

<body>
    <h1 class="header"><?php echo $json_data['title']; ?></h1>
    <div id="chartdiv"></div>

    <script>

        var data = []

        data.push(<?php echo $json2 ?>)
        am4core.useTheme(am4themes_animated);
        console.log(typeof(Object.entries(data)))
        var chart = am4core.create("chartdiv", am4charts.TreeMap);
        
        console.log(data)
       
        chart.data = data
        chart.maxLevels = 1;

        /* Set color step */
        chart.colors.step = 2;


        // chart.layoutAlgorithm = chart.squarify 	;


        /* Define data fields */
        chart.dataFields.value = "value";
        chart.dataFields.name = "name";
        chart.dataFields.color = "color";
        chart.dataFields.children = "children";

        var level1 = chart.seriesTemplates.create("0");
        var level1_bullet = level1.bullets.push(new am4charts.LabelBullet());
        level1_bullet.locationY = 0.5;
        level1_bullet.locationX = 0.5;
        level1_bullet.label.text = "{name}";
        level1_bullet.label.fill = am4core.color("#fff");

        var level2 = chart.seriesTemplates.create("1");
        var level2_bullet = level2.bullets.push(new am4charts.LabelBullet());
        level2_bullet.locationY = 0.5;
        level2_bullet.locationX = 0.5;
        level2_bullet.label.text = "{name}";
        level2_bullet.label.fill = am4core.color("#fff");

        var level3 = chart.seriesTemplates.create("2");
        var level3_bullet = level3.bullets.push(new am4charts.LabelBullet());
        level3_bullet.locationY = 0.5;
        level3_bullet.locationX = 0.5;
        level3_bullet.label.text = "{name}";
        level3_bullet.label.fill = am4core.color("#fff");

        /* Navigation bar */
        chart.homeText = "Home";
        chart.navigationBar = new am4charts.NavigationBar();

    </script>
</body>

</html>
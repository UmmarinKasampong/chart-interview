<?php

// Your hierarchical data
$url = "https://new-e-service.spcc.co.th/Dashbord/Api/Area/Get_sunburst";

$json = file_get_contents($url);

$json_data = json_decode($json, true);


$root = array("name" => "งานคดี", "children" => array());
$c = 0;
foreach ($json_data["data"] as $entry) {
    if (!isset($entry["value"])) {
        // data in root and current data : งานคดี
        addToHierarchy($root, $entry);
    }
}

function addToHierarchy(&$node, $entry)
{
    if ($entry["parent"] !== "") {
        if ($entry["parent"] === "0.0") {
            $node["children"][] = [
                "name" => $entry["name"],
                "color" => $entry["color"] ? $entry["color"] : "",
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
                    if (!isset($childEntry["color"])) {
                        $parentNode["children"][] = [
                            "name" => $childEntry["name"],
                            "value" => $childEntry["value"],
                            "color" =>  "lightgreen"
                        ];
                    } else {
                        $parentNode["children"][] = [
                            "name" => $childEntry["name"],
                            "value" => $childEntry["value"],
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
    <title>Chart</title>

    <!-- Styles -->
    <style>
        body {
            padding: 20px;
        }

        .header {
            text-align: center;
            font-size: 25px;

        }

        #chartdiv {
            width: 100%;
            height: 650px;
        }
    </style>
</head>

<body>
    <h1 class="header"><?php echo $json_data['title']; ?></h1>
    <div id="chartdiv"></div>


    <!-- Resources -->
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/hierarchy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>

    <!-- Chart code -->
    <script>
        am5.ready(function() {

            // Create root element
            // https://www.amcharts.com/docs/v5/getting-started/#Root_element
            var root = am5.Root.new("chartdiv");


            // Set themes
            // https://www.amcharts.com/docs/v5/concepts/themes/
            root.setThemes([
                am5themes_Animated.new(root)
            ]);


            // Create wrapper container
            var container = root.container.children.push(am5.Container.new(root, {
                width: am5.percent(100),
                height: am5.percent(100),
                layout: root.verticalLayout
            }));


            // Create series
            // https://www.amcharts.com/docs/v5/charts/hierarchy/#Adding
            var series = container.children.push(am5hierarchy.Sunburst.new(root, {
                singleBranchOnly: true,
                downDepth: 10,
                initialDepth: 10,
                valueField: "value",
                categoryField: "name",
                childDataField: "children"
            }));


            // Generate and set data
            // https://www.amcharts.com/docs/v5/charts/hierarchy/#Setting_data
            var maxLevels = 2;
            var maxNodes = 3;
            var maxValue = 100;

            var data = <?php echo $json2 ?>
            // generateLevel(data, "", 0);

            series.data.setAll([data]);
            series.set("selectedDataItem", series.dataItems[0]);

            // function generateLevel(data, name, level) {
            //   for (var i = 0; i < Math.ceil(maxNodes * Math.random()) + 1; i++) {
            //     var nodeName = name + "ABCDEFGHIJKLMNOPQRSTUVWXYZ"[i];
            //     var child;
            //     if (level < maxLevels) {
            //       child = {
            //         name: nodeName + level
            //       }

            //       if (level > 0 && Math.random() < 0.5) {
            //         child.value = Math.round(Math.random() * maxValue);
            //       }
            //       else {
            //         child.children = [];
            //         generateLevel(child, nodeName + i, level + 1)
            //       }
            //     }
            //     else {
            //       child = {
            //         name: name + i,
            //         value: Math.round(Math.random() * maxValue)
            //       }
            //     }
            //     data.children.push(child);
            //   }

            //   level++;
            //   return data;
            // }


            // Make stuff animate on load
            series.appear(1000, 100);

        }); // end am5.ready()
    </script>
</body>



</html>